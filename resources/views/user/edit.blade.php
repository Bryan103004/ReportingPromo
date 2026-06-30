@extends('layouts.app')
@section('content')

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="mb-5 text-2xl font-bold">Edit Master User</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('user.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium">Nama User <span class="font-bold text-red-600">*</span></label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="username" class="mb-1.5 block text-sm font-medium">Username <span class="font-bold text-red-600">*</span></label>
            <input type="text" name="username" id="username" value="{{ old('username', $user->username) }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium">Email <span class="font-bold text-red-600">*</span></label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium">Password <span class="font-bold text-red-600">*</span></label>
            <input type="password" name="password" id="password" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Kosongkan jika tidak ingin mengubah password">
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium">Konfirmasi Password <span class="font-bold text-red-600">*</span></label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Isi jika mengubah password">
        </div>

        {{-- @foreach ($user->roles as $role)
            <div>
                <label for="roles[]" class="mb-1.5 block text-sm font-medium">Role</label>
                <input type="checkbox" name="roles[]" id="role-{{ $role->id }}" value="{{ $role->id }}" {{ $user->roles->contains($role) ? 'checked' : '' }} class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <label for="role-{{ $role->id }}" class="ml-2 text-sm font-medium">{{ $role->name }}</label>
            </div>
        @endforeach --}}

        <div>
            <label for="roles" class="mb-1.5 block text-sm font-medium">Role <span class="font-bold text-red-600">*</span></label>
            <select name="roles[]" id="roles" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">-- Pilih Role --</option>
                @foreach ($roles as $role)
                 <option value="{{ $role->id }}" {{ collect(old('roles', $user->roles->pluck('id')->toArray()))->contains($role->id) ? 'selected' : '' }}>
                    {{ $role->name }}
                </option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Update</button>
            <a href="{{ route('user.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-100">Kembali</a>
        </div>
    </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

        });
    </script>

@endsection
