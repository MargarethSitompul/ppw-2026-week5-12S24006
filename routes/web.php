<?php


Route::get('/', function () {
    return redirect('/bukus');
});

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\BukuController;

Route::get('/', function () {
    return view('welcome');
});

Route::resource('kategoris', KategoriController::class);
Route::resource('bukus', BukuController::class);