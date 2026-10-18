<x-layout>
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-md mt-6">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Form Peminjaman Buku</h1>

        <form action="{{ route('peminjamans.store') }}" method="POST">
            @csrf

            <!-- ID Buku -->
            <input type="hidden" name="buku_id" value="{{ $buku->id }}">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Judul Buku yang Dipilih</label>
                <input type="text" value="{{ $buku->judul }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100" disabled>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nama Peminjam</label>
                <input type="text" name="nama_peminjam" placeholder="Masukkan nama peminjam" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Tanggal Pinjam</label>
                <input type="date" name="tanggal_pinjam" value="{{ date('Y-m-d') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('bukus.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400">Batal</a>
                <button type="submit" class="bg-[#4C1D95] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-[#3B1578]">Simpan Peminjaman</button>
            </div>
        </form>
    </div>
</x-layout>