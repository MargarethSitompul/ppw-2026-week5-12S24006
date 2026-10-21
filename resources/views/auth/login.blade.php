<x-layout>                                                    {{-- pakai kerangka halaman yang sama --}}
    <div class="max-w-md mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
        <h1 class="text-2xl font-bold mb-4">Login Pustakawan</h1>

        @if ($errors->any())                                  {{-- tampilkan pesan validasi kalau ada --}}
            <div class="mb-4 rounded-md bg-red-50 text-red-700 px-4 py-3 text-sm">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login') }}" method="POST">    {{-- kirim ke AuthController@login --}}
            @csrf                                             {{-- token keamanan --}}
            <label class="block text-sm">Email</label>
            <input type="email" name="email" value="{{ old('email', request()->cookie('del_last_user_email')) }}" required class="mt-1 mb-4 block w-full rounded-md border-gray-300"> {{-- diisi otomatis dari COOKIE login terakhir --}}
            <label class="block text-sm">Password</label>
            <input type="password" name="password" required class="mt-1 mb-4 block w-full rounded-md border-gray-300"> {{-- tipe password: isi tersamarkan --}}
            <label class="flex items-center text-sm mb-4"><input type="checkbox" name="remember" class="mr-2"> Ingat saya</label> {{-- dibaca oleh $request->boolean('remember') --}}
            <button type="submit" class="w-full bg-[#4C1D95] text-white py-2 rounded-md">Masuk</button>
        </form>
    </div>
</x-layout>