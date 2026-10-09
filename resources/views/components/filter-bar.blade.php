@props(['suppliers' => [], 'tokos' => [], 'categories' => [], 'pts' => []])

<!-- Choices.js CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />

<style>
    /* Menyelaraskan tinggi Choices.js dengan input Tailwind biasa */
    .choices__inner {
        min-height: 38px !important;
        padding: 4px 8px !important;
        border-radius: 0.375rem !important;
        border-color: #d1d5db !important;
        background-color: #fff !important;
        font-size: 0.875rem !important;
    }
    .choices {
        margin-bottom: 0 !important;
    }
</style>

<form method="GET" action="{{ url()->current() }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-200 mb-6 space-y-4">

    <!-- Grid Filter Input (4 Kolom Rapi) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
        
        {{-- Dropdown Supplier --}}
        <div>
            <label for="filter-supplier" class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
            <select name="supplier_code" id="filter-supplier" class="w-full">
                <option value="">Semua Supplier</option>
                @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->kode_supplier }}" {{ request('supplier_code') == $supplier->kode_supplier ? 'selected' : '' }}>
                        {{ $supplier->nama_supplier }} ({{ $supplier->kode_supplier }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Input No RAF --}}
        <div>
            <label for="no_raf" class="block text-sm font-medium text-gray-700 mb-1">No RAF</label>
            <input type="text" id="no_raf" name="no_raf" placeholder="Masukkan No RAF" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2 border outline-none" value="{{ old('no_raf', request('no_raf')) }}">
        </div>
        
        {{-- Dropdown Store/Toko --}}
        <div>
            <label for="filter-toko" class="block text-sm font-medium text-gray-700 mb-1">Store</label>
            <select name="toko_id" id="filter-toko" class="w-full">
                <option value="">Semua Store</option>
                @foreach($tokos as $toko)
                    <option value="{{ $toko->id }}" {{ request('toko_id') == $toko->id ? 'selected' : '' }}>
                        {{ $toko->nama_toko }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Dropdown PT --}}
        <div>
            <label for="filter-pt" class="block text-sm font-medium text-gray-700 mb-1">PT</label>
            <select name="filter_pt" id="filter-pt" class="w-full">
                <option value="">Semua PT</option>
                @foreach($pts as $pt)
                    <option value="{{ $pt }}" {{ request('filter_pt') == $pt ? 'selected' : '' }}>
                        {{ $pt }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Dropdown Category --}}
        <div>
            <label for="filter-category" class="block text-sm font-medium text-gray-700 mb-1">Category</label>
            <select name="category_id" id="filter-category" class="w-full">
                <option value="">Semua Category</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->nama_kategori }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Dropdown Status Invoice --}}
        <div>
            <label for="invoice_status" class="block text-sm font-medium text-gray-700 mb-1">Status Invoice</label>
            <select name="invoice_status" id="invoice_status" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2 border outline-none">
                <option value="" {{ request('invoice_status') == '' ? 'selected' : '' }}>Semua Status</option>
                <option value="0" {{ request()->filled('invoice_status') && request('invoice_status') == 0 ? 'selected' : '' }}>Not Done</option>
                <option value="1" {{ request('invoice_status') == 1 ? 'selected' : '' }}>Done</option>
            </select>
        </div>

        {{-- Periode Awal --}}
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Periode Awal</label>
            <input type="date" name="start_date" id="start_date" value="{{ old('start_date', request('start_date')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2 border outline-none">
        </div>

        {{-- Periode Akhir --}}
        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Periode Akhir</label>
            <input type="date" name="end_date" id="end_date" value="{{ old('end_date', request('end_date')) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm px-3 py-2 border outline-none">
        </div>

        {{-- Status Kesiapan Divisi Buyer (Segmented Toggle Button) --}}
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Kesiapan Divisi Buyer</label>
            <div class="grid grid-cols-2 gap-2">
                <label class="flex items-center justify-center gap-2 p-2 border rounded-md cursor-pointer text-xs font-semibold select-none transition-all has-[:checked]:bg-red-50 has-[:checked]:border-red-500 has-[:checked]:text-red-700 border-gray-300 hover:bg-gray-50">
                    <input type="radio" name="status_email" value="aktif" {{ request('status_email', 'aktif') == 'aktif' ? 'checked' : '' }} class="text-red-600 focus:ring-red-500">
                    <span>Belum Siap</span>
                </label>

                <label class="flex items-center justify-center gap-2 p-2 border rounded-md cursor-pointer text-xs font-semibold select-none transition-all has-[:checked]:bg-green-50 has-[:checked]:border-green-500 has-[:checked]:text-green-700 border-gray-300 hover:bg-gray-50">
                    <input type="radio" name="status_email" value="tidak_aktif" {{ request('status_email') == 'tidak_aktif' ? 'checked' : '' }} class="text-green-600 focus:ring-green-500">
                    <span>Sudah Siap</span>
                </label>
            </div>
        </div>

        {{-- Tombol Aksi --}}
        <div class="sm:col-span-2 flex items-center justify-end gap-2 pt-2">
            <a href="{{ url()->current() }}" class="px-5 py-2 rounded-md font-medium border border-gray-300 bg-white text-gray-700 hover:bg-gray-100 transition text-sm">
                Reset
            </a>
            <button type="submit" class="px-6 py-2 rounded-md font-medium bg-blue-600 text-white hover:bg-blue-700 transition text-sm shadow-sm">
                Terapkan Filter
            </button>
        </div>

    </div>
</form>

<!-- Script Choices.js -->
<script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        ['filter-supplier', 'filter-toko', 'filter-category', 'filter-pt'].forEach(function (id) {
            const element = document.getElementById(id);
            if (element) {
                new Choices(element, {
                    searchEnabled: true,
                    searchChoices: true,
                    itemSelectText: '',
                    shouldSort: false,
                    placeholderValue: 'Pilih opsi...',
                });
            }
        });
    });
</script>