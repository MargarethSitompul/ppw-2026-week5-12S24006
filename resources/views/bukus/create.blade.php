<x-layout>
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold mb-4">Tambah Buku Baru</h1>
        <form action="{{ route('bukus.store') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">ISBN</label>
                <input type="text" name="isbn" value="{{ old('isbn') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                @error('isbn') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Judul Buku</label>
                <input type="text" name="judul" value="{{ old('judul') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                @error('judul') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Penulis</label>
                    <input type="text" name="penulis" value="{{ old('penulis') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                    @error('penulis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Penerbit</label>
                    <input type="text" name="penerbit" value="{{ old('penerbit') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                    @error('penerbit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="grid grid-cols-3 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tahun Terbit</label>
                    <input type="number" name="tahun_terbit" value="{{ old('tahun_terbit') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                    @error('tahun_terbit') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kategori</label>
                    <select name="kategori_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Stok</label>
                    <input type="number" name="stok" value="{{ old('stok', 0) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                    @error('stok') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Sinopsis</label>
                <textarea name="sinopsis" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2">{{ old('sinopsis') }}</textarea>
                @error('sinopsis') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="bg-[#4C1D95] text-white px-4 py-2 rounded-md hover:bg-[#3B1578]">Simpan</button>
            <a href="{{ route('bukus.index') }}" class="ml-2 text-gray-600">Batal</a>
        </form>
    </div>
</x-layout>