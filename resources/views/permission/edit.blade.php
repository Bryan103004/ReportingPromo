@extends('layouts.app')
@section('content')

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
    <h1 class="mb-5 text-2xl font-bold">Edit Master Permission</h1>

    @if ($errors->any())
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('permission.update', $permission->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium">Nama Permission</label>
            <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
        </div>

        <div class="flex items-center gap-2 pt-1">
            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Update</button>
            <a href="{{ route('permission.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-100">Kembali</a>
        </div>
    </form>
    </div>

@endsection
