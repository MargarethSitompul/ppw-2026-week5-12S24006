<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;

class PeminjamanController extends Controller {
    public function pinjam(Request $request, int $buku_id) {
        $validated = $request->validate([
            'nim_peminjam' => 'required|string|min:6|max:15',
            'nama_peminjam' => 'required|string|min:3|max:150',
        ]);

        try {
            DB::transaction(function () use ($validated, $buku_id) {
                $buku = Buku::where('id', $buku_id)->lockForUpdate()->firstOrFail();

                if ($buku->stok < 1) {
                    throw new \RuntimeException("Stok buku '{$buku->judul}' telah habis!");
                }

                $isBorrowed = Peminjaman::where('buku_id', $buku_id)
                    ->where('nim_peminjam', $validated['nim_peminjam'])
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($isBorrowed) {
                    throw new \RuntimeException("NIM ini sedang meminjam buku yang sama dan belum dikembalikan.");
                }

                $buku->decrement('stok');

                Peminjaman::create([
                    'kode_pinjam' => 'PJM-' . strtoupper(uniqid()),
                    'buku_id' => $buku_id,
                    'nim_peminjam' => strtoupper(trim($validated['nim_peminjam'])),
                    'nama_peminjam' => $validated['nama_peminjam'],
                    'tanggal_pinjam' => now(),
                    'tanggal_jatuh_tempo' => now()->addDays(7),
                    'status' => 'dipinjam',
                ]);
            }, 3);

            return back()->with('success', 'Peminjaman berhasil dicatat!');
        } catch (\RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - PINJAM]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem database.');
        }
    }

    public function kembali(int $peminjaman_id) {
        try {
            DB::transaction(function () use ($peminjaman_id) {
                $peminjaman = Peminjaman::where('id', $peminjaman_id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new \RuntimeException("Buku sudah dikembalikan sebelumnya.");
                }

                $peminjaman->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now(),
                ]);

                $peminjaman->buku()->increment('stok');
            }, 3);

            return back()->with('success', 'Pengembalian buku berhasil diproses!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION - KEMBALI]: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pengembalian.');
        }
    }
}