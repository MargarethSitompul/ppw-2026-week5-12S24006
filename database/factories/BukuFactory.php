<?php

namespace Database\Factories;

use App\Models\Kategori;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'isbn' => $this->faker->unique()->isbn13(),
            'judul' => $this->faker->sentence(3),
            'penulis' => $this->faker->name(),
            'penerbit' => $this->faker->company() . ' Publisher',
            'tahun_terbit' => $this->faker->numberBetween(2015, 2026),
            'kategori_id' => Kategori::inRandomOrder()->first()?->id ?? 1,
            'stok' => $this->faker->numberBetween(5, 50),
            'sinopsis' => $this->faker->paragraph(),
        ];
    }
}