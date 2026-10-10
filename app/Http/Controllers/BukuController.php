<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index()
    {
        // Menerapkan Eager Loading untuk mencegah N+1 problem
        $bukus = Buku::with('kategori')->latest()->paginate(10);
        return view('bukus.index', compact('bukus'));
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