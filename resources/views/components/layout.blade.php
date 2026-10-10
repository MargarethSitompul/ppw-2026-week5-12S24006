<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIPUS-Del' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <!-- Navbar Del Purple -->
    <nav class="bg-[#4C1D95] text-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-6">
                    <span class="font-bold text-xl tracking-wider">SIPUS-Del</span>
                    <a href="{{ route('kategoris.index') }}" class="hover:bg-[#3B1578] px-3 py-2 rounded-md text-sm font-medium">Kategori</a>
                    <a href="{{ route('bukus.index') }}" class="hover:bg-[#3B1578] px-3 py-2 rounded-md text-sm font-medium">Buku</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative shadow" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        {{ $slot }}
    </main>
</body>
</html>