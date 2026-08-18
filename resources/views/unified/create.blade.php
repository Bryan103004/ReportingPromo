@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-800">Formulir Tambah Dokumen</h2>
                <p class="text-sm text-gray-500 mt-1">Pilih tipe (RAF / JSM / PWP) dan lengkapi data di bawah ini.</p>
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
                    <label for="no_raf" class="block text-sm font-semibold text-gray-700 mb-1.5">No. RAF <span class="text-red-500">*</span></label>
                    <input type="text" name="no_raf" id="no_raf" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" readonly placeholder="Otomatis terisi" required>
                </div>

                {{-- Periode Bulan --}}
                <div>
                    <label for="periode_bulan" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Rekap<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_bulan" id="periode_bulan" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" required>
                </div>

                <div class="md:col-span-2 text-lg font-semibold text-gray-700 border-b pb-2 mt-4">Pemilihan Toko (Store)</div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Berdasarkan Region</label>
                    <div class="flex items-center gap-4">
                        <select id="region_filter" onchange="fetchTokos(this.value)" class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm">
                        <option value="">-- Pilih Region untuk memunculkan Toko --</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->nama_region }}</option>
                        @endforeach
                    </select>
                        <label class="flex items-center text-sm text-slate-500">
                            <input type="checkbox" id="local_pt_filter" class="h-4 w-4 mr-2" /> Hanya PT. MITRA BELANJA ANDA
                        </label>
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

            {{-- Nominal (Full Width di bawah) --}}
            <div class="mb-8 mx-6">
                <label for="nominal" class="block text-sm font-semibold text-gray-700 mb-1.5">Nominal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="text-gray-500 text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" name="nominal" id="nominal" class="w-full rounded-md border border-gray-300 pl-10 pr-4 py-2.5 text-sm" placeholder="0" required>
                </div>
            </div> 

            <!-- Remark -->
            <div class="mb-8 mx-6">
                <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-1.5">Remarks</label>  
                <textarea name="remarks" id="remarks"  rows="3" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm" placeholder="Masukkan catatan di sini..."></textarea>
            </div> 

            {{-- Action Buttons --}}
            <div class="m-4 flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ url()->previous() }}" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg">Batal</a>
                
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg">
                    Simpan Data
                </button>
            </div>
        </form> 


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

    function fetchTokos(regionId){
        const container = document.getElementById('toko_container');
        container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Memuat...</div>';
        let url = '/get-tokos/' + regionId;
        const onlyPt = document.getElementById('local_pt_filter').checked;
        if (onlyPt) url += '?name_pt=' + encodeURIComponent('PT. MITRA BELANJA ANDA');
        fetch(url).then(r => r.json()).then(data => {
            if(!data.length){ container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Tidak ada toko di region ini.</div>'; return; }
            container.innerHTML = '';
            data.forEach(t => {
                const id = 'toko_cb_' + t.id;
                const html = `<label class="flex items-center gap-2 p-2 bg-white rounded shadow-sm border"><input type="checkbox" name="toko_id[]" value="${t.id}" id="${id}" /> <span class="text-sm">${t.nama_toko}</span></label>`;
                container.insertAdjacentHTML('beforeend', html);
            });
        }).catch(e => { container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Gagal memuat toko.</div>'; console.error(e); });
    }

    // Auto-generate no_raf when category or periode_bulan changes
    const categoryEl = document.getElementById('category');
    const periodeBulanEl = document.getElementById('periode_bulan');
    const noRafEl = document.getElementById('no_raf');
    const rafSeqEl = document.getElementById('raf_sequence');

    async function refreshNoRaf(){
        const cat = categoryEl.value || 'RAF';
        const periode = periodeBulanEl.value;
        const url = new URL('{{ url('/next-no-raf') }}', window.location.origin);
        url.searchParams.set('category', cat);
        if (periode) url.searchParams.set('periode', periode);
        try{
            const res = await fetch(url.toString());
            const j = await res.json();
            noRafEl.value = j.no_raf;
            rafSeqEl.value = j.sequence;
        }catch(e){ console.error(e); }
    }

    categoryEl.addEventListener('change', function(){
        // switch form action
        const form = document.getElementById('unifiedForm');
        if (this.value === 'JSM') form.action = '{{ url('/jsm') }}';
        else form.action = '{{ url('/rafaksi') }}';
        refreshNoRaf();
    });

    periodeBulanEl.addEventListener('change', refreshNoRaf);
    document.getElementById('local_pt_filter').addEventListener('change', function(){
        const region = document.getElementById('region_filter').value;
        if(region) fetchTokos(region);
    });

    window.addEventListener('load', function(){
        refreshNoRaf();
    });
</script>

@endsection
