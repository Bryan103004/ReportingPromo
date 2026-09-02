@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    
    {{-- Bagian Header & Tombol Kembali --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('rafaksi.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Kembali ke Index">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Detail Rafaksi</h1>
            </div>
            <p class="text-md text-black font-medium">Daftar seluruh transaksi rafaksi pada periode <span class="font-bold text-gray-700">{{ $periodeTitle }}</span>.</p>
        </div>
    </div>
    <div class="flex ms-auto m-4 gap-2">
        <!-- <a href="{{ route('rafaksi.export', ['year' => $year, 'month' => $month]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
            Export Rekap CSV
        </a> -->
        <button type="button" onclick="openExportModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Export Rekap XLS
        </button>
        <a href="{{ route('rafaksi.print', array_merge(['year' => $year, 'month' => $month], request()->only(['category_id', 'toko_id']))) }}" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Print
        </a>
    </div>

    {{-- Modal Export --}}
    <div id="exportModal" class="fixed inset-0 z-50 hidden bg-gray-100 bg-opacity-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6">
            <h3 class="text-lg font-bold mb-4">Export Rekap Detail</h3>
            <form action="{{ route('rafaksi.export.excel') }}" method="GET">
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">

                <label class="block text-xs font-semibold text-gray-500 mb-1">Category (kosongkan untuk semua)</label>
                <select name="category_id" class="w-full border-gray-300 rounded-md mb-4">
                    <option value="">Semua Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->nama_kategori }}</option>
                    @endforeach
                </select>

                <label class="block text-xs font-semibold text-gray-500 mb-1">Store (kosongkan untuk semua)</label>
                <select name="toko_id" class="w-full border-gray-300 rounded-md mb-4">
                    <option value="">Semua Store</option>
                    @foreach ($tokos as $toko)
                        <option value="{{ $toko->id }}" {{ request('toko_id') == $toko->id ? 'selected' : '' }}>{{ $toko->nama_toko }}</option>
                    @endforeach
                </select>

                <div class="flex justify-end gap-2">
                    <button type="button" onclick="closeExportModal()" class="px-4 py-2 text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Download</button>
                </div>
            </form>
        </div>
    </div>

    {{--  KOMPONEN FILTER --}}
    <x-filter-bar :suppliers="$suppliers" :tokos="$tokos" :categories="$categories" />

    <x-search-bar 
        placeholder="Masukkan user atau aksi..." 
        tableId="rafaksiDetail" 
    />

    <x-per-page/>

    {{-- Tabel Container (Card) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="rafaksiDetail" class="w-full text-left text-sm text-gray-600">
                
                {{-- Head Tabel --}}
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="px-6 py-4">Supplier Code</th>
                        <th class="px-6 py-4">Supplier Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Periode Promo</th>
                        <th class="px-6 py-4">No. RAF</th>
                        <!-- <th class="px-6 py-4">Region</th> -->
                        <th class="px-6 py-4">Store</th>
                        <th class="px-6 py-4 text-right">Remarks</th>
                        @can('see_nominal')
                        <th class="px-6 py-4 text-right">Nominal</th>
                        @endcan
                        <th class="px-6 py-4 text-right">Aksi</th>

                        {{-- <th class="px-6 py-4 text-center">Aksi</th> --}} {{-- Buka komen ini jika nanti butuh tombol Edit/Delete --}}
                    </tr>
                </thead>
                
                {{-- Body Tabel --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($rafaksis as $rafaksi)
                        <tr class="hover:bg-gray-50 transition-colors" data-nominal="{{ $rafaksi->nominal }}">
                            {{-- Nomor Urut --}}
                            <td class="px-6 py-4 text-center font-medium text-gray-500">
                                {{ $loop->iteration }}
                            </td>
            
                            
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $rafaksi->supplier_code }}</div>
                            </td>

                            {{-- Supplier (Kode & Nama) --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $rafaksi->supplier_name }}</div>
                            </td>
                
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $rafaksi->categories->nama_kategori }}</div>
                            </td>
                            
                            {{-- Periode (Awal - Akhir) --}}
                            <td class="px-6 py-4">
                                <div class="text-xs font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($rafaksi->periode_awal)->format('d M Y') }} 
                                    <span class="text-gray-400 mx-1">-</span> 
                                    {{ \Carbon\Carbon::parse($rafaksi->periode_akhir)->format('d M Y') }}
                                </div>
                            </td>
                        
                            {{-- No RAF --}}
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{ $rafaksi->no_raf ?? '-' }}
                            </td>

                            {{-- Region --}}
                            <!-- <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 font-semibold px-2 py-1 rounded text-xs">
                                    {{ $rafaksi->store }}
                                </span>
                            </td> -->

                            {{-- Store --}}
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 font-semibold px-2 py-1 rounded text-xs">
                                    {{ optional($rafaksi->tokos->first())->nama_toko ?? '-' }}
                                </span>
                            </td>
                            
                            {{-- Remarks --}}
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 font-semibold px-2 py-1 rounded text-xs">
                                    {{ $rafaksi->remarks }}
                                </span>
                            </td>
                            @can('see_nominal')
                            {{-- Nominal --}}
                            <td class="px-6 py-4 text-right font-bold text-green-600">
                                Rp {{ number_format($rafaksi->nominal, 0, ',', '.') }}
                            </td>
                            @endcan

                            {{-- Aksi (Opsional) --}}
                            <td class="px-6 py-4 text-center">
                                <div class="flex flex-row justify-center items-center gap-x-2">
                                    {{-- Tombol Edit --}}
                                    <a href="{{ route('rafaksi.edit', ['rafaksi' => $rafaksi->id, 'page' => request('page')]) }}" title="Edit Data">
                                        ✎
                                    </a>

                                    {{--
                                    @php
                                        $bulanReminder = $rafaksi->bulanReminder ?? 0;
                                        $tglMulaiReminder = \Carbon\Carbon::parse($rafaksi->periode_akhir)->subMonths($bulanReminder);
                                        $tglAkhirReminder = \Carbon\Carbon::parse($rafaksi->periode_akhir)->addMonth();
                                    @endphp

                                    @if (
                                        now()->greaterThanOrEqualTo($tglMulaiReminder) && 
                                        now()->lessThanOrEqualTo($tglAkhirReminder) && 
                                        !is_null($rafaksi->periode_akhir)
                                    )
                                        <!-- Tombol Renew Anda di sini -->
                                        <a href="{{ route('rafaksi.renew.index', ['id' => $rafaksi->id]) }}" title="Renew Data">🆕</a>
                                    @endif
                                    --}}

                                    @php
                                        // Ubah periode_akhir menjadi instance Carbon agar bisa dibandingkan
                                        $periodeAkhir = \Carbon\Carbon::parse($rafaksi->periode_akhir);
                                    @endphp

                                    {{-- Muncul jika periode_akhir sudah kurang dari hari ini (sudah lewat/kadaluarsa) --}}
                                    @if (!is_null($rafaksi->periode_akhir) && now()->greaterThan($periodeAkhir))
                                        <!-- Tombol Renew hanya muncul jika periode_akhir < sekarang -->
                                        <a href="{{ route('rafaksi.renew.index', ['id' => $rafaksi->id]) }}" title="Renew Data">🆕</a>
                                    @endif

                    
                                    {{-- Tombol Hapus --}}
                                    <form action="{{ route('rafaksi.destroy', $rafaksi->id) }}" method="POST" class="inline">
                                        @csrf 
                                        @method('DELETE')
                                        <button type="submit" class="cursor-pointer text-red-500 hover:text-red-700 transition-colors duration-200" onclick="return confirm('Yakin hapus data ini?')" title="Hapus Data">
                                            🗑
                                        </button>
                                    </form>
                                </div>
                            </td> 
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <span class="font-medium text-gray-500">Tidak ada data transaksi di bulan ini.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                @if($rafaksis->count() > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-right font-bold text-gray-800 uppercase tracking-wider text-xs">
                            Grand Total:
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-blue-700 text-base">
                            Rp <span id="rafaksiGrandTotal">{{ number_format($rafaksis->sum('nominal'), 0, ',', '.') }}</span>
                        </td>
                        {{-- Tambahkan 1 <td> kosong di bawah ini JIKA kamu mengaktifkan kolom Aksi di atas --}}
                        {{-- <td></td> --}}
                    </tr>
                </tfoot>
                @endif
                
            </table>
        </div>
    </div>

    <div class="my-2">
        {{ $rafaksis->links() }}
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('.dynamic-search-bar[data-target="rafaksiDetail"]');
        const totalEl = document.getElementById('rafaksiGrandTotal');
        if (!searchInput || !totalEl) return;

        function recalcGrandTotal() {
            let total = 0;
            document.querySelectorAll('#rafaksiDetail tbody tr').forEach(function (row) {
                if (row.style.display === 'none') return;
                total += parseFloat(row.dataset.nominal || 0);
            });
            totalEl.textContent = total.toLocaleString('id-ID');
        }

        // The search-bar component's own listener runs first and hides/shows rows;
        // this listener recalculates the total from whatever ends up visible.
        searchInput.addEventListener('input', recalcGrandTotal);
    });

    function openExportModal() { document.getElementById('exportModal').classList.remove('hidden'); }
    function closeExportModal() { document.getElementById('exportModal').classList.add('hidden'); }
</script>
@endsection