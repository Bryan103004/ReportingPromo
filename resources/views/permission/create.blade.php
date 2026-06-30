@extends('layouts.app')
@section('content')

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Buat Permission</h1>
            <p class="mt-1 text-sm text-gray-600">Tambahkan izin akses baru untuk fitur aplikasi.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('permission.store') }}" method="POST" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Permission <span class="font-bold text-red-600">*</span></label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="Contoh: create_tanda_terima"
                >
                <p class="mt-1 text-xs text-gray-500">Gunakan format konsisten, misalnya: `view_...`, `create_...`, `edit_...`, `delete_...`.</p>
            </div>

            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Guard</label>
                <input
                    type="text"
                    value="web"
                    disabled
                    class="w-full rounded-md border-gray-200 bg-gray-100 text-sm text-gray-600"
                >
            </div>

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                <a href="{{ route('permission.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-100">Kembali</a>
            </div>
        </form>
    </div>

@endsection
