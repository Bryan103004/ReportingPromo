@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    {{-- Tombol Kembali --}}
    <div class="mb-4">
        <a href="{{ route('supplier_rafaksi.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider flex items-center gap-1">
            &larr; Kembali ke Data Master Supplier
        </a>
    </div>

    {{-- Card Form Edit --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span>✏️</span> Edit Supplier Rafaksi #{{ $supplier_rafaksi->id }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Perbarui kode atau nama supplier rafaksi.</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-mono font-bold bg-gray-100 text-gray-800 border border-gray-200">
                    Kode: {{ $supplier_rafaksi->kode_supplier }}
                </span>
            </div>
        </div>

        {{-- Error Validation Alert --}}
        @if ($errors->any())
            <div class="m-6 p-4 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700">
                <span class="font-bold">Terjadi kesalahan input:</span>
                <ul class="list-disc list-inside mt-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form Edit --}}
        <form action="{{ route('supplier_rafaksi.update', $supplier_rafaksi->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Kode Supplier --}}
                <div>
                    <label for="kode_supplier" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Kode Supplier <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="kode_supplier" id="kode_supplier" 
                            value="{{ old('kode_supplier', $supplier_rafaksi->kode_supplier) }}" 
                            placeholder="Contoh: 00016" 
                            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm font-mono text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" 
                            required>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Kode unik supplier yang terhubung ke dokumen dan master Biltu (vendor_no).</p>
                </div>

                {{-- Nama Supplier --}}
                <div>
                    <label for="nama_supplier" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Nama Supplier <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="nama_supplier" id="nama_supplier" 
                            value="{{ old('nama_supplier', $supplier_rafaksi->nama_supplier) }}" 
                            placeholder="Contoh: PT. BAHANA GENTA VIKTORY" 
                            class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors uppercase" 
                            required>
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Nama resmi entitas supplier / vendor.</p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('supplier_rafaksi.index') }}" class="px-5 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition uppercase tracking-wider">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm uppercase tracking-wider">
                    <span>💾</span> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

    {{-- Card Data Biltu (C_BPartner_ID) yang Terhubung --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span>📑</span> Data Biltu (C_BPartner_ID) Terhubung
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    Daftar partner Biltu yang memiliki kode vendor <code class="font-mono font-semibold text-gray-800">{{ $supplier_rafaksi->kode_supplier }}</code>.
                </p>
            </div>
            <div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                    {{ $supplier_rafaksi->biltus->count() }} Biltu
                </span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-200 uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-3 text-center w-12">#</th>
                        <th class="px-6 py-3">C_BPartner_ID</th>
                        <th class="px-6 py-3">Search Key</th>
                        <th class="px-6 py-3">Nama Biltu</th>
                        <th class="px-6 py-3 text-center">Tipe PT</th>
                        <th class="px-6 py-3">Kota</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($supplier_rafaksi->biltus as $b)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 text-center text-gray-400 font-mono">{{ $loop->iteration }}</td>
                            <td class="px-6 py-3 font-mono font-bold text-gray-900">{{ $b->C_BPartner_ID }}</td>
                            <td class="px-6 py-3 font-mono">
                                <span class="px-2 py-0.5 rounded font-bold {{ $b->is_mba ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-blue-50 text-blue-800 border border-blue-200' }}">
                                    {{ $b->value }}
                                </span>
                            </td>
                            <td class="px-6 py-3 font-semibold text-gray-800">{{ $b->name }}</td>
                            <td class="px-6 py-3 text-center">
                                @if($b->is_mba)
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200 uppercase">
                                        PT. MBA
                                    </span>
                                @else
                                    <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800 border border-blue-200 uppercase">
                                        Non-MBA
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-600">{{ $b->City ?: '-' }}</td>
                            <td class="px-6 py-3 text-right">
                                <a href="{{ route('biltu.edit', $b->id) }}" class="text-indigo-600 hover:text-indigo-800 font-semibold hover:underline">
                                    Edit &rarr;
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                <p class="text-gray-500 font-medium">Belum ada partner Biltu yang terhubung dengan kode supplier ini.</p>
                                <p class="text-[11px] text-gray-400 mt-1">Data akan otomatis terhubung saat proses import file Biltu dijalankan atau ditambahkan manual di Master Biltu.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-between items-center text-xs">
            <span class="text-gray-500">Ingin menambahkan C_BPartner_ID untuk supplier ini?</span>
            <a href="{{ route('biltu.create') }}" class="font-bold text-indigo-600 hover:text-indigo-800 hover:underline">
                + Tambah Biltu Baru &rarr;
            </a>
        </div>
    </div>
</div>
@endsection
