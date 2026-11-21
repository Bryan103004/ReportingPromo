@extends('layouts.app')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<div class="mx-auto max-w-7xl px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header (Quiet Chrome) -->
        <div class="border-b border-slate-200/80 bg-slate-50/70 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-1.5 text-xs font-medium text-slate-500 mb-1">
                    <a href="{{ route('rafaksi.index') }}" class="hover:text-slate-800 transition-colors">Rafaksi</a>
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    <span>Edit Dokumen</span>
                </div>
                <h1 class="text-lg font-semibold text-slate-900 tracking-tight">Edit Data Rafaksi</h1>
            </div>
            <div class="flex items-center gap-2.5">
                <button type="button" onclick="document.getElementById('excelFileInput').click()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition-colors">
                    <svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
                    Import dari Excel
                </button>
                <input type="file" id="excelFileInput" accept=".xlsx,.xls" class="hidden">
                <a href="{{ route('rafaksi.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition-colors">
                    Kembali
                </a>
            </div>
        </div>

        <div class="p-6 md:p-8 space-y-8">
            <!-- Tampilkan Error Validasi Global (Jika Ada) -->
            @if ($errors->any())
                <div class="mb-6 rounded-lg border-l-4 border-rose-500 bg-rose-50 p-4">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-rose-500 mr-2.5 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wider text-rose-800">Terdapat kesalahan validasi:</h4>
                            <ul class="text-xs text-rose-700 list-disc list-inside mt-1.5 space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

        <form id="rafaksiEditForm" action="{{route('rafaksi.update', ['rafaksi' => $rafaksi->id, 'page' => request('page')])}}" method="POST" class="space-y-8" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Section 1: Dokumen & Supplier -->
            <div>
                <div class="border-b border-slate-200 pb-2 mb-5">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700">1. Data Dokumen & Supplier</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Informasi supplier, kategori, dan periode rafaksi.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                
                {{-- Kode Supplier --}}
                <div>
                    <label for="choices-supplier" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Kode Supplier <span class="text-rose-500">*</span>
                    </label>
                    
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <select name="supplier_code" id="choices-supplier" 
                                class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-500" 
                                required>
                                <option value="" disabled class="text-slate-400">Pilih atau cari supplier...</option>
                                @foreach ($supplierRafaksi as $supplier)
                                    <option value="{{ $supplier->kode_supplier }}" {{ old('supplier_code', $rafaksi->supplier_code) == $supplier->kode_supplier ? 'selected' : '' }}>
                                        {{ $supplier->kode_supplier }} - {{ $supplier->nama_supplier }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="button" onclick="openModal()" 
                            class="inline-flex h-[38px] shrink-0 items-center justify-center gap-1 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition" 
                            title="Tambah Supplier Baru">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            <span>Baru</span>
                        </button>
                    </div>
                </div>

                {{-- Nama Supplier --}}
                <div>
                    <label for="supplier_name" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Supplier <span class="text-rose-500">*</span></label>
                    <input type="text" name="supplier_name" id="supplier_name" value="{{ old('supplier_name', $rafaksi->supplier_name) }}" class="w-full rounded-lg border border-slate-200 bg-slate-50/80 px-3.5 py-2 text-sm text-slate-700 shadow-xs cursor-default outline-none" placeholder="Otomatis terisi saat memilih supplier" readonly required>
                </div>    
                
                {{-- Category --}}
                <div>
                    <label for="category_id" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">
                        Category <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="category_id" id="category_id" 
                            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10 appearance-none" 
                            required>
                            <option value="" disabled class="text-slate-400">-- Pilih Category --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $rafaksi->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->nama_kategori }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>    
                
                {{-- Periode Awal --}}
                <div>
                    <label for="periode_awal" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Periode Awal Rafaksi <span class="text-rose-500">*</span></label>
                    <input type="text" name="periode_awal" id="periode_awal" value="{{ old('periode_awal', $rafaksi->periode_awal ? date('Y-m-d', strtotime($rafaksi->periode_awal)) : '') }}" class="date-dmy w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" required>
                </div>

                {{-- Periode Akhir --}}
                <div>
                    <label for="periode_akhir" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Periode Akhir Rafaksi <span class="text-rose-500">*</span></label>
                    <input type="text" name="periode_akhir" id="periode_akhir" value="{{ old('periode_akhir', $rafaksi->periode_akhir ? date('Y-m-d', strtotime($rafaksi->periode_akhir)) : '') }}" class="date-dmy w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" required>
                </div> 

                {{-- No. RAF --}}
                <div>
                    <label for="no_raf" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">No. RAF <span class="text-rose-500">*</span></label>
                    <input type="text" name="no_raf" id="no_raf" value="{{ old('no_raf', $rafaksi->no_raf) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" placeholder="Contoh: RAF/2026/001" required>
                </div>

                {{-- No. Shiji --}}
                <div>
                    <label for="no_shiji" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">No. Shiji</label>
                    <input type="text" name="no_shiji" id="no_shiji" value="{{ old('no_shiji', $rafaksi->no_shiji) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" placeholder="Nomor dokumen Shiji (opsional)">
                </div>

                {{-- No. Shiji Promotion --}}
                <div>
                    <label for="no_shiji_promotion" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">No. Shiji Promotion</label>
                    <input type="text" name="no_shiji_promotion" id="no_shiji_promotion" value="{{ old('no_shiji_promotion', $rafaksi->no_shiji_promotion) }}" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" placeholder="Nomor dokumen Shiji Promotion (opsional)">
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Status Email <span class="text-rose-500">*</span></label>
                    <select name="status_email" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" required>
                        <option value="aktif" {{ old('status_email', $rafaksi->status_email) == 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ old('status_email', $rafaksi->status_email) == 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                </div>

                {{-- Periode Bulan --}}
                <div>
                    <label for="periode_bulan" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Periode Rekap <span class="text-rose-500">*</span></label>
                    <input type="text" name="periode_bulan" id="periode_bulan" value="{{ old('periode_bulan', $rafaksi->periode_bulan ? date('Y-m-d', strtotime($rafaksi->periode_bulan)) : '') }}" class="date-dmy w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" required>
                </div>

            </div>
            </div>

            <!-- Section 2: Pemilihan Toko (Store) -->
            <div>
                <div class="border-b border-slate-200 pb-2 mb-5">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700">2. Pemilihan Toko (Store)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih region dan centang store yang berlaku untuk promo rafaksi ini.</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Filter Berdasarkan Region</label>
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex-1">
                                <select id="region_filter" multiple class="tom-select block w-full rounded-lg border-slate-300 shadow-xs sm:text-sm">
                                    @foreach($regions as $region)
                                        <option value="{{ $region->id }}">{{ $region->nama_region }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <label class="inline-flex items-center text-xs font-medium text-slate-600 cursor-pointer select-none py-1">
                                <input type="checkbox" id="local_pt_filter" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500/20 mr-2" />
                                <span>Hanya PT. MITRA BELANJA ANDA</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Daftar Toko <span class="text-rose-500">*</span></label>
                        <div id="toko_container" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 p-4 border border-slate-200 rounded-lg bg-slate-50/50 min-h-[90px]">
                            <div class="col-span-full text-center text-slate-400 text-xs py-5">
                                Silakan pilih region terlebih dahulu.
                            </div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="store" id="hidden_store_name" value="{{ old('store', $rafaksi->store) }}">
                    <input type="hidden" name="raf_sequence" id="raf_sequence" value="{{ old('raf_sequence', $rafaksi->raf_sequence) }}">
                    <input type="hidden" name="items_json" id="items_json_input" value="">
                </div>
            </div>

            <!-- Section 3: Baris Item (Detail per Artikel) -->
            <div>
                <div class="border-b border-slate-200 pb-2 mb-3 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700">3. Baris Item (Detail per Artikel)</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kosongkan jika hanya menggunakan 1 nominal manual. Kolom sales otomatis mengikuti toko yang dicentang di atas.</p>
                    </div>
                    <button type="button" onclick="addItemRow()" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-xs hover:bg-slate-50 hover:text-slate-900 transition">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Tambah Baris</span>
                    </button>
                </div>

                <div class="overflow-x-auto border border-slate-200 rounded-lg">
                    <table class="w-full text-xs" id="itemsTable">
                        <thead id="itemsTableHead" class="bg-slate-50 border-b border-slate-200"></thead>
                        <tbody id="itemsTableBody" class="divide-y divide-slate-100"></tbody>
                    </table>
                    <div id="itemsTableEmpty" class="text-center text-slate-400 text-xs py-6 bg-slate-50/50">Belum ada baris item. Klik "Tambah Baris" atau lakukan "Import dari Excel".</div>
                </div>
            </div>

            <!-- Section 4: Nominal, Catatan & Lampiran -->
            <div>
                <div class="border-b border-slate-200 pb-2 mb-5">
                    <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-700">4. Nominal & Lampiran Dokumen</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Penetapan nominal akhir, catatan tambahan, dan file pendukung.</p>
                </div>

                <div class="space-y-5">
                    {{-- Nominal --}}
                    <div>
                        <label for="nominal" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nominal <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                                <span class="text-slate-400 text-xs font-semibold">Rp</span>
                            </div>
                            <input type="number" name="nominal" id="nominal" min="0" step="any" value="{{ old('nominal', $rafaksi->nominal) }}" class="w-full rounded-lg border border-slate-300 bg-white pl-10 pr-3.5 py-2 text-sm font-mono text-slate-900 shadow-xs transition focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10" placeholder="0" required>
                        </div>
                        <p id="nominalComputedHint" class="text-xs text-slate-500 mt-1.5 flex items-center gap-1 hidden">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Otomatis dihitung dari total Value semua baris item.
                        </p>
                    </div>

                    <!-- Remark -->
                    <div>
                        <label for="remarks" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Remarks / Catatan</label>  
                        <textarea name="remarks" id="remarks" rows="3" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm text-slate-900 shadow-xs transition placeholder:text-slate-400 focus:border-slate-500 focus:outline-none focus:ring-2 focus:ring-slate-500/10 @error('remarks') border-rose-500 focus:ring-rose-500/20 @enderror" placeholder="Tambahkan catatan jika diperlukan...">{{ old('remarks', $rafaksi->remarks) }}</textarea>
                    </div> 

                    <!-- Dokumen PDF -->
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Dokumen Lampiran (PDF)</label>

                        @if ($rafaksi->documents->isNotEmpty())
                            <ul class="mb-3 divide-y divide-slate-200 rounded-lg border border-slate-200 bg-slate-50/50">
                                @foreach ($rafaksi->documents as $doc)
                                    <li class="flex items-center justify-between gap-3 px-4 py-2.5 text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            <a href="{{ route('rafaksi.documents.download', $doc->id) }}" class="font-medium text-slate-700 hover:text-slate-900 hover:underline truncate" target="_blank">
                                                {{ $doc->filename }}
                                            </a>
                                        </div>
                                        <button type="button" onclick="submitDocumentDelete('{{ route('rafaksi.documents.destroy', $doc->id) }}')" class="text-rose-600 hover:text-rose-800 text-xs font-semibold shrink-0">Hapus</button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="rounded-lg border border-dashed border-slate-300 p-4 bg-slate-50/30">
                            <label for="document_file" class="block text-xs font-medium text-slate-600 mb-1">Unggah Dokumen Baru</label>
                            <input type="file" name="document_file[]" id="document_file" accept="application/pdf" multiple class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-slate-200 file:text-slate-700 hover:file:bg-slate-300 cursor-pointer @error('document_file.*') border-rose-500 @enderror">
                            <p class="text-[11px] text-slate-400 mt-1.5">Format PDF, maksimal 10MB per file. Bisa pilih lebih dari satu file sekaligus.</p>
                            @error('document_file.*')
                                <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200">
                <a href="{{ route('rafaksi.show_month', ['year' => $year, 'month' => $month, 'page' => request('page')]) }}" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 hover:text-slate-900 transition shadow-xs">
                    Batal
                </a>
                
                <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition shadow-xs">
                    Simpan Perubahan
                </button>
            </div>
        </form>

        {{-- ================= MODAL TAMBAH SUPPLIER ================= --}}
        <div id="supplierModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 flex items-center justify-center p-4 backdrop-blur-xs">
            <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden transform">
                <div class="px-6 py-4 border-b border-slate-200/80 flex justify-between items-center bg-slate-50/80">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-800">Tambah Supplier Baru</h3>
                    <button type="button" onclick="closeModal()" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form id="addSupplierForm" class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Kode Supplier <span class="text-rose-500">*</span></label>
                        <input type="text" id="new_kode_supplier" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-500/10 outline-none" required placeholder="Contoh: SUP-001">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1.5">Nama Supplier <span class="text-rose-500">*</span></label>
                        <input type="text" id="new_nama_supplier" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-slate-500 focus:ring-2 focus:ring-slate-500/10 outline-none" required placeholder="Contoh: PT. Sumber Makmur">
                    </div>

                    <div class="flex justify-end gap-2.5 pt-2">
                        <button type="button" onclick="closeModal()" id="btnCancelSupplier" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">Batal</button>
                        <button type="button" onclick="saveNewSupplier()" id="btnSaveSupplier" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition shadow-xs">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================= MODAL PREVIEW IMPORT EXCEL ================= --}}
        <div id="importPreviewModal" class="fixed inset-0 z-50 hidden bg-slate-900/40 flex items-center justify-center p-4 backdrop-blur-xs">
            <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-5xl max-h-[90vh] overflow-hidden flex flex-col">
                <div class="px-6 py-4 border-b border-slate-200/80 flex justify-between items-center bg-slate-50/80 shrink-0">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-800">Preview Import Excel</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Tinjau ringkasan data sebelum diterapkan ke form dokumen.</p>
                    </div>
                    <button type="button" onclick="closeImportPreview()" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto space-y-4">
                    <div id="importPreviewLoading" class="text-center text-slate-500 py-10">
                        <div class="inline-block animate-spin rounded-full h-6 w-6 border-2 border-slate-300 border-t-slate-800 mb-2"></div>
                        <div class="text-xs font-medium text-slate-600">Membaca dan memverifikasi file Excel...</div>
                    </div>
                    <div id="importPreviewContent" class="hidden space-y-4">
                        <div id="importPreviewErrors" class="hidden bg-rose-50 border-l-4 border-rose-500 p-4 rounded-r-lg text-xs text-rose-700"></div>
                        <div id="importPreviewWarnings" class="hidden bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg text-xs text-amber-800"></div>

                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 p-4 rounded-lg bg-slate-50 border border-slate-200 text-xs">
                            <div><span class="text-slate-500 block">Periode Awal</span> <span id="pvPeriodeAwal" class="font-semibold text-slate-800"></span></div>
                            <div><span class="text-slate-500 block">Periode Akhir</span> <span id="pvPeriodeAkhir" class="font-semibold text-slate-800"></span></div>
                            <div><span class="text-slate-500 block">No. Shiji</span> <span id="pvNoShiji" class="font-semibold text-slate-800"></span></div>
                            <div><span class="text-slate-500 block">No. Shiji Promotion</span> <span id="pvNoShijiPromotion" class="font-semibold text-slate-800"></span></div>
                            <div><span class="text-slate-500 block">Vendor</span> <span id="pvVendor" class="font-semibold text-slate-800"></span></div>
                            <div><span class="text-slate-500 block">No. RAF di file</span> <span id="pvNoRaf" class="font-semibold text-slate-400"></span></div>
                            <div class="col-span-2 md:col-span-3"><span class="text-slate-500 block">Toko</span> <span id="pvStores" class="font-semibold text-slate-800"></span></div>
                        </div>

                        <div class="overflow-x-auto border border-slate-200 rounded-lg">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-semibold uppercase tracking-wider text-slate-600">
                                    <tr>
                                        <th class="px-3 py-2 text-left">ARTICLE</th>
                                        <th class="px-3 py-2 text-left">DESCRIPTION</th>
                                        <th class="px-3 py-2 text-right">REG</th>
                                        <th class="px-3 py-2 text-right">DISC NOMINAL</th>
                                        <th class="px-3 py-2 text-right">PROMO DISC</th>
                                        <th class="px-3 py-2 text-right bg-slate-100 font-mono">CLAIM</th>
                                        <th class="px-3 py-2 text-right">Total Sales</th>
                                    </tr>
                                </thead>
                                <tbody id="importPreviewItemsBody" class="divide-y divide-slate-100"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-200/80 bg-slate-50/50 flex justify-end gap-2.5 shrink-0">
                    <button type="button" onclick="closeImportPreview()" class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition shadow-xs">Batal</button>
                    <button type="button" id="btnApproveImport" onclick="approveImportPreview()" class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 transition shadow-xs disabled:opacity-50 disabled:cursor-not-allowed">Gunakan Data Ini</button>
                </div>
            </div>
        </div>

        </div>
    </div>
</div>

@php
    $itemRowsForJs = $rafaksi->items->map(function ($item) {
        return [
            'article' => $item->article,
            'shiji_code' => $item->shiji_code,
            'description' => $item->description,
            'disc_nominal' => $item->disc_nominal,
            'promo_disc' => $item->promo_disc,
            'reg' => $item->reg,
            'promo' => $item->promo,
            'sales' => $item->stores->mapWithKeys(function ($s) {
                return [(string) $s->toko_id => $s->sales_qty];
            })->toArray(),
        ];
    })->values();
@endphp
<script>
    // Form dokumen dibuat lewat JS (bukan <form> bersarang di dalam form utama --
    // <form> nested tidak valid HTML dan browser akan "menaikkan" field-nya ke
    // form terluar, yang bisa bikin update dokumen malah men-trigger delete data utama).
    function submitDocumentDelete(url) {
        if (!confirm('Hapus dokumen ini?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;
        form.style.display = 'none';
        form.innerHTML = `@csrf @method('DELETE')`;
        document.body.appendChild(form);
        form.submit();
    }

    let supplierList = @json($supplierRafaksi);
    // Selected toko ids for edit
    window.selectedTokos = @json($rafaksi->tokos->pluck('id'));
    // Baris item yang sudah tersimpan (kalau ada), buat pre-populate tabel item
    window.itemRows = @json($itemRowsForJs);

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
            supplierList.push({ kode_supplier: kodeSupplier, nama_supplier: namaSupplier });

            const select = document.getElementById('choices-supplier');
            const option = document.createElement('option');
            option.value = kodeSupplier;
            option.text = kodeSupplier + ' - ' + namaSupplier;
            option.selected = true;
            select.appendChild(option);

            document.getElementById('supplier_name').value = namaSupplier;

            closeModal();
            btn.innerHTML = 'Simpan';
            btn.disabled = false;
        })
        .catch((error) => {
            console.error('Error:', error);
            // Alert sekarang akan menunjukkan secara spesifik apa yang salah
            alert('Gagal: ' + error.message);
            btn.innerHTML = 'Simpan';
            btn.disabled = false;
        });
    }

    // Simpan toko yang lagi dicentang sebelum region-nya berubah/di-refresh,
    // biar centangan gak ilang pas nambah/ganti region (bisa multi-region sekarang).
    window.checkedTokoIds = new Set((window.selectedTokos || []).map(String));

    function rememberCheckedTokos(){
        document.querySelectorAll('#toko_container input[type=checkbox]').forEach(cb => {
            if (cb.checked) window.checkedTokoIds.add(cb.value);
            else window.checkedTokoIds.delete(cb.value);
        });
    }

    async function fetchTokos(skipRemember) {
        const container = document.getElementById('toko_container');
        const hiddenStore = document.getElementById('hidden_store_name');
        // skipRemember: dipakai pas approve import Excel, karena checkedTokoIds sudah
        // di-set eksplisit dari hasil parse -- kalau tetap "diingat" dari DOM lama,
        // toko yang belum sempat dirender bakal ke-hapus lagi dari Set-nya.
        if (!skipRemember) {
            rememberCheckedTokos();
        }

        const regionSelect = document.getElementById('region_filter');
        const regionIds = Array.from(regionSelect.selectedOptions).map(o => o.value);
        const regionNames = Array.from(regionSelect.selectedOptions).map(o => o.text);
        hiddenStore.value = regionNames.length ? regionNames.join(', ') : '-';

        if (!regionIds.length) {
            container.innerHTML = '<div class="col-span-full text-center text-gray-400 text-sm py-4">Silakan pilih region terlebih dahulu.</div>';
            renderItemsTable();
            return;
        }

        container.innerHTML = '<div class="col-span-full text-center text-blue-500 text-sm py-4">Memuat data toko...</div>';

        const ptChecked = document.getElementById('local_pt_filter') && document.getElementById('local_pt_filter').checked;
        const ptParam = ptChecked ? '&name_pt=' + encodeURIComponent('PT. MITRA BELANJA ANDA') : '';

        try {
            const base = "{{ url('/get-tokos') }}/";
            const results = await Promise.all(regionIds.map(id => fetch(base + id + '?_=1' + ptParam).then(r => r.json())));
            const seen = new Map();
            results.flat().forEach(t => seen.set(t.id, t));
            const data = Array.from(seen.values());

            if (!data.length) {
                container.innerHTML = '<div class="col-span-full text-center text-red-500 text-sm py-4">Tidak ada toko untuk region/filter ini.</div>';
                renderItemsTable();
                return;
            }

            container.innerHTML = '';
            data.forEach(toko => {
                const div = document.createElement('div');
                div.className = 'flex items-center';
                const checked = window.checkedTokoIds.has(String(toko.id)) ? 'checked' : '';
                div.className = "flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:border-slate-300 transition-colors shadow-2xs";
                div.innerHTML = `
                <input type="checkbox" id="toko_${toko.id}" name="toko_id[]" value="${toko.id}" ${checked}
                    class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-500/20 cursor-pointer">
                <label for="toko_${toko.id}" class="text-xs font-medium text-slate-800 cursor-pointer select-none truncate">
                    ${toko.nama_toko}
                </label>
                `;
                container.appendChild(div);
            });

            renderItemsTable();
        } catch (error) {
            console.error('Error fetching tokos:', error);
            container.innerHTML = '<div class="col-span-full text-center text-red-500 text-sm py-4">Gagal memuat data.</div>';
        }
    }

    document.getElementById('toko_container').addEventListener('change', function(e){
        if (e.target.matches('input[type=checkbox]')) {
            renderItemsTable();
        }
    });

    document.getElementById('local_pt_filter').addEventListener('change', fetchTokos);

    // ===================== BARIS ITEM (DETAIL PER ARTIKEL) =====================
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

        let headHtml = '<tr class="border-b border-slate-200">';
        headHtml += '<th class="px-3 py-2 text-left font-semibold text-[11px] text-slate-500 uppercase tracking-wider">NO</th>';
        headHtml += '<th class="px-3 py-2 text-left font-semibold text-[11px] text-slate-500 uppercase tracking-wider">ARTICLE</th>';
        headHtml += '<th class="px-3 py-2 text-left font-semibold text-[11px] text-slate-500 uppercase tracking-wider">SHIJI CODE</th>';
        headHtml += '<th class="px-3 py-2 text-left font-semibold text-[11px] text-slate-500 uppercase tracking-wider">DESCRIPTION</th>';
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-500 uppercase tracking-wider">DISC NOMINAL</th>';
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-500 uppercase tracking-wider">PROMO DISC (%)</th>';
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-500 uppercase tracking-wider">REG</th>';
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-500 uppercase tracking-wider">PROMO</th>';
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-700 uppercase tracking-wider bg-slate-100">CLAIM</th>';
        stores.forEach(s => { headHtml += `<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-500 uppercase tracking-wider whitespace-nowrap">Sales ${s.name}</th>`; });
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-700 uppercase tracking-wider bg-slate-100">Sales TOTAL</th>';
        stores.forEach(s => { headHtml += `<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-500 uppercase tracking-wider whitespace-nowrap">Value ${s.name}</th>`; });
        headHtml += '<th class="px-3 py-2 text-right font-semibold text-[11px] text-slate-700 uppercase tracking-wider bg-slate-100">Value TOTAL</th>';
        headHtml += '<th class="px-2 py-2"></th>';
        headHtml += '</tr>';
        thead.innerHTML = headHtml;

        let bodyHtml = '';
        window.itemRows.forEach((row, i) => {
            bodyHtml += `<tr class="border-b border-slate-100 hover:bg-slate-50/50 transition-colors">`;
            bodyHtml += `<td class="px-3 py-2 text-slate-400 font-mono text-[11px]">${i + 1}</td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="text" class="w-28 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="article" data-row="${i}" value="${escAttr(row.article)}" required></td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="text" class="w-24 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="shiji_code" data-row="${i}" value="${escAttr(row.shiji_code)}"></td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="text" class="w-32 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="description" data-row="${i}" value="${escAttr(row.description)}"></td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="number" step="0.01" class="w-20 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono text-right focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="disc_nominal" data-row="${i}" value="${escAttr(row.disc_nominal)}"></td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="number" step="0.01" class="w-16 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono text-right focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="promo_disc" data-row="${i}" value="${escAttr(row.promo_disc)}"></td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="number" step="0.01" class="w-20 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono text-right focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="reg" data-row="${i}" value="${escAttr(row.reg)}" required></td>`;
            bodyHtml += `<td class="px-1.5 py-1.5"><input type="number" step="0.01" class="w-20 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono text-right focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="promo" data-row="${i}" value="${escAttr(row.promo)}"></td>`;
            bodyHtml += `<td class="px-3 py-2 text-right font-semibold font-mono text-slate-800 bg-slate-50" data-claim-cell="${i}">0</td>`;
            stores.forEach(s => {
                const val = row.sales[s.id] ?? '';
                bodyHtml += `<td class="px-1.5 py-1.5"><input type="number" step="0.01" class="w-16 rounded-md border border-slate-300 bg-white text-xs px-2 py-1 font-mono text-right focus:border-slate-500 focus:ring-1 focus:ring-slate-400 outline-none" data-field="sales" data-store="${s.id}" data-row="${i}" value="${escAttr(val)}"></td>`;
            });
            bodyHtml += `<td class="px-3 py-2 text-right font-semibold font-mono text-slate-800 bg-slate-50" data-salestotal-cell="${i}">0</td>`;
            stores.forEach(s => {
                bodyHtml += `<td class="px-3 py-2 text-right font-mono text-slate-700" data-value-cell="${i}-${s.id}">0</td>`;
            });
            bodyHtml += `<td class="px-3 py-2 text-right font-semibold font-mono text-slate-800 bg-slate-50" data-valuetotal-cell="${i}">0</td>`;
            bodyHtml += `<td class="px-2 py-1.5 text-center"><button type="button" onclick="removeItemRow(${i})" class="inline-flex items-center justify-center w-6 h-6 rounded text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus baris"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></button></td>`;
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

    function getNextNoRaf(){
        const categorySelect = document.getElementById('category_id');
        const cat_id = categorySelect.value;
        const periode = document.getElementById('periode_bulan') ? document.getElementById('periode_bulan').value : null;
        if (!categorySelect || !cat_id) return;

        const url = new URL('{{ url('/next-no-raf') }}', window.location.origin);
        url.searchParams.set('category', 'RAF');
        url.searchParams.set('category_id', cat_id);
        if (periode) url.searchParams.set('periode', periode);

        fetch(url).then(r => r.json()).then(data => {
            if (data.no_raf) {
                document.getElementById('no_raf').value = data.no_raf;
                document.getElementById('raf_sequence').value = data.sequence || '';
            }
        }).catch(err => console.error(err));
    }

    const cat = document.getElementById('category_id');
    if (cat) cat.addEventListener('change', getNextNoRaf);
    const per = document.getElementById('periode_bulan');
    if (per) per.addEventListener('change', getNextNoRaf);

    // Baris item dikirim sebagai 1 field JSON (bukan ratusan field items[i][...]
    // terpisah) -- dokumen gede (banyak baris x banyak toko) bisa nembus limit
    // max_input_vars PHP (default 1000) dan datanya kepotong diam-diam tanpa ada
    // error apapun kalau masih pakai field per-baris.
    document.getElementById('rafaksiEditForm').addEventListener('submit', function(){
        document.getElementById('items_json_input').value = JSON.stringify(window.itemRows);
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.regionTomSelect = new TomSelect('#region_filter', {
            plugins: ['remove_button'],
            create: false,
            maxItems: null,
            placeholder: '-- Pilih Region (bisa lebih dari satu) --',
            onChange: fetchTokos,
        });

        const initialRegionIds = @json($rafaksi->tokos->pluck('region_id')->unique()->values());
        if (initialRegionIds.length) {
            window.regionTomSelect.setValue(initialRegionIds.map(String));
        } else {
            renderItemsTable();
        }
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
        document.getElementById('pvNoRaf').textContent = (h.no_raf || '-') + ' (tidak menimpa No. RAF dokumen ini)';
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
        if (h.periode_awal) window.setDateFieldValue('periode_awal', h.periode_awal);
        if (h.periode_akhir) window.setDateFieldValue('periode_akhir', h.periode_akhir);
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