<x-layout>
    <div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold mb-4">Edit Kategori</h1>
        <form action="{{ route('kategoris.update', $kategori->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Kode Kategori</label>
                <input type="text" name="kode_kategori" value="{{ old('kode_kategori', $kategori->kode_kategori) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                @error('kode_kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Nama Kategori</label>
                <input type="text" name="nama_kategori" value="{{ old('nama_kategori', $kategori->nama_kategori) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm border p-2" required>
                @error('nama_kategori') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="bg-[#4C1D95] text-white px-4 py-2 rounded-md hover:bg-[#3B1578]">Perbarui</button>
            <a href="{{ route('kategoris.index') }}" class="ml-2 text-gray-600">Batal</a>
        </form>
    </div>
</x-layout>