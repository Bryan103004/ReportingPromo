@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    <div class="mb-4">
        <a href="{{ route('biltu.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider flex items-center gap-1">
            &larr; Kembali ke Daftar Master Biltu
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span>➕</span> Tambah Master Biltu Baru
            </h2>
            <p class="text-xs text-gray-500 mt-1">Lengkapi informasi partner Biltu (C_BPartner_ID) dan relasi supplier di bawah ini.</p>
        </div>

        {{-- Error Validation Alerts --}}
        @if ($errors->any())
            <div class="m-6 p-4 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700">
                <span class="font-bold">Perhatian: Terjadi kesalahan input:</span>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('biltu.store') }}" method="POST" class="p-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                {{-- C_BPartner_ID --}}
                <div>
                    <label for="C_BPartner_ID" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        C_BPartner_ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="C_BPartner_ID" id="C_BPartner_ID" value="{{ old('C_BPartner_ID') }}" 
                        placeholder="Contoh: 1125516" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono" required>
                    <p class="text-[11px] text-gray-400 mt-1">ID unik BPartner dari sistem ERP Biltu.</p>
                </div>

                {{-- Value (Search Key) --}}
                <div>
                    <label for="value" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Search Key / Value <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="value" id="value" value="{{ old('value') }}" 
                        placeholder="Contoh: C00010 atau JC00010" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono" required>
                    <p class="text-[11px] text-gray-400 mt-1">Kode Search Key (Awalan JC untuk MBA, awalan C untuk Non-MBA).</p>
                </div>

                {{-- Tipe PT --}}
                <div>
                    <label for="is_mba" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Tipe PT (Kategori) <span class="text-red-500">*</span>
                    </label>
                    <select name="is_mba" id="is_mba" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                        <option value="0" {{ old('is_mba') === '0' || old('is_mba') === null ? 'selected' : '' }}>Non-MBA / GrandLucky (Prefix C)</option>
                        <option value="1" {{ old('is_mba') === '1' ? 'selected' : '' }}>PT. Mitra Belanja Anda / MBA (Prefix JC)</option>
                    </select>
                </div>

                {{-- Relasi Supplier Rafaksi --}}
                <div>
                    <label for="vendor_no" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Relasi Supplier Rafaksi (Vendor No)
                    </label>
                    <select name="vendor_no" id="vendor_no" class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                        <option value="">-- Pilih Supplier Rafaksi (Opsional) --</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->kode_supplier }}" {{ old('vendor_no') == $sup->kode_supplier ? 'selected' : '' }}>
                                {{ $sup->kode_supplier }} - {{ $sup->nama_supplier }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1">Menghubungkan Biltu ini dengan master Supplier Rafaksi saat pembuatan dokumen.</p>
                </div>

                {{-- Nama Partner --}}
                <div>
                    <label for="name" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Nama Partner <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" 
                        placeholder="Contoh: PT. BAHANA GENTA VIKTORY" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500" required>
                </div>

                {{-- Nama 2 --}}
                <div>
                    <label for="name2" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Nama Alternatif / Nama 2 (Opsional)
                    </label>
                    <input type="text" name="name2" id="name2" value="{{ old('name2') }}" 
                        placeholder="Contoh: BAHANA GENTA" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                {{-- NPWP / Tax ID --}}
                <div>
                    <label for="taxid" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Tax ID / NPWP
                    </label>
                    <input type="text" name="taxid" id="taxid" value="{{ old('taxid') }}" 
                        placeholder="Contoh: 01.234.567.8-901.000" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                </div>

                {{-- NITKU --}}
                <div>
                    <label for="NITKU" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        NITKU
                    </label>
                    <input type="text" name="NITKU" id="NITKU" value="{{ old('NITKU') }}" 
                        placeholder="Contoh: 0000000000000000" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 font-mono">
                </div>

                {{-- Kota --}}
                <div>
                    <label for="City" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Kota
                    </label>
                    <input type="text" name="City" id="City" value="{{ old('City') }}" 
                        placeholder="Contoh: JAKARTA BARAT" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                {{-- Region Name --}}
                <div>
                    <label for="regionName" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Provinsi / Region
                    </label>
                    <input type="text" name="regionName" id="regionName" value="{{ old('regionName') }}" 
                        placeholder="Contoh: DKI JAKARTA" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                {{-- Alamat 1 --}}
                <div class="md:col-span-2">
                    <label for="Address1" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Alamat Lengkap (Address 1)
                    </label>
                    <input type="text" name="Address1" id="Address1" value="{{ old('Address1') }}" 
                        placeholder="Contoh: JL. RAYA PERJUANGAN NO. 88" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                {{-- Phone --}}
                <div>
                    <label for="phone" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Telepon
                    </label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" 
                        placeholder="Contoh: 021-1234567" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>

                {{-- Kode Pos --}}
                <div>
                    <label for="postal" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Kode Pos
                    </label>
                    <input type="text" name="postal" id="postal" value="{{ old('postal') }}" 
                        placeholder="Contoh: 11530" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                </div>
            </div>

            {{-- Footer Action --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('biltu.index') }}" class="px-5 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition uppercase tracking-wider">
                    Batal
                </a>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm uppercase tracking-wider">
                    Simpan Biltu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
