@extends('layouts.app')
@section('content')

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Buat Master User</h1>
            <p class="mt-1 text-sm text-gray-600">Tambah user baru beserta role, dan tanda tangan.</p>
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

        <form action="{{ route('user.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            @csrf

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Name <span class="font-bold text-red-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="username" class="mb-1.5 block text-sm font-medium text-gray-700">Username <span class="font-bold text-red-600">*</span></label>
                <input type="text" name="username" id="username" value="{{ old('username') }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email <span class="font-bold text-red-600">*</span></label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Password <span class="font-bold text-red-600">*</span></label>
                <input type="password" name="password" id="password" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">Konfirmasi Password <span class="font-bold text-red-600">*</span></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <label for="roles" class="mb-1.5 block text-sm font-medium text-gray-700">Role <span class="font-bold text-red-600">*</span></label>
                <select name="roles[]" id="roles" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    <option value="">Pilih Role</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ collect(old('roles', []))->contains($role->id) ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Simpan</button>
                <a href="{{ route('user.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-100">Kembali</a>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
        });
    </script>

@endsection
