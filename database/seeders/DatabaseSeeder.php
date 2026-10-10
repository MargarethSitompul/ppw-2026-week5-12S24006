<?php

namespace Database\Seeders;

use App\Models\Kategori;
use App\Models\Buku;
use Illuminate\Database\Seeder;

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
    }
}