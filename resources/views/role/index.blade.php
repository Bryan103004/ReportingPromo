@extends('layouts.app')
@section('content')

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 flex items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Data Master Roles</h1>
            <p class="mt-1 text-sm text-gray-600">Daftar role yang tersedia.</p>
        </div>
        <a class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium hover:bg-gray-100" href="{{ route('role.create') }}">+ Buat Role</a>
    </div>

        @if (session('success'))
            <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
        @endif

        {{-- ===== SEARCH BAR ===== --}}
        <x-search-bar 
            placeholder="Masukkan user atau aksi..." 
            tableId="roleTable" 
        />

        <!-- Filter Dropdown Button -->
        <div class="mb-6">
            <button type="button" id="filterToggle" class="flex items-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                Filter Lanjutan
                <svg class="h-4 w-4 transition-transform" id="filterToggleIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </button>
        </div>
    
        {{-- Form Paginate Atas --}}
        <form method="GET" action="{{ route('role.index') }}" id="paginateForm" class="inline-block mb-4">
            @foreach(request()->except('number') as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v) <input type="hidden" name="{{ $key }}[]" value="{{ $v }}"> @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <select id="number" name="number" class="rounded-md border-gray-300 border px-6 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                @foreach($number_paginate as $num)
                    <option value="{{ $num }}" {{ $number == $num ? 'selected' : '' }}>
                        {{ $num == 999999999 ? 'All' : $num }}
                    </option>
                @endforeach
            </select>
        </form>

        <!-- Filter Section (Hidden by default) -->
        <div id="filterForm" class="mb-6 hidden rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('role.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Nama Role -->
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama Role</label>
                    <input type="text" id="name" name="name" value="{{ request('name') }}"
                        placeholder="Cari nama role..." class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <!-- Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">Terapkan Filter</button>
                    <a href="{{ route('role.index') }}" class="flex-1 border border-gray-300 bg-white px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 text-center">Reset</a>
                </div>
            </form>
        </div>

        <div class="my-4">
            {{ $roles->links() }}
        </div>

        {{-- Tabel Section --}}
        <div id="scrollbar-top" class="overflow-x-auto border-x border-t border-gray-200 bg-white" style="height: 12px;">
            <div style="width: 1800px; height: 1px;"></div>
        </div>

        <div id="scrollbar-bottom" class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
        <table id="roleTable" class="uppercase min-w-full table-fixed divide-y divide-gray-200 text-sm">
            <thead>
                <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-600">
                    <th class="w-16 px-4 py-3 !font-black">No</th>
                    <th class="w-56 px-4 py-3 !font-black">Nama Role</th>
                    <th class="w-96 px-4 py-3 !font-black">Permissions</th>
                    <th class="w-40 px-4 py-3 !font-black">Dibuat</th>
                    <th class="w-40 px-4 py-3 !font-black">Diperbarui</th>
                    <th class="w-48 px-4 py-3 !font-black">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($roles as $item)
                    <tr>
                        <td class="px-4 py-3">
                            {{ ($roles->currentPage() - 1) * $roles->perPage() + $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">{{ $item->name }}</td>
                        <td class="px-4 py-3 align-top">
                            <details class="group">
                                <summary class="flex cursor-pointer list-none items-center justify-between rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-100">
                                    <span>{{ $item->permissions->count() }} permissions</span>
                                    <svg class="h-4 w-4 transition-transform group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </summary>

                                <div class="mt-2 max-h-40 overflow-y-auto rounded-md border border-gray-100 bg-white p-2">
                                    @forelse ($item->permissions as $permission)
                                        <span class="mb-1 mr-1 inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                            {{ $permission->name }}
                                        </span>
                                    @empty
                                        <p class="text-xs text-gray-500">Belum ada permission.</p>
                                    @endforelse
                                </div>
                            </details>
                        </td>
                        <td class="px-4 py-3">{{ $item->created_at ? $item->created_at->format('d M Y H:i:s') : '-' }}</td>
                        <td class="px-4 py-3">{{ $item->updated_at ? $item->updated_at->format('d M Y H:i:s') : '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap items-center gap-2">
                                <a class="inline-flex items-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-gray-100" href="{{ route('role.edit', $item->id) }}">Edit</a>
                                <form action="{{ route('role.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center rounded-md border border-red-300 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada data roles.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <div class="mt-4">
            {{ $roles->links() }}
        </div>

        <form method="GET" action="{{ route('role.index') }}" id="paginateFormBottom" class="inline-block mt-2 mb-2">
            @foreach(request()->except('number') as $key => $val)
                @if(is_array($val))
                    @foreach($val as $v)
                        <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                    @endforeach
                @else
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
            <select id="number-bottom" name="number" class="rounded-md border-gray-300 border px-6 py-2 text-sm focus:border-blue-500 focus:ring-blue-500" onchange="document.getElementById('paginateFormBottom').submit()">
                @foreach($number_paginate as $num)
                    <option value="{{ $num }}" {{ $number == $num ? 'selected' : '' }}>
                        {{ $num == 999999999 ? 'All' : $num }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <script>
        // Toggle Filter Form
        document.getElementById('filterToggle').addEventListener('click', function() {
            const filterForm = document.getElementById('filterForm');
            const icon = document.getElementById('filterToggleIcon');
            filterForm.classList.toggle('hidden');
            icon.style.transform = filterForm.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });

        // Sinkronkan scroll atas & bawah
        const topScroll = document.getElementById('scrollbar-top');
        const bottomScroll = document.getElementById('scrollbar-bottom');
        if (topScroll && bottomScroll) {
            topScroll.addEventListener('scroll', () => {
                bottomScroll.scrollLeft = topScroll.scrollLeft;
            });
            bottomScroll.addEventListener('scroll', () => {
                topScroll.scrollLeft = bottomScroll.scrollLeft;
            });
        }

        // Sinkronkan select jumlah data per halaman atas & bawah
        const selectTop = document.getElementById('number');
        const selectBottom = document.getElementById('number-bottom');
        const formTop = document.getElementById('paginateForm');
        const formBottom = document.getElementById('paginateFormBottom');

        if (selectTop && selectBottom) {
            selectTop.addEventListener('change', function() {
                selectBottom.value = selectTop.value;
                // submit form atas
                formTop.submit();
            });
            selectBottom.addEventListener('change', function(e) {
                selectTop.value = selectBottom.value;
                // submit form bawah, delay sedikit agar value sinkron
                setTimeout(function() {
                    formBottom.submit();
                }, 10);
            });
        }

        const perPageSelect = document.querySelector('select[name="number"]');

        if (perPageSelect) {
            perPageSelect.addEventListener('change', function() {
                // Cari form filter terdekat atau buat URL manual
                const url = new URL(window.location.href);
                
                // Update nilai 'number' dan paksa 'page' kembali ke 1
                url.searchParams.set('number', this.value);
                url.searchParams.set('page', '1'); 
                
                // Redirect ke URL baru
                window.location.href = url.toString();
            });
        }
    </script>

@endsection

