<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;          // untuk login dan logout
use Illuminate\Support\Facades\Cookie;        // untuk menyimpan cookie

class AuthController extends Controller
{
    public function showLogin()               // method baru
    {
        return view('auth.login');            // tampilkan halaman login
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([   // cek isian login
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) { // cocokkan email dan password dengan tabel users
            $request->session()->regenerate();   // ganti nomor session baru, cegah serangan Session Fixation
            session(['login_timestamp' => now()->format('d M Y, H:i:s') . ' WIB']); // simpan waktu login di SESSION
            Cookie::queue('del_last_user_email', $request->email, 10080); // simpan email di COOKIE selama 7 hari (10080 menit)

            return redirect()->intended(route('bukus.index')) // kembali ke halaman yang tadi dituju, atau ke daftar buku
                ->with('success', 'Selamat datang, Pustakawan!'); // pesan sekali tampil (flash)
        }

        return back()                         // login gagal: kembali ke form
            ->with('error', 'Email atau password salah.')
            ->withInput($request->only('email')); // hanya email yang dikembalikan, password tidak
    }

    public function logout(Request $request)
    {
        Auth::logout();                       // keluarkan pengguna
        $request->session()->invalidate();    // hapus seluruh isi session di server
        $request->session()->regenerateToken(); // buat token CSRF baru
        return redirect()->route('login')->with('success', 'Anda sudah keluar.');
    }
}