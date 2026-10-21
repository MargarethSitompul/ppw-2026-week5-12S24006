<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\PeminjamanController;

Route::get('/', fn () => redirect()->route('bukus.index'));   // halaman depan diarahkan ke daftar buku

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest'); // halaman login, hanya untuk yang belum login
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');                    // proses login

Route::get('/bukus', [BukuController::class, 'index'])->name('bukus.index'); // melihat katalog boleh tanpa login

Route::middleware('auth')->group(function () {                // semua rute DI DALAM sini wajib login
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::resource('bukus', BukuController::class)->except(['index', 'show']);  // tambah, ubah, hapus buku
    Route::resource('kategoris', KategoriController::class);                     // kelola kategori
    Route::resource('peminjamans', PeminjamanController::class)->only(['index', 'create', 'store']); // daftar dan form pinjam
    Route::post('peminjamans/{id}/kembalikan', [PeminjamanController::class, 'kembalikan'])->name('peminjamans.kembalikan'); // kembalikan buku
});