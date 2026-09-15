@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="mx-auto max-w-4xl px-4 py-8">

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Formulir Tambah Dokumen</h2>
                <p class="text-sm text-gray-500 mt-1">Pilih tipe (RAF / JSM / PWP) dan lengkapi data di bawah ini.</p>
            </div>
            <div class="flex items-end gap-3">
                <div>
                    <button type="button" onclick="document.getElementById('excelFileInput').click()" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                        Import dari Excel
                    </button>
                    <input type="file" id="excelFileInput" accept=".xlsx,.xls" class="hidden">
                </div>
                <div class="w-56">
                    <label class="block text-sm font-medium text-gray-700">Tipe Dokumen</label>
                    <select id="category" name="category" class="mt-1 block w-full rounded-md border-gray-300" required>
                        <option value="RAF">Rafaksi</option>
                        <option value="JSM">JSM</option>
                        <option value="PWP">PWP</option>
                    </select>
                </div>
            </div>
        </div>

        <form id="unifiedForm" action="{{ route('rafaksi.store') }}" method="POST" class="p-6">
            @csrf
            
            {{-- Grid 2 Kolom --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                {{-- Kode Supplier --}}
                <div class="space-y-2">
                    <label for="choices-supplier" class="block text-sm font-medium text-gray-700 tracking-wide">
                        Kode Supplier <span class="text-rose-500 font-bold" title="Wajib diisi">*</span>
                    </label>
                    <div class="flex items-center gap-3 ">
                        <div class="relative flex-1">
                            <select name="supplier_code" id="choices-supplier" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm" 
                                required>
                                <option value="" disabled selected class="text-gray-400">Pilih atau cari supplier...</option>
                                @foreach ($supplierRafaksi as $supplier)
                                    <option value="{{ $supplier->kode_supplier }}" class="text-gray-900">
                                        {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" onclick="openModal()" 
                            class="inline-flex h-[42px] shrink-0 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm" 
                            title="Tambah Supplier Baru">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://w3.org">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Baru</span>
                        </button>
                    </div>
                </div>

                {{-- Nama Supplier --}}
                <div>
                    <label for="supplier_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="supplier_name" id="supplier_name" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" readonly required>
                </div>    
                
                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Category <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="category_id" id="category_id" class="w-full rounded-md border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-900 shadow-sm" required>
                            <option value="" disabled selected class="text-gray-400">-- Pilih Category --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" class="text-gray-900">
                                    {{ $category->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>     
                
                {{-- Periode Awal --}}
                <div>
                    <label for="periode_awal" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Awal<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_awal" id="periode_awal" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" required>
                </div>

                {{-- Periode Akhir --}}
                <div>
                    <label for="periode_akhir" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Akhir<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_akhir" id="periode_akhir" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" required>
                </div> 

                {{-- No. RAF --}}
                <div>
                    <label for="no_raf" class="block text-sm font-semibold text-gray-700 mb-1.5">No Dokumen<span class="text-red-500">*</span></label>
                    <input type="text" name="no_raf" id="no_raf" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" readonly placeholder="Otomatis terisi" required>
                </div>

                {{-- No. Shiji --}}
                <div>
                    <label for="no_shiji" class="block text-sm font-semibold text-gray-700 mb-1.5">No. Shiji</label>
                    <input type="text" name="no_shiji" id="no_shiji" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" placeholder="Nomor dokumen Shiji (opsional)">
                </div>

                {{-- No. Shiji Promotion --}}
                <div>
                    <label for="no_shiji_promotion" class="block text-sm font-semibold text-gray-700 mb-1.5">No. Shiji Promotion</label>
                    <input type="text" name="no_shiji_promotion" id="no_shiji_promotion" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" placeholder="Nomor dokumen Shiji Promotion (opsional)">
                </div>

                {{-- Periode Bulan --}}
                <div>
                    <label for="periode_bulan" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Rekap<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_bulan" id="periode_bulan" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" required>
                </div>

                <div class="md:col-span-2 text-lg font-semibold text-gray-700 border-b pb-2 mt-4">Pemilihan Toko (Store)</div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Berdasarkan Region (bisa pilih lebih dari satu)</label>
                    <div class="flex flex-col md:flex-row md:items-center gap-4">
                        <select id="region_filter" multiple class="tom-select block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm">
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->nama_region }}</option>
                        @endforeach
                    </select>
                        <select id="pt_filter_mode" class="block w-full md:w-auto rounded-md border-gray-300 shadow-sm text-sm">
                            <option value="all">Semua Toko</option>
                            <option value="include|PT. MITRA BELANJA ANDA">Hanya PT. MITRA BELANJA ANDA</option>
                            <option value="exclude|PT. MITRA BELANJA ANDA">Selain PT. MITRA BELANJA ANDA</option>
                            <optgroup label="Detail per PT (selain MBA)">
                                @foreach($ptOptions as $pt)
                                    @if($pt !== 'PT. MITRA BELANJA ANDA')
                                        <option value="include|{{ $pt }}">{{ $pt }}</option>
                                    @endif
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Toko <span class="text-red-500">*</span></label>
                    
                    <div id="toko_container" class="grid grid-cols-2 md:grid-cols-4 gap-4 p-4 border rounded-md bg-gray-50 min-h-[100px]">
                        <div class="col-span-full text-center text-gray-400 text-sm py-4">
                            Silakan pilih region terlebih dahulu.
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="store" id="hidden_store_name" value="-">
                <input type="hidden" name="raf_sequence" id="raf_sequence" value="">
            </div>

            {{-- Baris Item (Detail per Artikel) --}}
            <div class="mx-6 mb-8">
                <div class="flex items-center justify-between border-b pb-2 mb-3">
                    <span class="text-lg font-semibold text-gray-700">Baris Item (Opsional)</span>
                    <button type="button" onclick="addItemRow()" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Baris
                    </button>
                </div>
                <p class="text-xs text-gray-500 mb-3">Kosongkan kalau dokumen ini cuma butuh 1 nominal manual (tanpa detail per artikel). Kolom Sales per toko mengikuti toko yang dicentang di atas.</p>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="w-full text-xs" id="itemsTable">
                        <thead id="itemsTableHead" class="bg-gray-100"></thead>
                        <tbody id="itemsTableBody"></tbody>
                    </table>
                    <div id="itemsTableEmpty" class="text-center text-gray-400 text-sm py-4 bg-gray-50">Belum ada baris item.</div>
                </div>
            </div>

            {{-- Nominal (Full Width di bawah) --}}
            <div class="mb-8 mx-6">
                <label for="nominal" class="block text-sm font-semibold text-gray-700 mb-1.5">Nominal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="text-gray-500 text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" name="nominal" id="nominal" class="w-full rounded-md border border-gray-300 pl-10 pr-4 py-2.5 text-sm" placeholder="0" required>
                </div>
                <p id="nominalComputedHint" class="text-xs text-gray-500 mt-1 hidden">Otomatis dari total Value semua baris item.</p>
            </div>

            <!-- Remark -->
            <div class="mb-8 mx-6">
                <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-1.5">Remarks</label>  
                <textarea name="remarks" id="remarks"  rows="3" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" placeholder="Masukkan catatan di sini..."></textarea>
            </div> 

            {{-- Action Buttons --}}
            <div class="m-4 flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ url()->previous() }}" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg">Batal</a>
                
                <button type="submit" id="btnSubmitUnifiedForm" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg disabled:opacity-60 disabled:cursor-not-allowed">
                    Simpan Data
                </button>
            </div>
        </form>

        {{-- ================= MODAL PREVIEW IMPORT EXCEL ================= --}}
        <div id="importPreviewModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 shrink-0">
                    <h3 class="text-lg font-bold text-gray-800">Preview Import Excel</h3>
                    <button type="button" onclick="closeImportPreview()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto">
                    <div id="importPreviewLoading" class="text-center text-gray-500 py-8">Membaca file...</div>
                    <div id="importPreviewContent" class="hidden">
                        <div id="importPreviewErrors" class="hidden bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-md text-sm text-red-700"></div>
                        <div id="importPreviewWarnings" class="hidden bg-amber-50 border-l-4 border-amber-500 p-4 mb-4 rounded-md text-sm text-amber-800"></div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-4 text-sm">
                            <div><span class="text-gray-500">Periode Awal:</span> <span id="pvPeriodeAwal" class="font-semibold"></span></div>
                            <div><span class="text-gray-500">Periode Akhir:</span> <span id="pvPeriodeAkhir" class="font-semibold"></span></div>
                            <div><span class="text-gray-500">No. Shiji:</span> <span id="pvNoShiji" class="font-semibold"></span></div>
                            <div><span class="text-gray-500">No. Shiji Promotion:</span> <span id="pvNoShijiPromotion" class="font-semibold"></span></div>
                            <div><span class="text-gray-500">Vendor:</span> <span id="pvVendor" class="font-semibold"></span></div>
                            <div><span class="text-gray-500">No. RAF di file:</span> <span id="pvNoRaf" class="font-semibold text-gray-400"></span></div>
                            <div><span class="text-gray-500">Toko:</span> <span id="pvStores" class="font-semibold"></span></div>
                        </div>

                        <div class="overflow-x-auto border rounded-lg">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-100">
                                    <tr>
                                        <th class="px-2 py-2 text-left">ARTICLE</th>
                                        <th class="px-2 py-2 text-left">DESCRIPTION</th>
                                        <th class="px-2 py-2 text-right">REG</th>
                                        <th class="px-2 py-2 text-right">DISC NOMINAL</th>
                                        <th class="px-2 py-2 text-right">PROMO DISC</th>
                                        <th class="px-2 py-2 text-right bg-amber-50">CLAIM</th>
                                        <th class="px-2 py-2 text-right">Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="importPreviewItemsBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 shrink-0">
                    <button type="button" onclick="closeImportPreview()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg">Batal</button>
                    <button type="button" id="btnApproveImport" onclick="approveImportPreview()" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg disabled:opacity-60 disabled:cursor-not-allowed">Gunakan Data Ini</button>
                </div>
            </div>
        </div>

        {{-- ================= MODAL TAMBAH SUPPLIER ================= --}}
        <div id="supplierModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 backdrop-blur-sm">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden transform">
                <div class="p-3">
                    <div class="px-6 py-4 w-full border-b border-gray-200 flex justify-between items-center bg-gray-50">
                            <h3 class="text-lg font-bold text-gray-800">Tambah Supplier Baru</h3>
                            <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        
                        <form id="addSupplierForm" class="p-6">
                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Supplier <span class="text-red-500">*</span></label>
                                <input type="text" id="new_kode_supplier" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" required placeholder="Contoh: SUP-001">
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Supplier <span class="text-red-500">*</span></label>
                                <input type="text" id="new_nama_supplier" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" required placeholder="Contoh: PT. Sumber Makmur">
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeModal()" id="btnCancelSupplier" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg">Batal</button>
                                <button type="button" onclick="saveNewSupplier()" id="btnSaveSupplier" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg">Simpan</button>
                            </div>
                        </form>
                </div>
            </div>
        </div>      
    </div>
</div>

<script>
    let supplierList = @json($supplierRafaksi);

    document.getElementById('choices-supplier').addEventListener('change', function() {
        var selectedSupplierCode = this.value;
        var supplierNameInput = document.getElementById('supplier_name');

        var selectedSupplier = supplierList.find(supplier => supplier.kode_supplier === selectedSupplierCode);

        if (selectedSupplier) {
            supplierNameInput.value = selectedSupplier.nama_supplier;
        } else {
            supplierNameInput.value = '';
        }
    })

    function openModal() {
        document.getElementById('supplierModal').classList.remove('hidden');
    }
    
    function closeModal() {
        document.getElementById('supplierModal').classList.add('hidden');
        let formModal = document.getElementById('addSupplierForm');
        if (formModal) { formModal.reset(); }
        document.getElementById('new_kode_supplier').value = '';
        document.getElementById('new_nama_supplier').value = '';
    }

    function saveNewSupplier(){
        var kodeSupplier = document.getElementById('new_kode_supplier').value.trim();
        var namaSupplier = document.getElementById('new_nama_supplier').value.trim();
        var btn = document.getElementById('btnSaveSupplier');

        if(kodeSupplier === '' || namaSupplier === ''){
            alert('Kode Supplier dan Nama Supplier harus di-isi.');
            return;
        }

        else if(supplierList.some(supplier => supplier.kode_supplier === kodeSupplier)){
            alert('Kode Supplier sudah ada. Silakan gunakan kode lain.');
            return;
        }

        btn.innerHTML = 'Menyimpan...';
        btn.disabled= true;

        fetch('{{ route("supplier_rafaksi.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                kode_supplier: kodeSupplier,
                nama_supplier: namaSupplier,
            })
        })
        .then(async response => {
            if (!response.ok) {
                const err = await response.json();
                throw new Error(err.message || 'Kesalahan dari sisi server.');
            }
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                throw new Error('Server merespon, tetapi bukan dengan format JSON.');
            }
        })
        .then(data => {
            supplierList.push({ kode_supplier: kodeSupplier, nama_supplier: namaSupplier });
            const select = document.getElementById('choices-supplier');
            const option = document.createElement('option');
            option.value = kodeSupplier; option.text = kodeSupplier + ' - ' + namaSupplier; option.selected = true;
            select.appendChild(option);
            document.getElementById('supplier_name').value = namaSupplier;
            closeModal();
        })
        .catch(err => {
            alert('Gagal menyimpan supplier: ' + err.message);
        })
        .finally(()=>{ btn.innerHTML = 'Simpan'; btn.disabled = false; });
    }

    // Remember which tokos the user has checked so switching/adding regions doesn't lose selections.
    window.checkedTokoIds = new Set();

    function rememberCheckedTokos(){
        document.querySelectorAll('#toko_container input[type=checkbox]').forEach(cb => {
            if (cb.checked) window.checkedTokoIds.add(cb.value);
            else window.checkedTokoIds.delete(cb.value);
        });
    }

    async function fetchTokos(skipRemember){
        const container = document.getElementById('toko_container');
        // skipRemember: dipakai pas approve import Excel, karena checkedTokoIds sudah
        // di-set eksplisit dari hasil parse -- kalau tetap "diingat" dari DOM lama,
        // toko yang belum sempat dirender bakal ke-hapus lagi dari Set-nya.
        if (!skipRemember) {
            rememberCheckedTokos();
        }

        const regionSelect = document.getElementById('region_filter');
        const regionIds = Array.from(regionSelect.selectedOptions).map(o => o.value);
        const regionNames = Array.from(regionSelect.selectedOptions).map(o => o.text);
        document.getElementById('hidden_store_name').value = regionNames.length ? regionNames.join(', ') : '-';

        if (!regionIds.length) {
            container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Silakan pilih region terlebih dahulu.</div>';
            return;
        }

        container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Memuat...</div>';

        const ptValue = document.getElementById('pt_filter_mode').value;
        let ptParam = '';
        if (ptValue !== 'all') {
            const [ptMode, ptName] = ptValue.split('|');
            ptParam = '&name_pt=' + encodeURIComponent(ptName) + '&name_pt_mode=' + ptMode;
        }

        try {
            const getTokosBaseUrl = '{{ url('/get-tokos') }}';
            const results = await Promise.all(regionIds.map(id =>
                fetch(getTokosBaseUrl + '/' + id + '?_=1' + ptParam).then(r => r.json())
            ));

            const seen = new Map();
            results.flat().forEach(t => { seen.set(t.id, t); });
            const data = Array.from(seen.values());

            if (!data.length) {
                container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Tidak ada toko untuk region/filter ini.</div>';
                return;
            }

            container.innerHTML = '';
            data.forEach(t => {
                const id = 'toko_cb_' + t.id;
                const checked = window.checkedTokoIds.has(String(t.id)) ? 'checked' : '';
                const html = `<label class="flex items-center gap-2 p-2 bg-white rounded shadow-sm border"><input type="checkbox" name="toko_id[]" value="${t.id}" id="${id}" ${checked} /> <span class="text-sm">${t.nama_toko}</span></label>`;
                container.insertAdjacentHTML('beforeend', html);
            });
        } catch (e) {
            container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Gagal memuat toko.</div>';
            console.error(e);
        }

        renderItemsTable();
    }

    // ===================== BARIS ITEM (DETAIL PER ARTIKEL) =====================
    window.itemRows = [];
    window.itemStores = [];

    function getCheckedStores(){
        return Array.from(document.querySelectorAll('#toko_container input[type=checkbox]:checked'))
            .map(cb => ({ id: cb.value, name: cb.nextElementSibling ? cb.nextElementSibling.textContent.trim() : cb.value }));
    }

    function fmtNum(n){
        n = Number(n) || 0;
        return n.toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function escAttr(v){
        return String(v ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
    }

    function computeClaim(discNominal, promoDisc, reg){
        discNominal = parseFloat(discNominal) || 0;
        promoDisc = parseFloat(promoDisc) || 0;
        reg = parseFloat(reg) || 0;
        if (discNominal > 0) return Math.round(discNominal * 100) / 100;
        return Math.round((promoDisc / 100) * reg * 100) / 100;
    }

    function addItemRow(){
        window.itemRows.push({
            article: '', shiji_code: '', description: '',
            disc_nominal: '', promo_disc: '', reg: '', promo: '',
            sales: {},
        });
        renderItemsTable();
    }

    function removeItemRow(index){
        window.itemRows.splice(index, 1);
        renderItemsTable();
    }

    function renderItemsTable(){
        const stores = getCheckedStores();
        window.itemStores = stores;

        const thead = document.getElementById('itemsTableHead');
        const tbody = document.getElementById('itemsTableBody');
        const empty = document.getElementById('itemsTableEmpty');
        const table = document.getElementById('itemsTable');

        if (!window.itemRows.length) {
            table.classList.add('hidden');
            empty.classList.remove('hidden');
        } else {
            table.classList.remove('hidden');
            empty.classList.add('hidden');
        }

        let headHtml = '<tr>';
        headHtml += '<th class="px-2 py-2 text-left">NO</th>';
        headHtml += '<th class="px-2 py-2 text-left">ARTICLE</th>';
        headHtml += '<th class="px-2 py-2 text-left">SHIJI CODE</th>';
        headHtml += '<th class="px-2 py-2 text-left">DESCRIPTION</th>';
        headHtml += '<th class="px-2 py-2 text-right">DISC NOMINAL</th>';
        headHtml += '<th class="px-2 py-2 text-right">PROMO DISC (%)</th>';
        headHtml += '<th class="px-2 py-2 text-right">REG</th>';
        headHtml += '<th class="px-2 py-2 text-right">PROMO</th>';
        headHtml += '<th class="px-2 py-2 text-right bg-amber-50">CLAIM</th>';
        stores.forEach(s => { headHtml += `<th class="px-2 py-2 text-right whitespace-nowrap">Sales ${s.name}</th>`; });
        headHtml += '<th class="px-2 py-2 text-right bg-amber-50">Sales TOTAL</th>';
        stores.forEach(s => { headHtml += `<th class="px-2 py-2 text-right whitespace-nowrap">Value ${s.name}</th>`; });
        headHtml += '<th class="px-2 py-2 text-right bg-amber-50">Value TOTAL</th>';
        headHtml += '<th class="px-2 py-2"></th>';
        headHtml += '</tr>';
        thead.innerHTML = headHtml;

        let bodyHtml = '';
        window.itemRows.forEach((row, i) => {
            bodyHtml += `<tr class="border-t">`;
            bodyHtml += `<td class="px-2 py-1 text-gray-500">${i + 1}</td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="text" name="items[${i}][article]" class="w-28 rounded border border-gray-300 text-xs px-1.5 py-1" data-field="article" data-row="${i}" value="${escAttr(row.article)}" required></td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="text" name="items[${i}][shiji_code]" class="w-24 rounded border border-gray-300 text-xs px-1.5 py-1" data-field="shiji_code" data-row="${i}" value="${escAttr(row.shiji_code)}"></td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="text" name="items[${i}][description]" class="w-32 rounded border border-gray-300 text-xs px-1.5 py-1" data-field="description" data-row="${i}" value="${escAttr(row.description)}"></td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="number" step="0.01" name="items[${i}][disc_nominal]" class="w-20 rounded border border-gray-300 text-xs px-1.5 py-1 text-right" data-field="disc_nominal" data-row="${i}" value="${escAttr(row.disc_nominal)}"></td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="number" step="0.01" name="items[${i}][promo_disc]" class="w-16 rounded border border-gray-300 text-xs px-1.5 py-1 text-right" data-field="promo_disc" data-row="${i}" value="${escAttr(row.promo_disc)}"></td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="number" step="0.01" name="items[${i}][reg]" class="w-20 rounded border border-gray-300 text-xs px-1.5 py-1 text-right" data-field="reg" data-row="${i}" value="${escAttr(row.reg)}" required></td>`;
            bodyHtml += `<td class="px-1 py-1"><input type="number" step="0.01" name="items[${i}][promo]" class="w-20 rounded border border-gray-300 text-xs px-1.5 py-1 text-right" data-field="promo" data-row="${i}" value="${escAttr(row.promo)}"></td>`;
            bodyHtml += `<td class="px-2 py-1 text-right font-semibold bg-amber-50" data-claim-cell="${i}">0</td>`;
            stores.forEach(s => {
                const val = row.sales[s.id] ?? '';
                bodyHtml += `<td class="px-1 py-1"><input type="number" step="0.01" name="items[${i}][sales][${s.id}]" class="w-16 rounded border border-gray-300 text-xs px-1.5 py-1 text-right" data-field="sales" data-store="${s.id}" data-row="${i}" value="${escAttr(val)}"></td>`;
            });
            bodyHtml += `<td class="px-2 py-1 text-right font-semibold bg-amber-50" data-salestotal-cell="${i}">0</td>`;
            stores.forEach(s => {
                bodyHtml += `<td class="px-2 py-1 text-right" data-value-cell="${i}-${s.id}">0</td>`;
            });
            bodyHtml += `<td class="px-2 py-1 text-right font-semibold bg-amber-50" data-valuetotal-cell="${i}">0</td>`;
            bodyHtml += `<td class="px-1 py-1 text-center"><button type="button" onclick="removeItemRow(${i})" class="text-red-500 hover:text-red-700 font-bold" title="Hapus baris">&times;</button></td>`;
            bodyHtml += '</tr>';
        });
        tbody.innerHTML = bodyHtml;

        tbody.querySelectorAll('input[data-field]').forEach(input => {
            input.addEventListener('input', onItemFieldInput);
        });

        recomputeAllRows();
    }

    function onItemFieldInput(e){
        const row = parseInt(e.target.dataset.row, 10);
        const field = e.target.dataset.field;
        if (field === 'sales') {
            window.itemRows[row].sales[e.target.dataset.store] = e.target.value;
        } else {
            window.itemRows[row][field] = e.target.value;
        }
        recomputeRow(row);
        recomputeGrandNominal();
    }

    function recomputeRow(i){
        const row = window.itemRows[i];
        if (!row) return;
        const claim = computeClaim(row.disc_nominal, row.promo_disc, row.reg);
        const claimCell = document.querySelector(`[data-claim-cell="${i}"]`);
        if (claimCell) claimCell.textContent = fmtNum(claim);

        let salesTotal = 0, valueTotal = 0;
        window.itemStores.forEach(s => {
            const qty = parseFloat(row.sales[s.id]) || 0;
            const value = Math.round(claim * qty * 100) / 100;
            salesTotal += qty;
            valueTotal += value;
            const cell = document.querySelector(`[data-value-cell="${i}-${s.id}"]`);
            if (cell) cell.textContent = fmtNum(value);
        });
        const salesTotalCell = document.querySelector(`[data-salestotal-cell="${i}"]`);
        if (salesTotalCell) salesTotalCell.textContent = fmtNum(salesTotal);
        const valueTotalCell = document.querySelector(`[data-valuetotal-cell="${i}"]`);
        if (valueTotalCell) valueTotalCell.textContent = fmtNum(valueTotal);
    }

    function recomputeAllRows(){
        window.itemRows.forEach((_, i) => recomputeRow(i));
        recomputeGrandNominal();
    }

    function recomputeGrandNominal(){
        const nominalInput = document.getElementById('nominal');
        const hint = document.getElementById('nominalComputedHint');
        if (!window.itemRows.length) {
            nominalInput.readOnly = false;
            hint.classList.add('hidden');
            return;
        }
        let total = 0;
        window.itemRows.forEach(row => {
            const claim = computeClaim(row.disc_nominal, row.promo_disc, row.reg);
            window.itemStores.forEach(s => {
                const qty = parseFloat(row.sales[s.id]) || 0;
                total += Math.round(claim * qty * 100) / 100;
            });
        });
        nominalInput.value = total;
        nominalInput.readOnly = true;
        hint.classList.remove('hidden');
    }

    // Toggle toko checkbox (bukan cuma ganti region) juga harus update kolom per-toko di tabel item
    document.getElementById('toko_container').addEventListener('change', function(e){
        if (e.target.matches('input[type=checkbox]')) {
            renderItemsTable();
        }
    });

    // Auto-generate no_raf when category or periode_bulan changes
    const categoryEl = document.getElementById('category');
    const categoryId = document.getElementById('category_id');
    const periodeBulanEl = document.getElementById('periode_bulan');
    const noRafEl = document.getElementById('no_raf');
    const rafSeqEl = document.getElementById('raf_sequence');

    async function refreshNoRaf(){
        const cat = categoryEl.value || 'RAF';
        const cat_id = categoryId.value;
        const periode = periodeBulanEl.value;
        const url = new URL('{{ url('/next-no-raf') }}', window.location.origin);
        url.searchParams.set('category', cat);
        url.searchParams.set('category_id', cat_id);
        if (periode) url.searchParams.set('periode', periode);
        try{
            const res = await fetch(url.toString());
            const j = await res.json();
            noRafEl.value = j.no_raf;
            rafSeqEl.value = j.sequence;
        }catch(e){ console.error(e); }
    }

    categoryId.addEventListener('change', function(){
        refreshNoRaf();
    });

    // Sinkronkan form.action dengan tipe dokumen yang lagi dipilih. Dipisah jadi
    // fungsi sendiri (bukan cuma di dalam listener 'change' categoryEl) karena
    // browser (terutama Chrome) suka otomatis me-restore pilihan <select> pas
    // halaman di-reload TANPA nge-fire event 'change' -- kalau cuma andalin
    // 'change', form.action bisa nyangkut di default HTML-nya (rafaksi) padahal
    // dropdown & no_raf yang ditampilkan udah keliatan benar (PWP/JSM), dan
    // dokumen kesave ke tabel yang salah.
    function syncFormAction(){
        const form = document.getElementById('unifiedForm');
        if (categoryEl.value === 'JSM') form.action = '{{ url('/jsm') }}';
        else if (categoryEl.value === 'PWP') form.action = '{{ url('/pwp') }}';
        else form.action = '{{ url('/rafaksi') }}';
    }

    categoryEl.addEventListener('change', function(){
        syncFormAction();
        refreshNoRaf();
    });

    periodeBulanEl.addEventListener('change', refreshNoRaf);

    document.addEventListener('DOMContentLoaded', function(){
        window.regionTomSelect = new TomSelect('#region_filter', {
            plugins: ['remove_button'],
            create: false,
            maxItems: null,
            placeholder: '-- Pilih Region (bisa lebih dari satu) --',
            onChange: fetchTokos,
        });
        renderItemsTable();
    });

    document.getElementById('pt_filter_mode').addEventListener('change', fetchTokos);

    window.addEventListener('load', function(){
        syncFormAction();
        refreshNoRaf();
    });

    // Cegah submit dobel kalau tombol "Simpan Data" di-klik/tekan-Enter lebih dari sekali
    // sebelum halaman sempat pindah (misal koneksi lambat) — tanpa ini bisa kebuat 2 dokumen.
    document.getElementById('unifiedForm').addEventListener('submit', function(e){
        const btn = document.getElementById('btnSubmitUnifiedForm');
        if (btn.disabled) {
            e.preventDefault();
            return;
        }
        // Safety net terakhir -- pastikan form.action beneran sesuai tipe dokumen
        // yang lagi kepilih SAAT SUBMIT, bukan cuma waktu terakhir kali event
        // 'change' sempat nyala.
        syncFormAction();
        btn.disabled = true;
        btn.textContent = 'Menyimpan...';
    });

    // ===================== IMPORT DARI EXCEL =====================
    window.importPreviewResult = null;

    document.getElementById('excelFileInput').addEventListener('change', async function(e){
        const file = e.target.files[0];
        if (!file) return;

        document.getElementById('importPreviewModal').classList.remove('hidden');
        document.getElementById('importPreviewLoading').classList.remove('hidden');
        document.getElementById('importPreviewContent').classList.add('hidden');
        document.getElementById('btnApproveImport').disabled = true;

        const formData = new FormData();
        formData.append('excel_file', file);

        try {
            const res = await fetch('{{ route('create.document.import') }}', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: formData,
            });
            const data = await res.json();
            renderImportPreview(data, res.ok);
        } catch (err) {
            console.error(err);
            renderImportPreview({ errors: ['Gagal membaca file: ' + err.message], warnings: [], items: [], stores: [], header: {} }, false);
        } finally {
            e.target.value = '';
        }
    });

    function renderImportPreview(data, ok){
        window.importPreviewResult = ok ? data : null;

        document.getElementById('importPreviewLoading').classList.add('hidden');
        document.getElementById('importPreviewContent').classList.remove('hidden');
        document.getElementById('btnApproveImport').disabled = !ok;

        const errBox = document.getElementById('importPreviewErrors');
        if (data.errors && data.errors.length) {
            errBox.innerHTML = '<strong>File tidak bisa diimport:</strong><ul class="list-disc list-inside mt-1">' + data.errors.map(m => `<li>${m}</li>`).join('') + '</ul>';
            errBox.classList.remove('hidden');
        } else {
            errBox.classList.add('hidden');
        }

        const warnBox = document.getElementById('importPreviewWarnings');
        if (data.warnings && data.warnings.length) {
            warnBox.innerHTML = '<strong>Perhatian:</strong><ul class="list-disc list-inside mt-1">' + data.warnings.map(m => `<li>${m}</li>`).join('') + '</ul>';
            warnBox.classList.remove('hidden');
        } else {
            warnBox.classList.add('hidden');
        }

        const h = data.header || {};
        document.getElementById('pvPeriodeAwal').textContent = h.periode_awal || '(gagal dibaca, isi manual)';
        document.getElementById('pvPeriodeAkhir').textContent = h.periode_akhir || '(gagal dibaca, isi manual)';
        document.getElementById('pvNoShiji').textContent = h.no_shiji || '-';
        document.getElementById('pvNoShijiPromotion').textContent = h.no_shiji_promotion || '-';
        document.getElementById('pvNoRaf').textContent = (h.no_raf || '-') + ' (nomor baru akan digenerate otomatis)';
        document.getElementById('pvVendor').textContent = h.supplier_match ? h.supplier_match.nama_supplier : (h.supplier_name_hint ? h.supplier_name_hint + ' (belum ketemu di database)' : '-');
        document.getElementById('pvStores').textContent = (data.stores || []).map(s => s.nama_toko).join(', ') || '-';

        const tbody = document.getElementById('importPreviewItemsBody');
        tbody.innerHTML = (data.items || []).map(item => {
            const totalSales = Object.values(item.sales || {}).reduce((a, b) => a + (parseFloat(b) || 0), 0);
            return `<tr class="border-t">
                <td class="px-2 py-1">${escAttr(item.article)}</td>
                <td class="px-2 py-1">${escAttr(item.description)}</td>
                <td class="px-2 py-1 text-right">${fmtNum(item.reg)}</td>
                <td class="px-2 py-1 text-right">${fmtNum(item.disc_nominal)}</td>
                <td class="px-2 py-1 text-right">${item.promo_disc ? fmtNum(item.promo_disc) + '%' : '-'}</td>
                <td class="px-2 py-1 text-right font-semibold bg-amber-50">${fmtNum(item.claim)}</td>
                <td class="px-2 py-1 text-right">${fmtNum(totalSales)}</td>
            </tr>`;
        }).join('');
    }

    function closeImportPreview(){
        document.getElementById('importPreviewModal').classList.add('hidden');
        window.importPreviewResult = null;
    }

    async function approveImportPreview(){
        const data = window.importPreviewResult;
        if (!data) return;

        const h = data.header || {};
        if (h.periode_awal) document.getElementById('periode_awal').value = h.periode_awal;
        if (h.periode_akhir) document.getElementById('periode_akhir').value = h.periode_akhir;
        if (h.no_shiji) document.getElementById('no_shiji').value = h.no_shiji;
        if (h.no_shiji_promotion) document.getElementById('no_shiji_promotion').value = h.no_shiji_promotion;

        if (h.supplier_match) {
            const supplierSelect = document.getElementById('choices-supplier');
            supplierSelect.value = h.supplier_match.kode_supplier;
            supplierSelect.dispatchEvent(new Event('change'));
        } else if (h.supplier_name_hint) {
            document.getElementById('new_nama_supplier').value = h.supplier_name_hint;
            openModal();
        }

        window.itemRows = (data.items || []).map(function (i) {
            return {
                article: i.article || '',
                shiji_code: i.shiji_code || '',
                description: i.description || '',
                disc_nominal: i.disc_nominal ?? '',
                promo_disc: i.promo_disc ?? '',
                reg: i.reg ?? '',
                promo: i.promo ?? '',
                sales: i.sales || {},
            };
        });

        window.checkedTokoIds = new Set((data.stores || []).map(s => String(s.toko_id)));

        const regionIds = [...new Set((data.stores || []).map(s => String(s.region_id)))];
        if (window.regionTomSelect) {
            window.regionTomSelect.setValue(regionIds, true);
        }

        await fetchTokos(true);
        renderItemsTable();

        closeImportPreview();
    }
</script>

@endsection
