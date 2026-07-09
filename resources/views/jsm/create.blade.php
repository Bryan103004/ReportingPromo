@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-bold text-gray-800">Formulir Tambah JSM</h2>
            <p class="text-sm text-gray-500 mt-1">Silakan lengkapi data supplier dan detail jsm di bawah ini.</p>
        </div>

        <form action="{{route('jsm.store')}}" method="POST" class="p-6">
            @csrf
            
            {{-- Grid 2 Kolom --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                {{-- Kode Supplier --}}
                <div class="space-y-2">
                    <!-- Label dengan layout yang lebih bersih -->
                    <label for="choices-supplier" class="block text-sm font-medium text-gray-700 tracking-wide">
                        Kode Supplier <span class="text-rose-500 font-bold" title="Wajib diisi">*</span>
                    </label>
                    
                    <!-- Container Flex menggunakan grid-like ratio yang konsisten -->
                    <div class="flex items-center gap-3 ">
                        <!-- Select Dropdown dengan shadow subtle dan ring focus yang lebih smooth -->
                        <div class="relative flex-1">
                            <select name="supplier_code" id="choices-supplier" 
                                class="w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition-all placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500" 
                                required>
                                <option value="" disabled selected class="text-gray-400">Pilih atau cari supplier...</option>
                                @foreach ($supplierRafaksi as $supplier)
                                    <option value="{{ $supplier->kode_supplier }}" class="text-gray-900">
                                        {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <!-- Tombol Trigger Modal dengan desain solid & micro-interaction yang bagus -->
                        <button type="button" onclick="openModal()" 
                            class="inline-flex h-[42px] shrink-0 items-center justify-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 active:bg-blue-800" 
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
                    <input type="text" name="supplier_name" id="supplier_name" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Otomatis Terisi Saat Anda Memilih Kode Supplier" readonly required>
                </div>     
                
                {{-- Periode Awal --}}
                <div>
                    <label for="periode_awal" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Awal Rafaksi<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_awal" id="periode_awal" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" required>
                </div>

                {{-- Periode Akhir --}}
                <div>
                    <label for="periode_akhir" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Akhir Rafaksi<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_akhir" id="periode_akhir" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" required>
                </div> 

                {{-- No. RAF --}}
                <div>
                    <label for="no_raf" class="block text-sm font-semibold text-gray-700 mb-1.5">No. RAF <span class="text-red-500">*</span></label>
                    <input type="text" name="no_raf" id="no_raf" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Contoh: RAF/2026/001" required>
                </div>

                {{-- Periode Bulan --}}
                <div>
                    <label for="periode_bulan" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Rekap<span class="text-red-500">*</span></label>
                    <input type="date" name="periode_bulan" id="periode_bulan" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" required>
                </div>

                {{-- Store --}}
                <!-- <div>
                    <label for="store" class="block text-sm font-semibold text-gray-700 mb-1.5">Store <span class="text-red-500">*</span></label>
                    <input type="text" name="store" id="store" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Masukkan Store.." required>
                </div>  -->

                <div class="md:col-span-2 text-lg font-semibold text-gray-700 border-b pb-2 mt-4">Pemilihan Toko (Store)</div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filter Berdasarkan Region</label>
                    <select id="region_filter" onchange="fetchTokos(this.value)" class="block w-full md:w-1/2 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="">-- Pilih Region untuk memunculkan Toko --</option>
                        @foreach($regions as $region)
                            <option value="{{ $region->id }}">{{ $region->nama_region }}</option>
                        @endforeach
                    </select>
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
            </div>

            {{-- Nominal (Full Width di bawah) --}}
            <div class="mb-8 mx-6">
                <label for="nominal" class="block text-sm font-semibold text-gray-700 mb-1.5">Nominal <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                        <span class="text-gray-500 text-sm font-medium">Rp</span>
                    </div>
                    <input type="number" name="nominal" id="nominal" class="w-full rounded-md border border-gray-300 pl-10 pr-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="0" required>
                </div>
            </div> 

            <!-- Remark -->
            <div class="mb-8 mx-6">
                <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-1.5">Remarks</label>  
                <textarea name="remarks" id="remarks"  rows="3" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors @error('remarks') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror" placeholder="Masukkan catatan di sini..."></textarea>
            </div> 

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                {{-- Sesuaikan route cancel dengan route index milikmu --}}
                <a href="{{ url()->previous() }}" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                    Simpan Data
                </button>
            </div>
        </form> 

        {{-- ================= MODAL TAMBAH SUPPLIER ================= --}}
        <div id="supplierModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4 backdrop-blur-sm transition-opacity">
            <div class="bg-white rounded-xl shadow-lg w-full max-w-md overflow-hidden transform transition-all">
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
                                <input type="text" id="new_kode_supplier" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required placeholder="Contoh: SUP-001">
                            </div>
                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Supplier <span class="text-red-500">*</span></label>
                                <input type="text" id="new_nama_supplier" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none" required placeholder="Contoh: PT. Sumber Makmur">
                            </div>
                            
                            <div class="flex justify-end gap-3">
                                <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                                <button type="button" onclick="saveNewSupplier()" id="btnSaveSupplier" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center justify-center min-w-[100px]">
                                    Simpan
                                </button>
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

        // Cari nama supplier berdasarkan kode yang dipilih
        var selectedSupplier = supplierList.find(supplier => supplier.kode_supplier === selectedSupplierCode);

        // Jika ditemukan, isi input nama supplier dengan nama yang sesuai
        if (selectedSupplier) {
            supplierNameInput.value = selectedSupplier.nama_supplier;
        } else {
            supplierNameInput.value = ''; // Kosongkan jika tidak ada yang dipilih
        }
    })

    function openModal() {
        document.getElementById('supplierModal').classList.remove('hidden');
    }
    
    function closeModal() {
        // 1. Sembunyikan modal
        document.getElementById('supplierModal').classList.add('hidden');
        
        // 2. Cari form-nya
        let formModal = document.getElementById('addSupplierForm');
        
        // 3. Hanya lakukan reset JIKA form-nya benar-benar ditemukan
        if (formModal) {
            formModal.reset(); 
        } else {
            // Alternatif jika form.reset() tetap gagal: kosongkan input manual
            document.getElementById('new_kode_supplier').value = '';
            document.getElementById('new_nama_supplier').value = '';
        }
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

        else if(supplierList.some(supplier => supplier.nama_supplier.toLowerCase() === namaSupplier.toLowerCase())){
            alert('Nama Supplier sudah ada. Silakan gunakan nama lain.');
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
            // Cek apakah HTTP statusnya sukses (200-299)
            if (!response.ok) {
                const err = await response.json();
                throw new Error(err.message || 'Kesalahan dari sisi server.');
            }
            
            // Cek apakah balasannya benar-benar JSON untuk mencegah crash
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) {
                return response.json();
            } else {
                throw new Error('Server merespon, tetapi bukan dengan format JSON.');
            }
        })
        .then(data => {
            // A. Masukkan data baru ke dalam Array JavaScript
            supplierList.push({ kode_supplier: kodeSupplier, nama_supplier: namaSupplier });

            // B & C. Tambahkan ke Choices.js dan langsung pilih
            supplierSelect.setChoices([
                {
                    value: kodeSupplier,
                    label: kodeSupplier,
                    selected: true,
                },
            ], 'value', 'label', false);

            // D. Trigger update input nama supplier
            document.getElementById('supplier_name').value = namaSupplier;

            // Tutup modal & kembalikan tombol
            closeModal();
            btn.innerHTML = 'Simpan';
            btn.disabled = false;

            alert('Data supplier baru berhasil ditambahkan.');
        })
        .catch((error) => {
            console.error('Error:', error);
            // Alert sekarang akan menunjukkan secara spesifik apa yang salah
            alert('Gagal: ' + error.message);
            btn.innerHTML = 'Simpan';
            btn.disabled = false;
        });
    }

    function fetchTokos(regionId) {
        const container = document.getElementById('toko_container');
        const hiddenStore = document.getElementById('hidden_store_name');
        const selectRegion = document.getElementById('region_filter');
        
        // Set input hidden 'store' sesuai nama region (opsional, jika controllermu masih meminta validasi 'store')
        hiddenStore.value = regionId ? selectRegion.options[selectRegion.selectedIndex].text : '-';

        // Jika region di-reset
        if (!regionId) {
            container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Silakan pilih region terlebih dahulu.</div>';
            return;
        }

        // Tampilkan loading state
        container.innerHTML = '<div class="col-span-full text-center text-blue-500 text-sm py-4">Memuat data toko...</div>';

        // Fetch data dari endpoint yang sudah kita buat
        fetch(`/get-tokos/${regionId}`)
            .then(response => response.json())
            .then(data => {
                container.innerHTML = ''; // Bersihkan container

                if (data.length === 0) {
                    container.innerHTML = '<div class="col-span-full text-center text-red-500 text-sm py-4">Tidak ada toko di region ini.</div>';
                    return;
                }

                // Loop data dan buat elemen checkbox
                data.forEach(toko => {
                    const div = document.createElement('div');
                    div.className = 'flex items-center';
                    
                    // Radio di-set 'checked' secara default (sesuai permintaanmu)
                    div.innerHTML = `
                        <input type="radio" id="toko_${toko.id}" name="toko_id" value="${toko.id}" checked 
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="toko_${toko.id}" class="ml-2 block text-sm text-gray-900 cursor-pointer">
                            ${toko.nama_toko}
                        </label>
                    `;
                    container.appendChild(div);
                });
            })
            .catch(error => {
                console.error('Error fetching tokos:', error);
                container.innerHTML = '<div class="col-span-full text-center text-red-500 text-sm py-4">Gagal memuat data.</div>';
            });
    }
</script>
@endsection