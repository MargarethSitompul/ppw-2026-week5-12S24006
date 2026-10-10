<x-layout>
    <div class="px-4 sm:px-0 flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Buku SIPUS-Del</h1>
        <a href="{{ route('bukus.create') }}" class="bg-[#4C1D95] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-[#3B1578]">Tambah Buku</a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penulis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($bukus as $buku)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $buku->isbn }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $buku->judul }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $buku->penulis }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $buku->kategori->nama_kategori ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $buku->stok }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('bukus.edit', $buku->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="{{ route('bukus.destroy', $buku->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada buku tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $bukus->links() }}
    </div>
</x-layout>