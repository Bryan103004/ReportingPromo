@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8">
    
    {{-- Bagian Header & Tombol Kembali --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('jsm.index') }}" class="text-gray-400 hover:text-blue-600 transition-colors" title="Kembali ke Index">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Detail Rafaksi JSM</h1>
            </div>
            <p class="text-md text-black font-medium">Daftar seluruh transaksi Rafaksi JSM pada periode <span class="font-bold text-gray-700">{{ $periodeTitle }}</span>.</p>
        </div>
    </div>

    <div class="flex ms-auto m-4 gap-2">
        <!-- <a href="{{ route('jsm.export', ['year' => $year, 'month' => $month]) }}" class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition">
            Export Rekap CSV
        </a> -->
        <a href="{{ route('jsm.export.excel', ['year' => $year, 'month' => $month]) }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Export Rekap XLS
        </a>
        <a href="{{ route('jsm.print', ['year' => $year, 'month' => $month]) }}" class="bg-red-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
            Print
        </a>
    </div>

    {{--  KOMPONEN FILTER --}}
    <x-filter-bar :suppliers="$suppliers" />

    <x-search-bar 
        placeholder="Masukkan user atau aksi..." 
        tableId="jsmDetail" 
    />

    <x-per-page/>

    {{-- Tabel Container (Card) --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="jsmDetail" class="w-full text-left text-sm text-gray-600">
                
                {{-- Head Tabel --}}
                <thead class="bg-gray-50 border-b border-gray-200 text-xs uppercase font-bold text-gray-500 tracking-wider">
                    <tr>
                        <th class="px-6 py-4 w-16 text-center">No</th>
                        <th class="px-6 py-4">Supplier Code</th>
                        <th class="px-6 py-4">Supplier Name</th>
                        <th class="px-6 py-4">Category</th>
                        <th class="px-6 py-4">Periode Promo</th>
                        <th class="px-6 py-4">No. RAF</th>
                        <th class="px-6 py-4">Region</th>
                        <th class="px-6 py-4">Store</th>
                        <th class="px-6 py-4 text-right">Remarks</th>
                        <th class="px-6 py-4 text-right">Nominal</th>

                        {{-- <th class="px-6 py-4 text-center">Aksi</th> --}} {{-- Buka komen ini jika nanti butuh tombol Edit/Delete --}}
                    </tr>
                </thead>
                
                {{-- Body Tabel --}}
                <tbody class="divide-y divide-gray-100">
                    @forelse($jsms as $jsm)
                        <tr class="hover:bg-gray-50 transition-colors">
                            {{-- Nomor Urut --}}
                            <td class="px-6 py-4 text-center font-medium text-gray-500">
                                {{ $loop->iteration }}
                            </td>
            
                            
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $jsm->supplier_code }}</div>
                            </td>

                            {{-- Supplier (Kode & Nama) --}}
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $jsm->supplier_name }}</div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-800">{{ $jsm->categories->nama_kategori }}</div>
                            </td>
                            
                            {{-- Periode (Awal - Akhir) --}}
                            <td class="px-6 py-4">
                                <div class="text-xs font-semibold text-gray-700">
                                    {{ \Carbon\Carbon::parse($jsm->periode_awal)->format('d M Y') }} 
                                    <span class="text-gray-400 mx-1">-</span> 
                                    {{ \Carbon\Carbon::parse($jsm->periode_akhir)->format('d M Y') }}
                                </div>
                            </td>
                        
                            {{-- No RAF --}}
                            <td class="px-6 py-4 font-bold text-gray-800">
                                {{ $jsm->no_raf ?? '-' }}
                            </td>

                            {{-- Region --}}
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 font-semibold px-2 py-1 rounded text-xs">
                                {{ $jsm->store }}                                </span>
                            </td>

                            {{-- Toko --}}
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 font-semibold px-2 py-1 rounded text-xs">
                                {{ optional($jsm->tokos->first())->nama_toko ?? '-' }}                                </span>
                            </td>
                            
                            {{-- Remarks --}}
                            <td class="px-6 py-4">
                                <span class="bg-gray-100 text-gray-700 border border-gray-200 font-semibold px-2 py-1 rounded text-xs">
                                    {{ $jsm->remarks }}
                                </span>
                            </td>

                            {{-- Nominal --}}
                            <td class="px-6 py-4 text-right font-bold text-green-600">
                                Rp {{ number_format($jsm->nominal, 0, ',', '.') }}
                            </td>

                            {{-- Aksi (Opsional) --}}
                            {{-- 
                            <td class="px-6 py-4 text-center">
                                <form action="{{ route('jsm.destroy', $jsm->id) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Yakin hapus data ini?')">Hapus</button>
                                </form>
                            </td> 
                            --}}
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
                @if($jsms->count() > 0)
                <tfoot class="bg-gray-50 border-t border-gray-200">
                    <tr>
                        <td colspan="9" class="px-6 py-4 text-right font-bold text-gray-800 uppercase tracking-wider text-xs">
                            Grand Total:
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-blue-700 text-base">
                            Rp {{ number_format($jsms->sum('nominal'), 0, ',', '.') }}
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
        {{ $jsms->links() }}
    </div>
    
</div>
@endsection