<?php

namespace App\Http\Controllers;

use App\Models\Buku;                          // untuk cek dan ubah stok
use App\Models\Peminjaman;                    // untuk mencatat transaksi
use Illuminate\Database\QueryException;       // jenis error yang datang dari database
use Illuminate\Http\Request;                  // untuk membaca isi form
use Illuminate\Support\Facades\DB;            // untuk memakai transaksi
use Illuminate\Support\Facades\Log;           // untuk menulis error ke file log

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjamans = Peminjaman::with('buku')   // with('buku') = ambil buku sekaligus, cegah N+1
            ->latest()                            // yang terbaru di atas
            ->paginate(10);                       // 10 data per halaman
        return view('peminjamans.index', compact('peminjamans')); // kirim data ke tampilan
    }

    public function create(Request $request)
    {
        $buku = Buku::findOrFail($request->buku_id); // cari buku dari ?buku_id=..., tidak ada = 404
        return view('peminjamans.create', compact('buku')); // tampilkan form pinjam
    }

    public function store(Request $request)
    {
        $request->validate([                      // cek isi form di server
            'buku_id'             => 'required|exists:bukus,id',   // wajib dan buku harus ada
            'nim_peminjam'        => 'required|string|max:15',     // sama dengan panjang kolom di database
            'nama_peminjam'       => 'required|string|max:150',
            'tanggal_pinjam'      => 'required|date',
            'tanggal_jatuh_tempo' => 'required|date|after_or_equal:tanggal_pinjam', // tidak boleh sebelum tanggal pinjam
        ]);

        $nim = strtoupper(trim($request->nim_peminjam)); // rapikan NIM: buang spasi, jadikan huruf besar

        try {
            DB::transaction(function () use ($request, $nim) { // mulai transaksi: gagal di tengah = semua dibatalkan

                $buku = Buku::where('id', $request->buku_id)
                    ->lockForUpdate()             // kunci baris buku, orang lain mengantre
                    ->firstOrFail();              // ambil buku, kalau tidak ada langsung error

                if ($buku->stok <= 0) {           // cek stok SETELAH dikunci, jadi angkanya pasti akurat
                    throw new \RuntimeException('Maaf, stok buku ini sudah habis.'); // lempar error = rollback
                }

                $sudahPinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', $nim)
                    ->where('status', 'dipinjam') // pinjaman NIM ini yang belum dikembalikan
                    ->exists();                   // true kalau ada

                if ($sudahPinjam) {               // aturan modul: NIM sama tidak boleh pinjam buku sama dua kali
                    throw new \RuntimeException('NIM ini masih meminjam buku yang sama.');
                }

                $buku->decrement('stok');         // langkah 1: stok berkurang 1

                Peminjaman::create([              // langkah 2: catat ke tabel peminjamans
                    'kode_pinjam'         => 'KP-' . strtoupper(uniqid()), // kode unik otomatis
                    'buku_id'             => $buku->id,
                    'nim_peminjam'        => $nim,
                    'nama_peminjam'       => $request->nama_peminjam,
                    'tanggal_pinjam'      => $request->tanggal_pinjam,
                    'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
                    'status'              => 'dipinjam', // status awal
                ]);
            });                                   // selesai tanpa error = disimpan permanen (commit)

            return redirect()->route('peminjamans.index')
                ->with('success', 'Peminjaman berhasil dicatat dan stok diperbarui!'); // pesan sekali tampil (flash)
        } catch (\RuntimeException $e) {          // error aturan bisnis: stok habis, NIM ganda
            return back()->with('error', $e->getMessage())->withInput(); // kembali ke form, isian tidak hilang
        } catch (QueryException $e) {             // error dari database
            Log::error('[DB EXCEPTION] ' . $e->getMessage()); // catat detail teknis di storage/logs/laravel.log
            return back()->with('error', 'Terjadi kesalahan database.')->withInput(); // pesan ramah untuk pengguna
        }
    }

    public function kembalikan($id)
    {
        try {
            DB::transaction(function () use ($id) { // transaksi lagi, karena ada dua perubahan sekaligus
                $peminjaman = Peminjaman::lockForUpdate() // kunci baris peminjaman
                    ->findOrFail($id);            // ambil datanya

                if ($peminjaman->status === 'dikembalikan') { // cegah klik Kembalikan dua kali
                    throw new \RuntimeException('Buku ini sudah dikembalikan sebelumnya.');
                }

                $peminjaman->update([             // perubahan 1: ubah status peminjaman
                    'status'          => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(), // isi tanggal hari ini
                ]);

                Buku::where('id', $peminjaman->buku_id)
                    ->lockForUpdate()->firstOrFail() // kunci juga baris bukunya
                    ->increment('stok');          // perubahan 2: stok bertambah 1
            });

            return redirect()->route('peminjamans.index')
                ->with('success', 'Buku berhasil dikembalikan!');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage()); // tampilkan pesan aturan bisnis
        } catch (QueryException $e) {
            Log::error('[DB EXCEPTION] ' . $e->getMessage()); // catat error database
            return back()->with('error', 'Terjadi kesalahan database.');
        }
    }
}