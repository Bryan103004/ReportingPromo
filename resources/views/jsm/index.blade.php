@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    {{-- Bagian Header Tabel --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Rekapitulasi Rafaksi JSM</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar total transaksi dan nominal rafaksi jsm yang dikelompokkan per bulan.</p>
        </div>
        
        {{-- Tombol Tambah Rafaksi Baru --}}
        <a href="{{ route('jsm.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Tambah Rafaksi JSM Baru
        </a>
    </div>

    <div class="flex ms-auto m-4 gap-2">
        <!-- <a href="{{ route('rafaksi.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
            Export Rekap CSV
        </a> -->
        <a href="{{ route('jsm.export.excel') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Export Rekap XLS
        </a>
    </div>

    {{-- ===== SEARCH BAR ===== --}}
    <x-search-bar 
        placeholder="Masukkan user atau aksi..." 
        tableId="jsmTable" 
    />

    {{-- Tabel Container (Card) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="jsmTable" class="w-full text-left text-sm text-gray-600">
                
                {{-- Head Tabel --}}
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="hidden px-6 py-4">Periode Rafaksi</th>
                        <th class="px-6 py-4">Periode Bulan</th>
                        <th class="px-6 py-4">Store</th>
                        <th class="px-6 py-4 text-center">Total Transaksi</th>
                        <th class="px-6 py-4 text-right">Total Nominal</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                
                {{-- Body Tabel --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse ($jsmGroups as $group)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-center font-medium text-gray-500">
                                {{ $loop->iteration }}
                            </td>
                            <td class="hidden px-6 py-4 font-bold text-gray-800">
                                {{-- Mengubah angka bulan & tahun menjadi teks (Contoh: "Mei 2026") --}}
                                {{ \Carbon\Carbon::createFromDate($group->year, $group->month, 1)->translatedFormat('F Y') }}
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{-- Mengubah angka bulan & tahun menjadi teks (Contoh: "Mei 2026") --}}
                                {{ \Carbon\Carbon::createFromDate($group->year_kerja, $group->month_kerja, 1)->translatedFormat('F Y') }}
                            </td>                           
                            <td class="px-6 py-4">
                                {{ $group->store }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-blue-50 text-blue-700 font-bold px-2.5 py-1 rounded-full text-xs">
                                    {{ $group->total_data }} Data
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-700">
                                Rp {{ number_format($group->total_nominal, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{-- Tombol menuju halaman detail per bulan --}}
                                <a href="{{ route('jsm.show_month', ['year' => $group->year_kerja, 'month' => $group->month_kerja]) }}" 
                                   class="inline-flex items-center justify-center px-3 py-1.5 text-xs font-bold text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50 hover:text-blue-600 transition-colors">
                                   <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                   Lihat Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="font-medium text-gray-500">Belum ada data rafaksi yang dicatat.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                
            </table>
        </div>
    </div>
    
</div>
@endsection