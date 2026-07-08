@extends('layouts.app')
@section('content')
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm border border-gray-100 sm:rounded-xl p-8">
                
                <div class="mb-8 border-b pb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Edit Toko</h2>
                    <p class="text-sm text-gray-500 mt-1">Perbarui informasi untuk toko <strong>{{ $toko->nama_toko }}</strong>.</p>
                </div>

                @if ($errors->any())
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.78 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.22 1.22a.75.75 0 101.06 1.06L10 11.06l1.22 1.22a.75.75 0 101.06-1.06L11.06 10l1.22-1.22a.75.75 0 00-1.06-1.06L10 8.94 8.78 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('toko.update', $toko->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Region <span class="text-red-500">*</span></label>
                            <select name="region_id" id="region_id" required class="text-center py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150">
                                <option value="">-- Pilih Region --</option>
                                @foreach ($regions as $region)
                                <option value="{{ $region->id }}" {{ old('region_id', $toko->region_id) == $region->id ? 'selected' : '' }}>
                                    {{ $region->nama_region }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko <span class="text-red-500">*</span></label>
                            <input type="text" name="nama_toko" value="{{ old('nama_toko', $toko->nama_toko) }}" required class="text-center py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode Toko</label>
                            <input type="text" name="kode_toko" value="{{ old('kode_toko', $toko->kode_toko) }}" class="text-center py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">NPWP</label>
                            <input type="text" name="npwp" value="{{ old('npwp', $toko->npwp) }}" class="text-center py-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition duration-150">
                        </div>
                    </div>

                    <div class="flex items-center justify-end space-x-4 pt-5 border-t border-gray-100">
                        <a href="{{ route('toko.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition">Batal & Kembali</a>
                        <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2.5 px-5 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-150">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection