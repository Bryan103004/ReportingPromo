@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('loc.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Kembali ke Index">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Detail LOC</h1>
            </div>
            <p class="text-md text-gray-500">{{ $loc->no_raf }}</p>
        </div>
        <a href="{{ route('loc.edit', $loc->id) }}" class="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
            Edit
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <dl class="divide-y divide-gray-100">
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Kode Supplier</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ $loc->supplier_code }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Nama Supplier</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ $loc->supplier_name }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Category</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ $loc->categories->nama_kategori ?? '-' }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Periode</dt>
                <dd class="col-span-2 text-sm text-gray-900">
                    {{ $loc->periode_awal ? \Carbon\Carbon::parse($loc->periode_awal)->format('d M Y') : '-' }}
                    <span class="text-gray-400 mx-1">-</span>
                    {{ $loc->periode_akhir ? \Carbon\Carbon::parse($loc->periode_akhir)->format('d M Y') : '-' }}
                </dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Toko</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ $loc->daftar_toko_formatted }}</dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Nominal</dt>
                <dd class="col-span-2 text-sm font-bold text-green-600">Rp {{ number_format($loc->nominal, 0, ',', '.') }}</dd>
            </div>
            @if($loc->remarks)
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Remarks</dt>
                <dd class="col-span-2 text-sm text-gray-900">{{ $loc->remarks }}</dd>
            </div>
            @endif
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Dokumen</dt>
                <dd class="col-span-2 text-sm">
                    @if($loc->document_path)
                        <a href="{{ route('loc.download', $loc->id) }}" class="text-blue-600 hover:underline font-medium">
                            {{ $loc->document_original_name ?? 'Download dokumen' }}
                        </a>
                    @else
                        <span class="text-gray-400">Belum ada dokumen diupload.</span>
                    @endif
                </dd>
            </div>
            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Status Approval</dt>
                <dd class="col-span-2 text-sm">
                    @if($loc->approved_at)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Approved oleh {{ $loc->approvedBy->name ?? '-' }} pada {{ \Carbon\Carbon::parse($loc->approved_at)->format('d M Y H:i') }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                            Belum di-approve
                        </span>
                        @if($loc->document_path)
                            <form action="{{ route('loc.approve', $loc->id) }}" method="POST" class="inline-block ml-2">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-green-700 hover:underline">Approve &amp; Tampilkan Tanda Tangan</button>
                            </form>
                        @endif
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</div>
@endsection