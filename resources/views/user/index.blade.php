@extends('layouts.app')
@section('content')

<div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5 flex users-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold">Data Master User</h1>
            <p class="mt-1 text-sm text-gray-600 text-white font-medium">Daftar user yang terdaftar.</p>
        </div>
        <a class="inline-flex users-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium hover:bg-gray-100" href="{{ route('user.create') }}">+ Buat User</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    {{-- ===== SEARCH BAR ===== --}}
    <x-search-bar 
        placeholder="Masukkan user atau aksi..." 
        tableId="userTable" 
    />

    <!-- Filter Dropdown Button -->
    <div class="mb-6">
        <button type="button" id="filterToggle" class="flex users-center gap-2 rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
            Filter Lanjutan
            <svg class="h-4 w-4 transition-transform" id="filterToggleIcon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
        </button>
    </div>

    {{-- Form Paginate Atas --}}
    <form method="GET" action="{{ route('user.index') }}" id="paginateForm" class="paginateForm inline-block mb-4">
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
        <form method="GET" action="{{ route('user.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Nama User -->
            <div>
                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nama User</label>
                <input type="text" id="name" name="name" value="{{ request('name') }}"
                    placeholder="Cari nama user..." class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Username -->
            <div>
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">Username</label>
                <input type="text" id="username" name="username" value="{{ request('username') }}"
                    placeholder="Cari username..." class="w-full rounded-md border-gray-300 border px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <!-- Buttons -->
            <div class="flex users-end gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">Terapkan Filter</button>
                <a href="{{ route('user.index') }}" class="flex-1 border border-gray-300 bg-white px-4 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100 text-center">Reset</a>
            </div>
        </form>
    </div>

    <div class="my-4">
        {{ $users->links() }}
    </div>

    <!-- Scrollbar Atas (Sinkron) -->
    <div id="scrollbar-top" class="overflow-x-auto border-x border-t border-gray-200 bg-white" style="height: 12px;">
        <div style="width: 1270px; height: 1px;"></div>
    </div>

    <div id="scrollbar-bottom" class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-sm">
    <table id="userTable" class="uppercase min-w-full table-fixed divide-y divide-gray-200 text-sm">
        <thead>
            <tr class="bg-gray-50 text-left text-xs uppercase tracking-wider text-gray-600">
                <th class="w-16 px-4 py-3 !font-black">No</th>
                <th class="w-56 px-4 py-3 !font-black">Nama User</th>
                <th class="w-48 px-4 py-3 !font-black">Username</th>
                <th class="w-48 px-4 py-3 !font-black">Email</th>
                <th class="w-96 px-4 py-3 !font-black">Roles</th>
                <th class="w-48 px-4 py-3 !font-black">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($users as $user)
                @if ($user->name != config('app.admin_name'))
                    <tr>
                        <td class="px-4 py-3">
                            {{ $loop->iteration }}
                        </td>
                        <td class="px-4 py-3">{{ $user->name }}</td>
                        <td class="px-4 py-3">{{ $user->username }}</td>
                        <td class="px-4 py-3">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            <span class="mb-1 mr-1 inline-flex users-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                @forelse ($user->roles as $role)
                                    <span class="mb-1 mr-1 inline-flex users-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <p class="text-xs text-gray-500">Belum ada role.</p>
                                @endforelse
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap users-center gap-2">
                                <a class="inline-flex users-center rounded-md border border-gray-300 bg-white px-2.5 py-1.5 text-xs font-medium hover:bg-gray-100" href="{{ route('user.edit', $user->id) }}">Edit</a>
                                    <form action="{{route('user.destroy', $user->id)}}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex users-center rounded-md border border-red-300 bg-white px-2.5 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">Hapus</button>
                                    </form>
                            </div>
                        </td>
                    </tr>            
                @endif
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada data user.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>

    <form method="GET" action="{{ route('user.index') }}" id="paginateFormBottom" class="paginateForm inline-block mt-2 mb-2">
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

