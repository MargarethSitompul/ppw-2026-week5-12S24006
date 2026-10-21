<x-layout>
    <div class="px-4 sm:px-0 flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Daftar Sirkulasi Peminjaman</h1>
        <a href="{{ route('bukus.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-700">Pilih Buku Dipinjam</a>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-md">
            {{ session('error') }}
        </div>
    @endif
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">     {{-- 4 kartu statistik berjajar --}}
    <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Total Judul</p><p class="text-2xl font-bold">{{ $stats['total_judul'] }}</p></div>
    <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Stok Tersedia</p><p class="text-2xl font-bold">{{ $stats['total_stok'] }}</p></div>
    <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Sedang Dipinjam</p><p class="text-2xl font-bold">{{ $stats['sedang_dipinjam'] }}</p></div>
    <div class="bg-white p-4 rounded-lg shadow"><p class="text-xs text-gray-500">Dikembalikan</p><p class="text-2xl font-bold">{{ $stats['dikembalikan'] }}</p></div>
</div>

<form method="GET" action="{{ route('bukus.index') }}" class="flex gap-2 mb-4">  {{-- GET: kata pencarian muncul di URL --}}
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis, atau ISBN" class="flex-1 rounded-md border-gray-300"> {{-- isi kembali kata yang tadi dicari --}}
    <select name="kategori_id" class="rounded-md border-gray-300">
        <option value="">Semua Kategori</option>
        @foreach($kategoris as $k)                           {{-- satu pilihan per kategori --}}
            <option value="{{ $k->id }}" @selected(request('kategori_id') == $k->id)>{{ $k->nama_kategori }}</option> {{-- @selected = pilihan tetap terpilih --}}
        @endforeach
    </select>
    <button class="bg-[#4C1D95] text-white px-4 rounded-md">Cari</button>
</form>

@if(session('last_search'))                                   {{-- data dari SESSION --}}
    <p class="text-xs text-gray-500 mb-2">Pencarian terakhir: {{ session('last_search') }}</p>
@endif

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul Buku</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Peminjam</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dipinjam</th>  {{-- judul kolom, taruh setelah kolom Stok --}}
<td class="px-6 py-4 text-sm text-gray-500">{{ $buku->peminjamans_count }}x</td>               {{-- hasil withCount --}}
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($peminjamans as $peminjaman)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $peminjaman->buku->judul ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $peminjaman->nama_peminjam ?? 'Mahasiswa/Staff' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $peminjaman->tanggal_pinjam }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @auth                                         {{-- isi di dalam hanya tampil kalau sudah login --}}
    <a href="{{ route('bukus.edit', $buku->id) }}">Edit</a>
    {{-- ...form Hapus dan link Pinjam tetap di dalam blok ini --}}
@endauth
                          @if($peminjaman->status === 'dipinjam') 
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Dipinjam</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Dikembalikan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                         @if($peminjaman->status === 'dipinjam') 
                         <td>{{ $peminjaman->tanggal_jatuh_tempo->format('d M Y') }}</td> {{-- tampil rapi berkat $casts di model --}}
                                <form action="{{ route('peminjamans.kembalikan', $peminjaman->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-xs font-semibold" onclick="return confirm('Konfirmasi pengembalian buku ini?')">Kembalikan</button>
                                </form>
                            @else
                                <span class="text-gray-400 text-xs">Selesai</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada transaksi peminjaman.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $peminjamans->links() }}
    </div>
    
</x-layout>