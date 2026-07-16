@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    
    {{-- Bagian Header Tabel --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Data Master Supplier</h1>
            <p class="text-md font-medium mt-1">Daftar seluruh kode dan nama supplier rafaksi.</p>
        </div>
        
        {{-- Tombol Tambah (Opsional, arahkan ke route create milikmu) --}}
        <a href="{{ route('supplier_rafaksi.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            + Tambah Supplier
        </a>
    </div>

    <x-search-bar 
        placeholder="Masukkan user atau aksi..." 
        tableId="supplier-rafaksi-table" 
    />

    <x-per-page/>
    
    {{-- Tabel Container (Card) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="supplier-rafaksi-table" class="w-full text-left text-sm text-gray-600">
                
                {{-- Head Tabel --}}
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-1/3">Kode Supplier</th>
                        <th class="px-6 py-4">Nama Supplier</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                
                {{-- Body Tabel --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse ($supplier_rafaksi as $supplier)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-semibold text-gray-800">
                            {{ $supplier->kode_supplier }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $supplier->nama_supplier }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('supplier_rafaksi.edit', $supplier->id) }}" class="text-blue-600 hover:text-blue-800">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('supplier_rafaksi.destroy', $supplier->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus supplier ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-.jumbo"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-10 text-center text-gray-500 font-medium">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-.jumbo"></path></svg>
                                Belum ada data supplier yang terdaftar.
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="my-2">
        {{ $supplier_rafaksi->links() }}
    </div>
    
</div>
@endsection