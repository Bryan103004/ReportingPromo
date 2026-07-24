@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <!-- Header Page dengan Tombol Kembali -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pengerjaan Periode Rekap</h1>
            <p class="text-sm text-gray-500 mt-1">Perbarui informasi rentang waktu pengerjaan untuk rekap data JSM.</p>
        </div>
        <a href="{{ url()->previous() }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">
            ✕ <span class="underline">Batal</span>
        </a>
    </div>

    <!-- Main Card Form -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="{{ route('loc.renew', $loc->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Input Group -->
            <div class="space-y-2">
                <label for="periode_bulan" class="block text-sm font-semibold text-gray-800">
                    Periode Rekap Baru <span class="text-red-500" title="Wajib diisi">*</span>
                </label>
                
                <div class="relative rounded-lg shadow-sm">
                    <input 
                        type="date" 
                        name="periode_bulan" 
                        id="periode_bulan" 
                        value="{{ old('periode_bulan', isset($loc->periode_bulan) ? \Carbon\Carbon::parse($loc->periode_bulan)->format('Y-m-d') : '') }}"
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 focus:border-blue-500 focus:bg-white focus:ring-4 focus:ring-blue-100 outline-none transition-all duration-200" 
                        required
                    >
                </div>
                
                @error('periode_bulan')
                    <p class="text-xs font-medium text-red-600 mt-1">⚠️ {{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-400">Pilih tanggal awal penanda bulan untuk sistem pelaporan.</p>
            </div>

            <!-- Action Buttons Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:ring-4 focus:ring-blue-100 active:bg-blue-800 transition-all duration-150 cursor-pointer"
                >
                    ↻ Perbarui Periode
                </button>
            </div>
        </form>
    </div>
</div>
@endsection