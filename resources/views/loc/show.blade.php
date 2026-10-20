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
                    @if($loc->documents->isNotEmpty() || $loc->document_path)
                        <ul class="space-y-2">
                            @foreach($loc->documents as $doc)
                                <li>
                                    <a href="{{ route('loc.documents.download', $doc->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium inline-flex items-center gap-1.5" target="_blank">
                                        <span class="text-rose-500 font-bold text-[10px] uppercase px-1 py-0.5 rounded bg-rose-50 border border-rose-200">PDF</span>
                                        {{ $doc->filename }}
                                    </a>
                                </li>
                            @endforeach
                            @if($loc->document_path)
                                <li>
                                    <a href="{{ route('loc.download', $loc->id) }}" class="text-blue-600 hover:text-blue-800 hover:underline font-medium inline-flex items-center gap-1.5" target="_blank">
                                        <span class="text-amber-600 font-bold text-[10px] uppercase px-1 py-0.5 rounded bg-amber-50 border border-amber-200">UTAMA</span>
                                        {{ $loc->document_original_name ?? basename($loc->document_path) }}
                                    </a>
                                </li>
                            @endif
                        </ul>
                    @else
                        <span class="text-gray-400">Belum ada dokumen diupload.</span>
                    @endif
                </dd>
            </div>

            <div class="grid grid-cols-3 gap-4 px-6 py-4">
                <dt class="text-sm font-semibold text-gray-500">Status Invoice</dt>
                <dd class="col-span-2 text-sm flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $loc->invoice_status ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-gray-100 text-gray-700 border border-gray-200' }}">
                        {{ $loc->invoice_status ? 'DONE' : 'NOT DONE' }}
                    </span>
                    <button type="button" onclick="showInvoiceLogs('loc', {{ $loc->id }}, '{{ addslashes($loc->no_raf ?? '-') }}')" class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-semibold transition shadow-xs" title="Lihat Riwayat Log Invoice">
                        <span>📋</span> Lihat Riwayat Log Invoice
                    </button>
                </dd>
            </div>

            @if($loc->invoice_done_by || $loc->invoice_cancel_by)
            <div class="grid grid-cols-3 gap-4 px-6 py-4 bg-gray-50/60">
                <dt class="text-sm font-semibold text-gray-500">Info Terakhir Invoice</dt>
                <dd class="col-span-2 text-xs space-y-1.5 text-gray-700">
                    @if($loc->invoice_done_by)
                        <div class="flex items-center gap-2">
                            <span class="px-1.5 py-0.5 rounded bg-green-100 text-green-800 font-bold text-[10px]">LATEST DONE</span>
                            <span class="font-semibold text-gray-800">{{ $loc->invoice_done_by }}</span>
                            <span class="text-gray-400">pada</span>
                            <span class="text-gray-600 font-mono">{{ $loc->invoice_done_at ?? '-' }}</span>
                            @if($loc->invoice_done_count)
                                <span class="text-[10px] text-gray-400 font-semibold">({{ $loc->invoice_done_count }}x Done)</span>
                            @endif
                        </div>
                    @endif
                    @if($loc->invoice_cancel_by)
                        <div class="flex items-center gap-2">
                            <span class="px-1.5 py-0.5 rounded bg-red-100 text-red-800 font-bold text-[10px]">LATEST CANCEL</span>
                            <span class="font-semibold text-gray-800">{{ $loc->invoice_cancel_by }}</span>
                            <span class="text-gray-400">pada</span>
                            <span class="text-gray-600 font-mono">{{ $loc->invoice_cancel_at ?? '-' }}</span>
                            @if($loc->invoice_cancel_count)
                                <span class="text-[10px] text-gray-400 font-semibold">({{ $loc->invoice_cancel_count }}x Cancel)</span>
                            @endif
                        </div>
                    @endif
                </dd>
            </div>
            @endif
            <!-- <div class="grid grid-cols-3 gap-4 px-6 py-4">
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
            </div> -->
        </dl>
    </div>
</div>
@endsection