<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Peminjaman;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat 5 Kategori tetap sesuai domain perpustakaan
        $kategoris = [
            ['kode_kategori' => 'KAT01', 'nama_kategori' => 'Teknologi & Informasi'],
            ['kode_kategori' => 'KAT02', 'nama_kategori' => 'Sains & Matematika'],
            ['kode_kategori' => 'KAT03', 'nama_kategori' => 'Ekonomi & Bisnis'],
            ['kode_kategori' => 'KAT04', 'nama_kategori' => 'Fiksi & Sastra'],
            ['kode_kategori' => 'KAT05', 'nama_kategori' => 'Sejarah & Budaya'],
        ];

        foreach ($kategoris as $kat) {
            Kategori::create($kat);
        }

        // Buat 20 Buku menggunakan Factory
        Buku::factory(20)->create();
                User::create([                        // akun pustakawan untuk uji
            'name'     => 'Pustakawan Del',
            'email'    => 'pustakawan@del.ac.id', // sesuai modul
            'password' => 'password123',      // otomatis di-hash oleh cast 'hashed' di model User
        ]);

        $kategori = Kategori::first();        // ambil satu kategori untuk sampel buku

        Buku::create([                        // sampel buku stok 0, untuk demo penolakan
            'isbn' => '9780000000001', 'judul' => 'Buku Uji Stok Nol', 'penulis' => 'Tim PPW',
            'penerbit' => 'Del Press', 'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id, 'stok' => 0, 'sinopsis' => 'Untuk menguji stok habis.',
        ]);

        $satu = Buku::create([                // sampel buku stok 1, untuk demo pinjam terakhir
            'isbn' => '9780000000002', 'judul' => 'Buku Uji Stok Satu', 'penulis' => 'Tim PPW',
            'penerbit' => 'Del Press', 'tahun_terbit' => 2024,
            'kategori_id' => $kategori->id, 'stok' => 1, 'sinopsis' => 'Untuk menguji stok tinggal satu.',
        ]);

        $contoh = Buku::first();              // satu buku dari factory untuk sampel pinjaman
        $contoh->decrement('stok');           // stok dikurangi 1 supaya sesuai dengan pinjaman di bawah
        Peminjaman::create([                  // sampel 1: sedang dipinjam
            'kode_pinjam' => 'KP-SAMPLE01', 'buku_id' => $contoh->id, 'nim_peminjam' => '12S24001',
            'nama_peminjam' => 'Contoh Mahasiswa', 'tanggal_pinjam' => now(),
            'tanggal_jatuh_tempo' => now()->addDays(7), 'status' => 'dipinjam',
        ]);

        Peminjaman::create([                  // sampel 2: sudah dikembalikan (stok tetap, jadi tidak perlu decrement)
            'kode_pinjam' => 'KP-SAMPLE02', 'buku_id' => $satu->id, 'nim_peminjam' => '12S24002',
            'nama_peminjam' => 'Contoh Mahasiswa 2', 'tanggal_pinjam' => now()->subDays(10),
            'tanggal_jatuh_tempo' => now()->subDays(3), 'tanggal_kembali' => now()->subDays(4),
            'status' => 'dikembalikan',
        ]);
    }
}