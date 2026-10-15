@extends('layouts.app')

@section('content')

@if(session('error'))
    <div style="color: white; background-color: #ff011a8c; padding: 10px; margin-bottom: 15px; border-radius: 5px; text-align: center;">
        {{ session('error') }}
    </div>
@endif

<div class="mx-auto max-w-xl px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden p-6">
        <h2 class="text-lg font-bold text-gray-800 mb-2">Pilih Toko untuk Dicetak</h2>
        <p class="text-sm text-gray-500 mb-6">Centang toko-toko yang ingin ditampilkan pada laporan cetak dokumen <strong>{{ $document->no_raf }}</strong>.</p>

        <!-- Form mengarah ke method printUniversal dengan metode POST atau GET -->
        <form action="{{ route('document.print', [$type, $document->id]) }}" method="GET" target="_blank">
            
            <div class="mb-4 flex items-center justify-between">
                <span class="text-sm font-medium text-gray-700">Daftar Toko Terkait:</span>
                <button type="button" id="selectAll" class="text-xs text-blue-600 hover:underline">Pilih Semua</button>
            </div>

            <div class="max-h-60 overflow-y-auto border border-gray-200 rounded-md p-4 space-y-2 bg-gray-50 mb-6">
                @foreach($document->tokos as $toko)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="toko_ids[]" value="{{ $toko->id }}" checked 
                               class="toko-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-800">{{ $toko->kode_excel }}</span>
                    </label>
                @endforeach
            </div>

            @if ($document->status_email === 'tidak_aktif' && !empty($autoPrepared) && !empty($autoAck))
                <div class="p-3 mb-4 rounded-lg bg-green-50 border border-green-200 text-xs text-green-800">
                    <p class="font-bold mb-1">Status Divisi Buyer: Nonaktif (Sudah Siap)</p>
                    <p>TTD SPV Kategori otomatis terisi:</p>
                    <ul class="list-disc list-inside mt-1">
                        <li>Prepared by: <strong>{{ $autoPrepared->name }}</strong></li>
                        <li>Acknowledged by: <strong>{{ $autoAck->name }}</strong></li>
                    </ul>
                </div>
                <input type="hidden" name="prepared_by" value="{{ $autoPrepared->id }}">
                <input type="hidden" name="acknowledged_by" value="{{ $autoAck->id }}">
            @else
                <div class="mb-4">
                    <label for="prepared_by" class="block text-sm font-medium text-gray-700 mb-1">Prepared by,</label>
                    <select name="prepared_by" id="prepared_by" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="{{ auth()->user()->id }}" selected>{{ auth()->user()->name }}</option>
                        @foreach ($users as $user)
                            @if ($user->id !== auth()->id())
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="acknowledged_by" class="block text-sm font-medium text-gray-700 mb-1">Acknowledged by,</label>
                    <select name="acknowledged_by" id="acknowledged_by" class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" {{ (isset($autoAck) && $autoAck && $autoAck->id === $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif


            <div class="flex justify-end gap-3">
                <a href="{{ url()->previous(true) }}" class="px-4 py-2 text-sm font-semibold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</a>
                @if($document->documents->isNotEmpty())
                    <a href="{{ route('document.print-attachments', [$type, $document->id]) }}" target="_blank" class="px-5 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Cetak Lampiran
                    </a>
                @endif
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                    Lanjut Cetak
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Script kecil untuk tombol Pilih Semua
    document.getElementById('selectAll').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.toko-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        this.textContent = allChecked ? 'Pilih Semua' : 'Batalkan Semua';
    });

</script>
@endsection