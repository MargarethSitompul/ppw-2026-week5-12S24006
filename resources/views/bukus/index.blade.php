<x-layout>
    <div class="px-4 sm:px-0 flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Buku SIPUS-Del</h1>
        @auth
            <a href="{{ route('bukus.create') }}" class="bg-[#4C1D95] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-[#3B1578]">Tambah Buku</a>
        @endauth
    </div>

    <!-- Kartu statistik (data dari DB::table di controller) -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Total Judul</p><p class="text-2xl font-bold">{{ $stats['total_judul'] }}</p></div>
        <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Stok Tersedia</p><p class="text-2xl font-bold">{{ $stats['total_stok'] }}</p></div>
        <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Sedang Dipinjam</p><p class="text-2xl font-bold">{{ $stats['sedang_dipinjam'] }}</p></div>
        <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Dikembalikan</p><p class="text-2xl font-bold">{{ $stats['dikembalikan'] }}</p></div>
    </div>

    <!-- Form pencarian dan filter kategori -->
    <form method="GET" action="{{ route('bukus.index') }}" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis, atau ISBN" class="flex-1 rounded-md border-gray-300">
        <select name="kategori_id" class="rounded-md border-gray-300">
            <option value="">Semua Kategori</option>
            @foreach($kategoris as $k)
                <option value="{{ $k->id }}" @selected(request('kategori_id') == $k->id)>{{ $k->nama_kategori }}</option>
            @endforeach
        </select>
        <button class="bg-[#4C1D95] text-white px-4 rounded-md">Cari</button>
    </form>

    @if(session('last_search'))
        <p class="text-xs text-gray-500 mb-2">Pencarian terakhir: {{ session('last_search') }}</p>
    @endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ISBN</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penulis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dipinjam</th>
                    @auth
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    @endauth
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $buku->peminjamans_count }}x</td>
                        @auth
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                @if($buku->stok > 0)
                                    <a href="{{ route('peminjamans.create', ['buku_id' => $buku->id]) }}" class="text-green-600 hover:text-green-900 font-semibold mr-2">Pinjam</a>
                                @else
                                    <span class="text-gray-400 font-normal mr-2 cursor-not-allowed">Stok Habis</span>
                                @endif

                                <a href="{{ route('bukus.edit', $buku->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</a>

                                <form action="{{ route('bukus.destroy', $buku->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                                </form>
                            </td>
                        @endauth
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada buku tersedia.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $bukus->links() }}
    </div>
</x-layout>