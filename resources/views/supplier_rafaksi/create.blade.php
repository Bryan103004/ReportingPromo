@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    {{-- Tombol Kembali --}}
    <div class="mb-4">
        <a href="{{ route('supplier_rafaksi.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider flex items-center gap-1">
            &larr; Kembali ke Data Master Supplier
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <span>➕</span> Formulir Tambah Supplier Rafaksi
            </h2>
            <p class="text-xs text-gray-500 mt-1">Silakan lengkapi data kode dan nama supplier rafaksi.</p>
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

        <form action="{{ route('supplier_rafaksi.store') }}" method="POST" class="p-6">
            @csrf
            
            {{-- Grid 2 Kolom --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- Kode Supplier --}}
                <div>
                    <label for="kode_supplier" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Kode Supplier <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="kode_supplier" id="kode_supplier" value="{{ old('kode_supplier') }}" 
                        placeholder="Contoh: 00016" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm font-mono text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors" required>
                    <p class="text-[11px] text-gray-400 mt-1">Kode unik supplier (sesuai vendor no di sistem ERP).</p>
                </div>

                {{-- Nama Supplier --}}
                <div>
                    <label for="nama_supplier" class="block text-xs font-bold uppercase text-gray-700 mb-1.5">
                        Nama Supplier <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="nama_supplier" id="nama_supplier" value="{{ old('nama_supplier') }}" 
                        placeholder="Contoh: PT. BAHANA GENTA VIKTORY" 
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-colors uppercase" required>
                    <p class="text-[11px] text-gray-400 mt-1">Nama resmi entitas supplier / vendor.</p>
                </div>     
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 pt-5 border-t border-gray-100">
                <a href="{{ route('supplier_rafaksi.index') }}" class="px-5 py-2 text-xs font-bold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition uppercase tracking-wider">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm uppercase tracking-wider">
                    <span>💾</span> Simpan Data
                </button>
            </div>
        </form> 
    </div>
</div>
@endsection
