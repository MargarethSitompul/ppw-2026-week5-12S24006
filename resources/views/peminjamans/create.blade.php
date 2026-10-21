<x-layout>
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Form Peminjaman Buku</h1>

        @if (session('error'))
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('peminjamans.store') }}" method="POST">
            @csrf

            <!-- ID Buku -->
            <input type="hidden" name="buku_id" value="{{ $buku->id }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Judul Buku yang Dipilih</label>
                <input type="text" value="{{ $buku->judul }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" disabled>
            </div>

            <div class="mb-4">
                <label for="nim_peminjam" class="block text-sm font-medium text-gray-700">NIM Peminjam</label>
                <input type="text" name="nim_peminjam" id="nim_peminjam" value="{{ old('nim_peminjam') }}" maxlength="15" placeholder="Contoh: 12S24006" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="mb-4">
                <label for="nama_peminjam" class="block text-sm font-medium text-gray-700">Nama Peminjam</label>
                <input type="text" name="nama_peminjam" id="nama_peminjam" value="{{ old('nama_peminjam') }}" placeholder="Masukkan nama lengkap" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="mb-4">
                <label for="tanggal_pinjam" class="block text-sm font-medium text-gray-700">Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" id="tanggal_pinjam" value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="mb-4">
                <label for="tanggal_jatuh_tempo" class="block text-sm font-medium text-gray-700">Tanggal Jatuh Tempo</label>
                <input type="date" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" value="{{ old('tanggal_jatuh_tempo', date('Y-m-d', strtotime('+7 days'))) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('bukus.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400">Batal</a>
                <button type="submit" class="bg-[#4C1D95] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-[#3B1578]">Simpan Peminjaman</button>
            </div>
        </form>
    </div>
</x-layout>