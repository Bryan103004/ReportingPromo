@extends('layouts.app')
@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold">Data Region</h2>
                    <button onclick="document.getElementById('createModal').classList.remove('hidden')" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        + Tambah Region
                    </button>
                </div>

                {{-- ===== SEARCH BAR ===== --}}
                <x-search-bar 
                    placeholder="Masukkan region atau lainnya..." 
                    tableId="regionTable" 
                />

                <x-per-page/>

                <table id="regionTable" class="w-full table-auto border-collapse border border-gray-200">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">ID</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Kode Region</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Nama Region</th>
                            <th class="border border-gray-300 px-4 py-2 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($regions as $region)
                            <tr>
                                <td class="border border-gray-300 px-4 py-2">{{ $region->id }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $region->kode_region }}</td>
                                <td class="border border-gray-300 px-4 py-2">{{ $region->nama_region }}</td>
                                <td class="border border-gray-300 px-4 py-2 text-center">
                                    <a class="inline-flex users-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-gray-100" href="{{ route('region.edit', $region->id) }}" class="text-yellow-600 hover:text-yellow-800 font-bold mr-2">Edit</a>
                                    
                                    <form action="{{ route('region.destroy', $region->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex users-center rounded-md border border-red-300 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-gray-500">Belum ada data region.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $regions->links() }}
                </div>
            </div>
        </div>
    </div>

    <div id="createModal" class="hidden fixed inset-0 bg-gray-100 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
        <div class="relative bg-white rounded-lg shadow-lg w-1/3">
            <div class="flex justify-between items-center border-b px-4 py-3">
                <h3 class="text-lg font-bold">Tambah Region Baru</h3>
                <button onclick="document.getElementById('createModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-900">&times;</button>
            </div>
            
            <form action="{{ route('region.store') }}" method="POST">
                @csrf
                <div class="p-4">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kode Region</label>
                        <input type="text" name="kode_region" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Region</label>
                        <input type="text" name="nama_region" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </div>
                <div class="border-t px-4 py-3 flex justify-end">
                    <button type="button" onclick="document.getElementById('createModal').classList.add('hidden')" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Simpan</button>
                </div>
            </form>
        </div>
    </div>
@endsection