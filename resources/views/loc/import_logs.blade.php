@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('loc.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 uppercase tracking-wider flex items-center gap-1">
                    &larr; Kembali ke Rekap Loc
                </a>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-2">
                <span>📋</span> Riwayat Log Penarikan & Import LOC
            </h1>
            <p class="text-sm text-gray-600 mt-1">
                Rekam jejak setiap proses penarikan otomatis file CSV LOC dari folder inbound beserta rincian status keberhasilannya.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @can('email_tambahan')
            <a href="{{ route('notification-recipients.index') }}" class="inline-flex items-center px-4 py-2 text-xs font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition shadow-sm uppercase tracking-wider">
                <span class="mr-1.5">📧</span> Setting Penerima Email
            </a>
            @endcan
        </div>
    </div>

    {{-- Info Card --}}
    <div style="background-color: white" class="mb-6 rounded-xl border border-blue-100 bg-blue-50/70 p-4 text-xs text-blue-800 flex items-start gap-3">
        <span class="text-lg">💡</span>
        <div>
            <span class="font-bold">Ketentuan Proses Import LOC:</span>
            Sistem menggunakan pendekatan <em>Best-Effort & Skip</em>:
            <ul class="list-disc list-inside mt-1 space-y-0.5 text-blue-900">
                <li><strong>Data Baru</strong>: Baris valid yang nomor RAF-nya belum ada di database akan langsung disimpan dan diasosiasikan dengan toko terkait.</li>
                <li><strong>Data Diupdate (Nimpa)</strong>: Jika nomor RAF sudah ada di database namun nilai <em>Nominal, PPN, atau Amount Incl PPN</em> di file CSV berbeda/berubah, sistem otomatis <strong>menimpa data lama</strong> dengan data terbaru tanpa mengubah status invoice, status email, approval, maupun dokumen lampiran.</li>
                <li><strong>Sudah Ada (Sama)</strong>: Jika nomor RAF sudah ada dan nilai nominal/PPN-nya sudah sama persis, data dilewati (skip).</li>
                <li><strong>Gagal / Error</strong>: Baris CSV yang kolom wajibnya (No RAF/Supplier) kosong atau alias tokonya tidak dikenali akan di-skip sebagai error dan dikirimkan email alert ke daftar <strong>Email Tambahan</strong>.</li>
            </ul>
        </div>
    </div>

    {{-- Tabel Log --}}
    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4 text-center w-12">#</th>
                        <th class="px-6 py-4">Waktu Eksekusi</th>
                        <th class="px-6 py-4">Nama File CSV</th>
                        <th class="px-6 py-4 text-center">Total Baris</th>
                        <th class="px-6 py-4 text-center">Data Baru</th>
                        <th class="px-6 py-4 text-center">Data Diupdate</th>
                        <th class="px-6 py-4 text-center">Sudah Ada (Sama)</th>
                        <th class="px-6 py-4 text-center">Gagal / Error</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Rincian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-center font-medium text-gray-400 text-xs">
                                {{ $loop->iteration + ($logs->currentPage() - 1) * $logs->perPage() }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-800 text-xs">
                                    {{ $log->started_at ? $log->started_at->translatedFormat('d M Y, H:i:s') : '-' }}
                                </div>
                                @if($log->finished_at && $log->started_at)
                                    <div class="text-[11px] text-gray-400 font-mono">
                                        Durasi: {{ $log->started_at->diffInSeconds($log->finished_at) }} detik
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-semibold text-gray-900">
                                {{ $log->filename }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-blue-600 text-xs">
                                {{ number_format($log->total_rows) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-green-600 text-xs">
                                {{ number_format($log->success_count) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-indigo-600 text-xs">
                                {{ number_format($log->updated_count ?? 0) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold text-gray-500 text-xs">
                                {{ number_format($log->skipped_count) }}
                            </td>
                            <td class="px-6 py-4 text-center font-bold {{ $log->failed_count > 0 ? 'text-red-600' : 'text-gray-400' }} text-xs">
                                {{ number_format($log->failed_count) }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $log->status_badge_class }}">
                                    {{ $log->status_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($log->failed_count > 0 && !empty($log->failed_rows))
                                    <button type="button" 
                                            onclick='openDetailModal(@json($log->filename), @json($log->failed_rows))' 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 border border-red-200 rounded-lg text-xs font-bold transition shadow-xs">
                                        <span>🔍</span> Lihat Error ({{ $log->failed_count }})
                                    </button>
                                @elseif($log->error_message)
                                    <span class="text-xs text-red-500 font-medium italic" title="{{ $log->error_message }}">
                                        {{ Str::limit($log->error_message, 25) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-400 text-sm">
                                <p class="font-medium text-gray-500">Belum ada riwayat penarikan import LOC yang tercatat.</p>
                                <p class="text-xs text-gray-400 mt-1">Setiap file CSV yang diproses oleh scheduler akan otomatis muncul di sini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Modal Rincian Baris Gagal --}}
<div id="failedDetailModal" class="fixed inset-0 z-50 hidden bg-gray-900 bg-opacity-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden flex flex-col max-h-[85vh]">
        {{-- Header Modal --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div>
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="text-red-600">⚠️</span> Rincian Baris Gagal / Dilewati
                    <span id="modalFilename" class="text-xs px-2.5 py-0.5 rounded-full font-mono bg-gray-200 text-gray-800 font-semibold"></span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Daftar baris data di file CSV yang dilewati karena tidak memenuhi validasi.</p>
            </div>
            <button type="button" onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-lg hover:bg-gray-100 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 overflow-y-auto flex-1">
            <table class="w-full text-left text-xs text-gray-600 border border-gray-200 rounded-lg overflow-hidden">
                <thead class="bg-gray-50 border-b border-gray-200 uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">#</th>
                        <th class="px-4 py-3 w-24">Baris CSV</th>
                        <th class="px-4 py-3 w-44">No. RAF</th>
                        <th class="px-4 py-3">Supplier</th>
                        <th class="px-4 py-3 w-28">Toko (Alias)</th>
                        <th class="px-4 py-3 text-red-600">Penyebab Gagal</th>
                    </tr>
                </thead>
                <tbody id="failedRowsTableBody" class="divide-y divide-gray-100 font-medium">
                    {{-- Row items inserted by JavaScript --}}
                </tbody>
            </table>
        </div>

        {{-- Footer Modal --}}
        <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-end">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-semibold rounded-lg transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
    function openDetailModal(filename, failedRows) {
        document.getElementById('modalFilename').textContent = filename;
        const tbody = document.getElementById('failedRowsTableBody');
        tbody.innerHTML = '';

        if (!failedRows || failedRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Tidak ada rincian data gagal.</td></tr>';
        } else {
            failedRows.forEach((row, index) => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-gray-100 hover:bg-gray-50';
                tr.innerHTML = `
                    <td class="px-4 py-2.5 text-center text-gray-400 font-mono">${index + 1}</td>
                    <td class="px-4 py-2.5 font-bold text-gray-700">Baris ${row.line || '-'}</td>
                    <td class="px-4 py-2.5 font-mono text-[11px] font-bold text-gray-900">${row.no_raf || '-'}</td>
                    <td class="px-4 py-2.5 text-gray-800">${row.supplier || '-'}</td>
                    <td class="px-4 py-2.5 font-mono text-[11px] text-gray-700">${row.toko_alias || '-'}</td>
                    <td class="px-4 py-2.5 font-semibold text-red-600">${row.reason || '-'}</td>
                `;
                tbody.appendChild(tr);
            });
        }

        document.getElementById('failedDetailModal').classList.remove('hidden');
    }

    function closeDetailModal() {
        document.getElementById('failedDetailModal').classList.add('hidden');
    }
</script>
@endsection

