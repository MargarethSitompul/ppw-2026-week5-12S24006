<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus
     * Koleksi buku terpaginasi: search, filter kategori_id, filter tersedia, sort
     */
    public function index(Request $request): JsonResponse
    {
        // 1. Batasi kolom pengurutan yang diizinkan (mencegah SQL Injection pada ORDER BY)
        $allowedSorts = ['judul', 'penulis', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy = in_array($request->query('sort'), $allowedSorts) ? $request->query('sort') : 'judul';
        $sortDir = strtolower($request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = min(max((int) $request->query('per_page', 5), 1), 50);

        // 2. Kueri bebas N+1: eager-load kategori + hitung jumlah peminjaman
        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), fn ($q, $kategoriId) => $q->where('kategori_id', (int) $kategoriId))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi buku berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total_items' => $paginator->total(),
                'total_pages' => $paginator->lastPage(),
                'links' => [
                    'first' => $paginator->url(1),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                    'last' => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/bukus/{id}
     * Detail satu buku beserta kategori dan riwayat peminjaman
     */
    public function show(string $id): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di basis data.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus
     * Membuat buku baru -> 201 Created + Header Location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'isbn' => 'required|string|max:20|unique:bukus,isbn',
            'judul' => 'required|string|max:150',
            'penulis' => 'required|string|max:100',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'kategori_id' => 'required|integer|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string|max:2000',
        ]);

        $buku = Buku::create($validated);
        $buku->load('kategori');
        $buku->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Buku baru berhasil dibuat.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id}
     * PUT = Full Replacement, PATCH = Partial Update
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // SEMANTIK PUT: seluruh field wajib dikirim utuh
            $validated = $request->validate([
                'isbn' => "required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul' => 'required|string|max:150',
                'penulis' => 'required|string|max:100',
                'penerbit' => 'required|string|max:100',
                'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'required|integer|exists:kategoris,id',
                'stok' => 'required|integer|min:0',
                'sinopsis' => 'nullable|string|max:2000',
            ]);
            // Pada PUT murni, field nullable yang tidak dikirim direset ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
        } else {
            // SEMANTIK PATCH: hanya atribut yang dikirim yang divalidasi & diubah
            $validated = $request->validate([
                'isbn' => "sometimes|required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul' => 'sometimes|required|string|max:150',
                'penulis' => 'sometimes|required|string|max:100',
                'penerbit' => 'sometimes|required|string|max:100',
                'tahun_terbit' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'kategori_id' => 'sometimes|required|integer|exists:kategoris,id',
                'stok' => 'sometimes|required|integer|min:0',
                'sinopsis' => 'sometimes|nullable|string|max:2000',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }
        }

        $buku->update($validated);
        $buku->load('kategori');
        $buku->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi buku berhasil diganti (PUT).'
                : 'Atribut buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id}
     * 409 Conflict jika masih ada peminjaman aktif, 204 No Content jika sukses
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::withCount(['peminjamans as active_loans_count' => function ($q) {
            $q->where('status', 'dipinjam');
        }])->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        if ($buku->active_loans_count > 0) {
            return $this->problemResponse(
                title: 'Conflict — Buku Has Active Loans',
                detail: "Buku '{$buku->judul}' tidak dapat dihapus karena masih memiliki {$buku->active_loans_count} peminjaman aktif.",
                status: 409,
                errorSlug: 'buku-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }

    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal
     * REST Service Consumer: mengambil metadata tambahan buku dari OpenLibrary API publik
     */
    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'buku-not-found'
            );
        }

        try {
            $response = Http::timeout(8)->get('https://openlibrary.org/api/books', [
                'bibkeys' => "ISBN:{$buku->isbn}",
                'format' => 'json',
                'jscmd' => 'data',
            ]);
        } catch (\Throwable $e) {
            return $this->problemResponse(
                title: 'Bad Gateway',
                detail: 'Gagal berkomunikasi dengan layanan eksternal OpenLibrary API (timeout atau koneksi gagal).',
                status: 502,
                errorSlug: 'external-service-unreachable'
            );
        }

        if ($response->failed()) {
            return $this->problemResponse(
                title: 'Bad Gateway',
                detail: 'Layanan eksternal OpenLibrary API mengembalikan galat.',
                status: 502,
                errorSlug: 'external-service-error'
            );
        }

        $key = "ISBN:{$buku->isbn}";
        $body = $response->json();
        $eksternal = $body[$key] ?? null;

        if (!$eksternal) {
            return $this->successResponse(
                data: [
                    'isbn' => $buku->isbn,
                    'ditemukan_di_openlibrary' => false,
                ],
                message: 'Buku tidak ditemukan pada basis data OpenLibrary eksternal.'
            );
        }

        return $this->successResponse(
            data: [
                'isbn' => $buku->isbn,
                'ditemukan_di_openlibrary' => true,
                'judul_eksternal' => $eksternal['title'] ?? null,
                'jumlah_halaman' => $eksternal['number_of_pages'] ?? null,
                'penerbit_eksternal' => collect($eksternal['publishers'] ?? [])->pluck('name')->implode(', ') ?: null,
                'tautan_pratinjau' => $eksternal['url'] ?? null,
                'sampul' => $eksternal['cover']['medium'] ?? null,
            ],
            message: 'Referensi metadata eksternal buku berhasil diambil dari OpenLibrary API.'
        );
    }
}
