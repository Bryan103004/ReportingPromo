@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs font-bold text-indigo-600 uppercase tracking-wider">Data Master</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>📑</span> Data Master Biltu
            </h1>
            <p class="text-sm text-gray-600 mt-1">
                Daftar master data partner Biltu (C_BPartner_ID), relasi dengan supplier rafaksi, dan kategori PT (MBA / Non-MBA).
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('biltu.import-logs') }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition shadow-sm uppercase tracking-wider">
                <span class="mr-1.5">📋</span> Log Penarikan Biltu
            </a>
            <a href="{{ route('biltu.create') }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition shadow-sm uppercase tracking-wider">
                <span class="mr-1.5">+</span> Tambah Biltu
            </a>
        </div>
    </div>

    {{-- Flash Message Success --}}
    @if(session('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4 text-sm text-green-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-green-600 hover:text-green-800 font-bold">&times;</button>
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="GET" action="{{ route('biltu.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            
            {{-- Keyword Search --}}
            <div class="md:col-span-5">
                <label class="block text-xs font-bold uppercase text-gray-600 tracking-wider mb-1.5">
                    Cari Data (C_BPartner_ID / Value / Nama / Vendor / NPWP)
                </label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                        placeholder="Contoh: 1125516, C00010, FRISIAN, 02723..." 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    @if(request('search'))
                        <a href="{{ route('biltu.index', request()->except('search')) }}" class="absolute right-3 top-2.5 text-gray-400 hover:text-gray-600 text-xs font-bold">✕</a>
                    @endif
                </div>
            </div>

            {{-- Filter Tipe PT --}}
            <div class="md:col-span-3">
                <label class="block text-xs font-bold uppercase text-gray-600 tracking-wider mb-1.5">
                    Tipe PT (Kategori)
                </label>
                <select name="filter_is_mba" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="all" {{ request('filter_is_mba') === 'all' || !request()->has('filter_is_mba') ? 'selected' : '' }}>Semua Tipe PT</option>
                    <option value="1" {{ request('filter_is_mba') === '1' ? 'selected' : '' }}>PT. MBA (Prefix JC)</option>
                    <option value="0" {{ request('filter_is_mba') === '0' ? 'selected' : '' }}>Non-MBA / GrandLucky (Prefix C)</option>
                </select>
            </div>

            {{-- Filter Supplier --}}
            <div class="md:col-span-4">
                <label class="block text-xs font-bold uppercase text-gray-600 tracking-wider mb-1.5">
                    Supplier Rafaksi
                </label>
                <select name="filter_vendor_no" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->kode_supplier }}" {{ request('filter_vendor_no') == $sup->kode_supplier ? 'selected' : '' }}>
                            {{ $sup->kode_supplier }} - {{ $sup->nama_supplier }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Actions Filter --}}
            <div class="md:col-span-12 flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                @if(request()->hasAny(['search', 'filter_is_mba', 'filter_vendor_no', 'per_page']))
                    <a href="{{ route('biltu.index') }}" class="px-4 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition uppercase tracking-wider">
                        Reset Filter
                    </a>
                @endif
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm uppercase tracking-wider">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    {{-- Info Card Total --}}
    <div class="flex items-center justify-between text-xs text-gray-500 mb-3 px-1">
        <div>
            Menampilkan <span class="font-bold text-gray-800">{{ $biltus->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-800">{{ $biltus->lastItem() ?? 0 }}</span> dari total <span class="font-bold text-gray-800">{{ $biltus->total() }}</span> partner Biltu.
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span> PT. MBA</span>
            <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Non-MBA</span>
        </div>
    </div>

    {{-- Tabel Master Biltu --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 text-center w-12">#</th>
                        <th class="px-4 py-3.5">C_BPartner_ID</th>
                        <th class="px-4 py-3.5">Search Key (Value)</th>
                        <th class="px-4 py-3.5">Nama Partner</th>
                        <th class="px-4 py-3.5 text-center">Tipe PT</th>
                        <th class="px-4 py-3.5">Relasi Supplier</th>
                        <th class="px-4 py-3.5">NPWP / Tax ID</th>
                        <th class="px-4 py-3.5">Kota</th>
                        <th class="px-4 py-3.5 text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($biltus as $b)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-center font-mono text-xs text-gray-400">
                            {{ $loop->iteration + ($biltus->currentPage() - 1) * $biltus->perPage() }}
                        </td>
                        <td class="px-4 py-3 font-mono font-bold text-gray-900 text-xs">
                            {{ $b->C_BPartner_ID }}
                        </td>
                        <td class="px-4 py-3 font-mono text-xs">
                            <span class="px-2 py-0.5 rounded font-bold {{ $b->is_mba ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                {{ $b->value ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-bold text-gray-800 text-xs">{{ $b->name }}</div>
                            @if($b->name2)
                                <div class="text-[11px] text-gray-400">{{ $b->name2 }}</div>
                            @endif
                            @if($b->client)
                                <div class="text-[10px] text-gray-400 font-mono mt-0.5">Client: {{ $b->client }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($b->is_mba)
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase tracking-wider">
                                    PT. MBA
                                </span>
                            @else
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase tracking-wider">
                                    Non-MBA
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($b->supplier)
                                <div class="text-xs font-semibold text-indigo-700">
                                    {{ $b->supplier->nama_supplier }}
                                </div>
                                <div class="text-[11px] text-gray-400 font-mono">
                                    Vendor No: {{ $b->vendor_no }}
                                </div>
                            @elseif($b->vendor_no)
                                <span class="font-mono text-xs font-medium text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200" title="Kode supplier belum terdaftar di Master Supplier Rafaksi">
                                    Vendor: {{ $b->vendor_no }} (Unlinked)
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Tidak ada relasi</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-gray-600">
                            {{ $b->taxid ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $b->City ?: '-' }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('biltu.edit', $b->id) }}" class="p-1 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded" title="Edit Biltu">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                                <form action="{{ route('biltu.destroy', $b->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data Biltu ini: {{ $b->C_BPartner_ID }} - {{ $b->name }}?')" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-red-600 hover:text-red-800 hover:bg-red-50 rounded" title="Hapus Biltu">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                            <div class="flex flex-col items-center justify-center">
                                <span class="text-3xl mb-2">📑</span>
                                <p class="font-medium text-gray-600">Tidak ada data Master Biltu yang ditemukan.</p>
                                <p class="text-xs text-gray-400 mt-1">Gunakan tombol tambah di atas atau pastikan proses import scheduler berjalan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($biltus->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $biltus->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
