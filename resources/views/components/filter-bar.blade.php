@props(['suppliers' => []])

<form method="GET" action="{{ url()->current() }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 flex flex-col md:flex-row gap-4 items-end">
    
    {{-- Dropdown Supplier --}}
    <div class="w-full md:w-1/4">
        <label for="supplier_code" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
        <select name="supplier_code" id="supplier_code" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
            <option value="">Semua Supplier</option>
            @foreach($suppliers as $supplier)
                {{-- request('supplier_code') berguna untuk menjaga pilihan terakhir user agar tidak reset saat direfresh --}}
                <option value="{{ $supplier->kode_supplier }}" {{ request('supplier_code') == $supplier->kode_supplier ? 'selected' : '' }}>
                    {{ $supplier->nama_supplier }} ({{ $supplier->kode_supplier }})
                </option>
            @endforeach
        </select>
    </div>
    
    {{-- Input Periode Awal --}}
    <div class="w-full md:w-1/4">
        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
    </div>

    {{-- Input Periode Akhir --}}
    <div class="w-full md:w-1/4">
        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
    </div>

    {{-- Tombol Aksi --}}
    <div class="w-full md:w-1/4 flex gap-2">
        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition text-sm">
            Terapkan Filter
        </button>
        {{-- Tombol reset akan mengarahkan ulang ke URL saat ini tanpa parameter GET --}}
        <a href="{{ url()->current() }}" class="w-full text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-semibold border border-gray-300 hover:bg-gray-200 transition text-sm">
            Reset
        </a>
    </div>
</form>