<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    // Menampilkan Daftar / Riwayat Peminjaman Buku
    public function index()
    {
        $peminjamans = Peminjaman::with('buku')->latest()->paginate(10);
        return view('peminjamans.index', compact('peminjamans'));
    }

    // Form Peminjaman Buku
    public function create(Request $request)
    {
        $buku = Buku::findOrFail($request->buku_id);
        return view('peminjamans.create', compact('buku'));
    }

    // Simpan Peminjaman Baru (Kurangi Stok dengan DB Transaction)
    public function store(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:bukus,id',
            'tanggal_pinjam' => 'required|date',
            'nama_peminjam' => 'required|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $buku = Buku::where('id', $request->buku_id)->lockForUpdate()->first();

                if ($buku->stok <= 0) {
                    throw new \Exception('Maaf, stok buku ini sudah habis.');
                }

                // Kurangi stok buku
                $buku->decrement('stok');

                // Simpan transaksi peminjaman
                Peminjaman::create([
                    'buku_id' => $buku->id,
                    'nama_peminjam' => $request->nama_peminjam,
                    'tanggal_pinjam' => $request->tanggal_pinjam,
                    'status' => 'DIPINJAM',
                ]);
            });

            return redirect()->route('peminjamans.index')->with('success', 'Peminjaman berhasil dicatat!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // Proses Pengembalian Buku (Tambah Stok Kembali)
    public function kembalikan($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $peminjaman = Peminjaman::findOrFail($id);

                if ($peminjaman->status === 'DIKEMBALIKAN') {
                    throw new \Exception('Buku ini sudah dikembalikan sebelumnya.');
                }

                // Update status peminjaman
                $peminjaman->update([
                    'status' => 'DIKEMBALIKAN',
                    'tanggal_kembali' => now(),
                ]);

                // Tambahkan kembali stok buku
                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();
                $buku->increment('stok');
            });

            return redirect()->route('peminjamans.index')->with('success', 'Buku berhasil dikembalikan dan stok telah diperbarui!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}