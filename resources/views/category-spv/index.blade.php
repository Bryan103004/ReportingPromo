@extends('layouts.app')
@section('content')

<div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
    <div class="mb-5">
        <h1 class="text-2xl font-bold">Pengaturan SPV Kategori</h1>
        <p class="mt-1 text-md font-medium">
            Atur siapa yang jadi <b>Prepared By</b> (tanda tangan di dokumen cetak, sekaligus satu-satunya yang
            boleh klik "Sudah Siap / Belum Siap Kirim ke AR" untuk kategori ini) dan <b>Acknowledged By</b> per kategori.
            Admin/superadmin selalu boleh toggle status kesiapan di kategori manapun.
        </p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ session('error') }}</div>
    @endif

    <div class="space-y-6">
        @foreach ($categories as $category)
            @php
                $currentPrepared = $category->signatories->where('role', 'prepared_by')->pluck('user_id')->all();
                $currentAck = $category->signatories->where('role', 'acknowledged_by')->pluck('user_id')->all();
            @endphp
            <form method="POST" action="{{ route('category-spv.update', $category) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                @csrf
                @method('PUT')

                <h2 class="text-base font-bold text-gray-800 mb-4">{{ $category->nama_kategori }}</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Prepared By (SPV Buyer)</label>
                        <input type="text" placeholder="Cari nama..." oninput="filterSpvList(this)" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm outline-none px-3 py-1.5 border mb-2">
                        <div class="spv-list border border-gray-200 rounded-lg max-h-56 overflow-y-auto p-3 space-y-1.5">
                            @foreach ($users as $user)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="prepared_by[]" value="{{ $user->id }}" {{ in_array($user->id, $currentPrepared) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    {{ $user->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Acknowledged By</label>
                        <input type="text" placeholder="Cari nama..." oninput="filterSpvList(this)" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm outline-none px-3 py-1.5 border mb-2">
                        <div class="spv-list border border-gray-200 rounded-lg max-h-56 overflow-y-auto p-3 space-y-1.5">
                            @foreach ($users as $user)
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="acknowledged_by[]" value="{{ $user->id }}" {{ in_array($user->id, $currentAck) ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    {{ $user->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition">
                        Simpan {{ $category->nama_kategori }}
                    </button>
                </div>
            </form>
        @endforeach
    </div>
</div>

<script>
    function filterSpvList(input) {
        const keyword = input.value.trim().toLowerCase();
        const list = input.nextElementSibling;
        if (!list) return;

        list.querySelectorAll('label').forEach(function (label) {
            const name = label.textContent.trim().toLowerCase();
            label.style.display = name.includes(keyword) ? '' : 'none';
        });
    }
</script>

@endsection
