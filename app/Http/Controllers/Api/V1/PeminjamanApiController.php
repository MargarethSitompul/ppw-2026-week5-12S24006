<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Wajib Bearer Token)
     * Daftar transaksi peminjaman terpaginasi, filter status & nim
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', $nim))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi peminjaman berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
            ]
        );
    }

    /**
     * POST /api/v1/peminjamans (Wajib Bearer Token)
     * Mencatat peminjaman baru dengan jaminan ACID + Pessimistic Locking
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id' => 'required|integer|exists:bukus,id',
            'nim_peminjam' => 'required|string|max:15',
            'nama_peminjam' => 'required|string|max:150',
            'durasi_hari' => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE) untuk menangkal Race Condition
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse('Resource Not Found', 'Buku tidak ditemukan.', 404, 'buku-not-found');
            }

            // 2. Tolak jika mahasiswa masih meminjam buku yang sama & belum dikembalikan -> 409 Conflict
            $sudahPinjamAktif = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $validated['nim_peminjam'])
                ->where('status', 'dipinjam')
                ->exists();

            if ($sudahPinjamAktif) {
                return $this->problemResponse(
                    title: 'Duplicate Active Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$validated['nim_peminjam']}' masih memiliki peminjaman aktif atas buku '{$buku->judul}' yang belum dikembalikan.",
                    status: 409,
                    errorSlug: 'duplicate-active-loan'
                );
            }

            // 3. Cek ketersediaan stok -> 422 Unprocessable Content jika habis
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Buku Stock Empty',
                    detail: "Stok buku sedang habis untuk '{$buku->judul}'.",
                    status: 422,
                    errorSlug: 'buku-stock-empty'
                );
            }

            // 4. Kurangi stok (-1) & catat baris peminjaman secara atomik
            $buku->decrement('stok');

            $durasiHari = $validated['durasi_hari'] ?? 7;

            $peminjaman = Peminjaman::create([
                'kode_pinjam' => 'KP-' . strtoupper(Str::random(8)),
                'buku_id' => $buku->id,
                'nim_peminjam' => strtoupper(trim($validated['nim_peminjam'])),
                'nama_peminjam' => $validated['nama_peminjam'],
                'tanggal_pinjam' => now(),
                'tanggal_jatuh_tempo' => now()->addDays($durasiHari),
                'status' => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok kini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * GET /api/v1/peminjamans/{id} (Wajib Bearer Token)
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('buku')->find($id);

        if (!$peminjaman) {
            return $this->problemResponse('Resource Not Found', 'Data peminjaman tidak ditemukan.', 404, 'peminjaman-not-found');
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail peminjaman berhasil ditemukan.'
        );
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Wajib Bearer Token)
     * Mengembalikan buku secara atomik, menolak jika sudah dikembalikan sebelumnya
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::find($id);

            if (!$peminjaman) {
                return $this->problemResponse('Resource Not Found', 'Data peminjaman tidak ditemukan.', 404, 'peminjaman-not-found');
            }

            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Conflict — Already Returned',
                    detail: "Peminjaman '{$peminjaman->kode_pinjam}' sudah berstatus dikembalikan sebelumnya.",
                    status: 409,
                    errorSlug: 'loan-already-returned'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status' => 'dikembalikan',
                'tanggal_kembali' => now(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: "Buku '{$buku?->judul}' berhasil dikembalikan. Stok kini: {$buku?->fresh()->stok}."
            );
        });
    }
}
