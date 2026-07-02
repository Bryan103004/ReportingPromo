@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8">
    
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        {{-- Header Form --}}
        <div class="border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 class="text-lg font-bold text-gray-800">Formulir Tambah Supplier Rafaksi</h2>
            <p class="text-sm text-gray-500 mt-1">Silakan lengkapi data supplier</p>
        </div>

        <form action="{{route('supplier_rafaksi.store')}}" method="POST" class="p-6">
            @csrf
            
            {{-- Grid 2 Kolom --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                
                {{-- Kode Supplier --}}
                <div>
                    <label for="kode_supplier" class="block text-sm font-semibold text-gray-700 mb-1.5">Kode Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_supplier" id="kode_supplier" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Masukkan kode..." required>
                </div>

                {{-- Nama Supplier --}}
                <div>
                    <label for="nama_supplier" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Supplier <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_supplier" id="nama_supplier" class="w-full rounded-md border border-gray-300 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition-colors" placeholder="Masukkan nama..." required>
                </div>     


                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-5 border-t border-gray-100">
                    {{-- Sesuaikan route cancel dengan route index milikmu --}}
                    <a href="{{ url()->previous() }}" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Batal</a>
                    
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                        Simpan Data
                    </button>
                </div>
        </form> 
    </div>
</div>
@endsection