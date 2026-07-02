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
                <div>
                    <label for="supplier_code" class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Supplier <span class="text-red-500">*</span></label>
                    <div class="flex gap-x-2">
                        <select name="supplier_code" id="supplier_code" class="w-4/5 rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" required>
                            <option value="">Pilih Kode Supplier</option>
                            @foreach ($supplierRafaksi as $supplier)
                                <option value="{{ $supplier->kode_supplier }}">{{ $supplier->kode_supplier }}</option>
                            @endforeach
                        </select>
                        
                        {{-- Tombol Trigger Modal --}}
                        <button type="button" onclick="openModal()" class="w-1/5 shrink-0 bg-blue-50 text-blue-600 border border-blue-200 px-3 py-2 rounded-md hover:bg-blue-100 hover:text-blue-700 font-semibold text-sm transition-colors flex items-center gap-1" title="Tambah Supplier Baru">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Baru
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
                    <label for="periode_awal" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Awal <span class="text-red-500">*</span></label>
                    <input type="date" name="periode_awal" id="periode_awal" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" required>
                </div>

                {{-- Periode Akhir --}}
                <div>
                    <label for="periode_akhir" class="block text-sm font-semibold text-gray-700 mb-1.5">Periode Akhir <span class="text-red-500">*</span></label>
                    <input type="date" name="periode_akhir" id="periode_akhir" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" required>
                </div> 

                {{-- No. RAF --}}
                <div>
                    <label for="no_raf" class="block text-sm font-semibold text-gray-700 mb-1.5">No. RAF <span class="text-red-500">*</span></label>
                    <input type="text" name="no_raf" id="no_raf" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Contoh: RAF/2026/001" required>
                </div>

                {{-- Store --}}
                <div>
                    <label for="store" class="block text-sm font-semibold text-gray-700 mb-1.5">Store <span class="text-red-500">*</span></label>
                    <input type="text" name="store" id="store" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Masukkan Store.." required>
                </div> 
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

    document.getElementById('supplier_code').addEventListener('change', function() {
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

            // B. Buat elemen <option> baru dan tambahkan ke dalam <select>
            let select = document.getElementById('supplier_code');
            let newOption = new Option(kodeSupplier, kodeSupplier);
            select.add(newOption);

            // C. Pilih opsi yang baru ditambahkan secara otomatis
            select.value = kodeSupplier;
            
            // D. Trigger event 'change' agar nama supplier otomatis terisi
            select.dispatchEvent(new Event('change'));

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
</script>
@endsection