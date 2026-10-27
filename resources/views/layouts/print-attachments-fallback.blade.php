@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-6">
        <div class="flex items-start gap-3 mb-4">
            <div class="p-2 bg-amber-100 rounded-lg text-amber-600 text-xl shrink-0">
                ⚠️
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-800">Lampiran Dokumen {{ $document->no_raf }}</h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $errorMessage ?? 'File lampiran tidak dapat digabungkan otomatis karena format kompresi modern (PDF 1.5+). Anda dapat langsung membuka atau mencetak masing-masing lampiran di bawah ini:' }}
                </p>
            </div>
        </div>

        <div class="divide-y divide-gray-200 border rounded-lg overflow-hidden bg-gray-50/50 my-6">
            @foreach($attachments as $index => $attachment)
                <div class="flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition-colors">
                    <div class="flex items-center gap-3 truncate pr-4">
                        <span class="text-rose-500 font-bold text-xs uppercase px-2 py-1 rounded bg-rose-50 border border-rose-200 shrink-0">PDF</span>
                        <div class="truncate">
                            <p class="text-sm font-semibold text-gray-800 truncate" title="{{ $attachment->filename }}">
                                {{ $attachment->filename ?? ('Lampiran ' . ($index + 1)) }}
                            </p>
                            <p class="text-xs text-gray-400">
                                Lampiran ke-{{ $index + 1 }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('document.attachments.view', [$type, $attachment->id]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-700 bg-blue-50 border border-blue-200 rounded-md hover:bg-blue-100 transition-colors">
                            <span>👁️</span> Buka / Cetak
                        </a>
                        @php
                            $downloadRoute = route($type . '.documents.download', $attachment->id);
                        @endphp
                        <a href="{{ $downloadRoute }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 bg-gray-100 border border-gray-200 rounded-md hover:bg-gray-200 transition-colors">
                            <span>⬇️</span> Unduh
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex justify-end gap-3">
            <button type="button" onclick="if(window.history.length > 1) { window.history.back(); } else { window.close(); }" class="px-5 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                Tutup / Kembali
            </button>
        </div>
    </div>
</div>
@endsection

