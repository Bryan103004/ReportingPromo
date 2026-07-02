@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-7xl">
    
    {{-- Form Paginate & Search Bar Atas (Card Putih) --}}
    <div class="flex flex-wrap justify-between items-center mb-6 shadow-sm p-4 bg-white rounded-lg gap-4">
        <form method="GET" action="{{ route('activity-log.index') }}" id="paginateForm" class="flex items-center gap-2">
            @foreach(request()->except('number') as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v) <input type="hidden" name="{{ $key }}[]" value="{{ $v }}"> @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <label for="number" class="text-sm font-bold text-gray-500">Tampilkan:</label>
            <select id="number" name="number" class="border border-gray-300 rounded-md text-sm font-bold text-gray-600 px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500">
                @foreach($number_paginate as $num)
                    <option value="{{ $num }}" {{ $number == $num ? 'selected' : '' }}>
                        {{ $num == 999999999 ? 'All' : $num }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- ===== SEARCH BAR ===== --}}
        <div class="flex-grow md:max-w-md w-full">
            <x-search-bar 
                placeholder="Masukkan user atau aksi..." 
                tableId="logsTable" 
            />
        </div>
    </div>

    {{-- Header Section (Clear Log, Judul, Total) --}}
    <div class="mb-6 pb-4 border-b border-gray-200">
        <form action="{{ route('activity-log.destroy') }}" method="POST" class="inline-block mb-1">
            @csrf @method('DELETE')
            <button type="submit" class="text-gray-800 hover:text-red-600 font-medium text-sm transition-colors" onclick="return confirm('Yakin clear semua log?')">
                Clear Log
            </button>
        </form>
        <h2 class="text-2xl font-bold text-gray-900 tracking-wide mb-1">Activity Log</h2>
        <p class="text-sm text-gray-600 mb-3">Pantau seluruh aktivitas perubahan data di dalam sistem.</p>
        <span class="inline-block bg-gray-100 text-gray-800 border border-gray-200 text-xs font-bold px-4 py-1.5 rounded-full shadow-sm">
            Total: {{ $logs->total() }} Log
        </span>
    </div>

    {{-- Log Table Card --}}
    <div class="bg-white shadow-sm rounded-xl overflow-hidden mb-6 border border-gray-200">
        <div class="overflow-x-auto">
            <table class="w-full text-left uppercase text-sm" id="logsTable">
                <thead class="bg-gray-50 text-gray-700 text-xs font-bold tracking-wider border-b border-gray-200">
                    <tr>
                        <th class="py-4 px-6">Waktu</th>
                        <th class="py-4 px-6">User</th>
                        <th class="py-4 px-6">Aksi</th>
                        <th class="py-4 px-6">Modul / Subjek</th>
                        <th class="py-4 px-6">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-900">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Waktu --}}
                            <td class="py-4 px-6 whitespace-nowrap align-top">
                                <div class="font-bold">{{ $log->created_at ? $log->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-xs text-gray-500 font-mono mt-1">{{ $log->created_at ? $log->created_at->format('H:i:s') : '-' }} WIB</div>
                            </td>

                            {{-- User --}}
                            <td class="py-4 px-6 align-top">
                                <div class="font-bold">{{ $log->causer ? $log->causer->name : 'System' }}</div>
                                <div class="text-[11px] text-gray-500 tracking-tight mt-1">ID: {{ $log->causer_id ?? '-' }}</div>
                            </td>

                            {{-- Aksi (Tanpa Badge, Seperti Screenshot) --}}
                            <td class="py-4 px-6 align-top">
                                <span class="font-medium text-xs">{{ $log->description }}</span>
                            </td>

                            {{-- Modul / Subjek --}}
                            <td class="py-4 px-6 align-top">
                                <div class="font-bold">{{ class_basename($log->subject_type) }}</div>
                                <div class="text-[11px] font-bold text-gray-500 mt-1">ID: {{ $log->subject_id }}</div>
                            </td>

                            {{-- Properties (JSON) --}}
                            <td class="py-4 px-6 align-top">
                                <button type="button" onclick="toggleProperties('prop-{{ $log->id }}')" class="text-gray-900 hover:text-blue-600 font-bold text-xs flex items-center gap-1 transition-colors">
                                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    Lihat Data
                                </button>
                                <div id="prop-{{ $log->id }}" class="hidden mt-3">
                                    <div class="bg-[#212529] rounded p-3 overflow-y-auto custom-scrollbar max-h-40 text-[11px] font-mono text-gray-200 break-all whitespace-pre-wrap shadow-inner">
                                        {{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center">
                                <div class="text-gray-400 py-3">
                                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <p class="mb-0 font-bold">Tidak ada aktivitas yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center my-6">
        {{ $logs->links() }}
    </div>

    {{-- Form Paginate Bawah --}}
    <form method="GET" action="{{ route('activity-log.index') }}" id="paginateFormBottom" class="flex items-center gap-2 mb-6">
        @foreach(request()->except('number') as $key => $val)
            @if(is_array($val))
                @foreach($val as $v) <input type="hidden" name="{{ $key }}[]" value="{{ $v }}"> @endforeach
            @else
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endif
        @endforeach
        <label for="number-bottom" class="text-sm font-bold text-gray-500">Tampilkan:</label>
        <select id="number-bottom" name="number" class="border border-gray-300 rounded-md text-sm font-bold text-gray-600 px-3 py-1.5 focus:ring-blue-500 focus:border-blue-500" onchange="document.getElementById('paginateFormBottom').submit()">
            @foreach($number_paginate as $num)
                <option value="{{ $num }}" {{ $number == $num ? 'selected' : '' }}>
                    {{ $num == 999999999 ? 'All' : $num }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<style>
    /* Styling scrollbar untuk JSON preview agar tetap estetik */
    .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #212529; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
</style>

<script>
    function toggleProperties(id) {
        document.getElementById(id).classList.toggle('hidden');
    }

    // JS Pagination bawaan kamu tetap sama
    const selectTop = document.getElementById('number');
    const selectBottom = document.getElementById('number-bottom');
    const formTop = document.getElementById('paginateForm');
    const formBottom = document.getElementById('paginateFormBottom');

    if (selectTop && selectBottom) {
        selectTop.addEventListener('change', function() {
            selectBottom.value = selectTop.value;
            formTop.submit();
        });
        selectBottom.addEventListener('change', function(e) {
            selectTop.value = selectBottom.value;
            setTimeout(() => { formBottom.submit(); }, 10);
        });
    }

    const perPageSelect = document.querySelector('select[name="number"]');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function() {
            const url = new URL(window.location.href);
            url.searchParams.set('number', this.value);
            url.searchParams.set('page', '1'); 
            window.location.href = url.toString();
        });
    }
</script>
@endsection