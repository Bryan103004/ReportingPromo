@extends('layouts.app')
@section('content')

    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-5">
            <h1 class="text-2xl font-bold text-gray-900">Edit Master Role</h1>
            <p class="mt-1 text-sm text-gray-600">Ubah nama role dan permission yang dimiliki.</p>
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

        <form action="{{ route('role.update', $role->id) }}" method="POST" class="space-y-5 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
            @csrf
            @method('PUT')

            <div>
                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Role <span class="font-bold text-red-600">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $role->name) }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <div>
                <p class="mb-2 block text-sm font-medium text-gray-700">Permission</p>
                <div class="grid grid-cols-1 gap-2 rounded-md border border-gray-200 bg-gray-50 p-3 sm:grid-cols-2">
                    @foreach ($permissions as $permission)
                        <label for="permission-{{ $permission->id }}" class="flex items-center gap-2 rounded bg-white px-3 py-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                name="permissions[]"
                                id="permission-{{ $permission->id }}"
                                value="{{ $permission->id }}"
                                {{ in_array($permission->id, old('permissions', $role->permissions->pluck('id')->toArray())) ? 'checked' : '' }}
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            >
                            <span>{{ $permission->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Update</button>
                <a href="{{ route('role.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-100">Kembali</a>
            </div>
        </form>
    </div>

@endsection
