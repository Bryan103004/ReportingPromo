@props(['suppliers' => [], 'tokos' => [], 'categories' => []])

<!-- Tambahkan CSS Choices.js jika belum ada di layout utama -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<form method="GET" action="{{ url()->current() }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">

    {{-- Dropdown Supplier (Searchable) --}}
    <div>
        <label for="filter-supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
        <select name="supplier_code" id="filter-supplier" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
            <option value="">Semua Supplier</option>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->kode_supplier }}" {{ request('supplier_code') == $supplier->kode_supplier ? 'selected' : '' }}>
                    {{ $supplier->nama_supplier }} ({{ $supplier->kode_supplier }})
                </option>
            @endforeach
        </select>
    </div>

    {{-- Dropdown Store/Toko (Searchable) --}}
    <div>
        <label for="filter-toko" class="block text-sm font-medium text-gray-700 mb-1">Store</label>
        <select name="toko_id" id="filter-toko" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
            <option value="">Semua Store</option>
            @foreach($tokos as $toko)
                <option value="{{ $toko->id }}" {{ request('toko_id') == $toko->id ? 'selected' : '' }}>
                    {{ $toko->nama_toko }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Dropdown Category --}}
    <div>
        <label for="filter-category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select name="category_id" id="filter-category" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
            <option value="">Semua Category</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->nama_kategori }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Input Periode Awal --}}
    <div>
        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
        <input type="date" name="start_date" id="start_date" value="{{ old('start_date', request('start_date')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
    </div>

    {{-- Input Periode Akhir --}}
    <div>
        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
        <input type="date" name="end_date" id="end_date" value="{{ old('end_date', request('end_date')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm outline-none px-3 py-2 border">
    </div>

    {{-- Tombol Aksi --}}
    <div class="flex gap-2">
        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition text-sm">
            Terapkan Filter
        </button>
        <a href="{{ url()->current() }}" class="w-full text-center bg-gray-100 text-gray-700 px-4 py-2 rounded-lg font-semibold border border-gray-300 hover:bg-gray-200 transition text-sm">
            Reset
        </a>
    </div>
</form>

<!-- Tambahkan Script Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['filter-supplier', 'filter-toko', 'filter-category'].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) {
                new Choices(element, {
                    searchEnabled: true,
                    searchChoices: true,
                    itemSelectText: '',
                    shouldSort: false,
                });
            }
        });
    });
</script>
