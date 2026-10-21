<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPUS-Del | Sistem Informasi Perpustakaan IT Del</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.4s ease-out forwards;
        }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900">
    <div class="min-h-full flex flex-col justify-between">
        <div>
            <!-- Navbar / Header dengan Tema Del Purple (#4C1D95) -->
            <nav class="bg-[#4C1D95] shadow-lg sticky top-0 z-50 transition-all duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Logo & Identitas IT Del di Sebelah Kiri -->
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center p-1 shadow-md overflow-hidden">
                               <img src="{{ asset('images/logo.jpg')}}" alt="Logo IT Del" class="w-full h-full object-contain">
                            </div>
                            <div>
                                <span class="text-white font-bold text-lg tracking-wide">SIPUS-Del</span>
                                <span class="block text-purple-200 text-xs">Institut Teknologi Del</span>
                            </div>
                        </div>

                        <!-- Menu Navigasi dengan Animasi Hover -->
                        <div class="hidden md:flex items-center space-x-4">
                            <a href="{{ route('bukus.index') }}" class="text-purple-100 hover:text-white hover:bg-[#3B1578] px-3 py-2 rounded-md text-sm font-medium transition-all duration-200">Daftar Buku</a>
                            <a href="{{ route('kategoris.index') }}" class="text-purple-100 hover:text-white hover:bg-[#3B1578] px-3 py-2 rounded-md text-sm font-medium transition-all duration-200">Kategori</a>
                            <a href="{{ route('peminjamans.index') }}" class="text-purple-100 hover:text-white hover:bg-[#3B1578] px-3 py-2 rounded-md text-sm font-medium transition-all duration-200">Peminjaman</a>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Konten Utama dengan Animasi Fade-In -->
            <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 animate-fade-in">
                <!-- Notifikasi Pesan Sukses (Jika ada) -->
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-md shadow-sm transition-all duration-300">
                        <div class="flex">
                            <div class="text-green-700 text-sm font-medium">
                                {{ session('success') }}
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notifikasi Pesan Error (Jika ada) -->
                @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-md shadow-sm transition-all duration-300">
                        <div class="flex">
                            <div class="text-red-700 text-sm font-medium">
                                {{ session('error') }}
                            </div>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <!-- Footer -->
        <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500">
            <p>&copy; 2026 <strong>SIPUS-Del</strong> &bull; Institut Teknologi Del. All rights reserved.</p>
        </footer>
    </div>
    <div class="flex items-center space-x-3 text-sm text-purple-100">
    @auth                                                 {{-- tampil kalau sudah login --}}
        <span>{{ auth()->user()->name }}</span>            {{-- nama pustakawan yang login --}}
        <form action="{{ route('logout') }}" method="POST">@csrf <button type="submit" class="hover:text-white">Keluar</button></form> {{-- logout wajib POST + @csrf --}}
    @else                                                 {{-- tampil kalau belum login --}}
        <a href="{{ route('login') }}" class="hover:text-white">Login</a>
    @endauth
</div>
</body>
</html>