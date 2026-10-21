<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\Kategori; 
use Illuminate\Support\Facades\DB;            // dibutuhkan untuk DB::table di statistik
class BukuController extends Controller
{
 public function index(Request $request)       // sekarang menerima Request supaya bisa membaca kolom pencarian
{
    $bukus = Buku::with('kategori')           // with = ambil kategori sekaligus, cegah N+1
        ->withCount('peminjamans')            // tambah kolom peminjamans_count = berapa kali buku dipinjam
        ->when($request->search, function ($q, $s) {  // when = jalankan hanya kalau kolom pencarian diisi
            $q->where(function ($q) use ($s) {        // kurung grup supaya OR tidak merusak filter lain
                $q->where('judul', 'like', "%{$s}%")      // cari di judul
                  ->orWhere('penulis', 'like', "%{$s}%")  // atau di penulis
                  ->orWhere('isbn', 'like', "%{$s}%");    // atau di ISBN
            });
        })
        ->when($request->kategori_id, fn ($q, $k) => $q->where('kategori_id', $k)) // filter kategori kalau dipilih
        ->latest()                            // data terbaru di atas
        ->paginate(10)                        // 10 buku per halaman
        ->withQueryString();                  // pindah halaman, kata pencarian tidak hilang

    $stats = [                                // kartu statistik, memakai Query Builder (DB::table)
        'total_judul'     => DB::table('bukus')->count(),                  // jumlah judul buku
        'total_stok'      => DB::table('bukus')->sum('stok'),              // jumlah semua stok tersedia
        'sedang_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),     // belum kembali
        'dikembalikan'    => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),  // sudah kembali
    ];

    if ($request->filled('search')) {         // kalau ada kata pencarian
        session(['last_search' => $request->search]); // simpan di SESSION (data tersimpan di server)
    }

    $kategoris = Kategori::orderBy('nama_kategori')->get(); // daftar kategori untuk dropdown filter

    return view('bukus.index', compact('bukus', 'stats', 'kategoris')); // kirim semuanya ke tampilan
}

    public function create()
    {
        $kategoris = Kategori::all();
        return view('bukus.create', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'isbn' => 'required|string|max:20|unique:bukus',
            'judul' => 'required|string|min:5|max:150',
            'penulis' => 'required|string|max:100',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ]);

        Buku::create($request->all());

        return redirect()->route('bukus.index')->with('success', 'Buku berhasil ditambahkan.');
    }

    public function show(Buku $buku)
    {
        return view('bukus.show', compact('buku'));
    }

    public function edit(Buku $buku)
    {
        $kategoris = Kategori::all();
        return view('bukus.edit', compact('buku', 'kategoris'));
    }

    public function update(Request $request, Buku $buku)
    {
        $request->validate([
            'isbn' => 'required|string|max:20|unique:bukus,isbn,' . $buku->id,
            'judul' => 'required|string|min:5|max:150',
            'penulis' => 'required|string|max:100',
            'penerbit' => 'required|string|max:100',
            'tahun_terbit' => 'required|digits:4|integer|min:1900|max:' . date('Y'),
            'kategori_id' => 'required|exists:kategoris,id',
            'stok' => 'required|integer|min:0',
            'sinopsis' => 'nullable|string',
        ]);

        $buku->update($request->all());

        return redirect()->route('bukus.index')->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(Buku $buku)
    {
        $buku->delete();
        return redirect()->route('bukus.index')->with('success', 'Buku berhasil dihapus.');
    }
}