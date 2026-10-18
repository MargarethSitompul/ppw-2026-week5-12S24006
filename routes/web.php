<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PeminjamanController;

// Redirect Halaman Utama ke /bukus
Route::get('/', function () {
    return redirect('/bukus');
});

// Resource Routes
Route::resource('kategoris', KategoriController::class);
Route::resource('bukus', BukuController::class);

// Resource & Aksi Pengembalian Peminjaman
Route::resource('peminjamans', PeminjamanController::class);
Route::post('peminjamans/{peminjaman}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjamans.kembalikan');