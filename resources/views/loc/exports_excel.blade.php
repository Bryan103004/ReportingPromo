<html>
<table>
    @if($isDetail)
        {{-- ================= TABEL DETAIL ================= --}}
        <thead>
            <tr>
                <!-- Ditambahkan FF di depan kode warna agar dibaca ARGB oleh Excel -->
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">No</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Category</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">No. RAF</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Kode Supplier</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Nama Supplier</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Region</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Store</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Periode Awal</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Periode Akhir</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Nominal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->categories->nama_kategori ?? 'Tanpa Kategori' }}</td>
                <td>{{ $row->no_raf }}</td>
                <td>{{ $row->supplier_code }}</td>
                <td>{{ $row->supplier_name }}</td>
                <td>{{ $row->store }}</td>
                <td>{{ $row->daftar_toko_formatted }}</td>
                
                <td>{{ $row->periode_awal ? \Carbon\Carbon::parse($row->periode_awal)->format('d M Y') : '-' }}</td>
                <td>{{ $row->periode_akhir ? \Carbon\Carbon::parse($row->periode_akhir)->format('d M Y') : '-' }}</td>
                <td align="right">{{ $row->nominal }}</td>
            </tr>
        @endforeach
        </tbody>
        @if($data->count() > 0)
        <tfoot>
            <tr>
                <td style="background-color: #FF4E73DF; color: #FFFFFF;" colspan="9" align="right"><b>Grand Total:</b></td>
                <td style="background-color: #FF4E73DF; color: #FFFFFF;" align="right"><b>{{ $data->sum('nominal') }}</b></td>
            </tr>
        </tfoot>
        @endif
    @else
        {{-- ================= TABEL REKAP (MATRIX) ================= --}}
        <thead>
            <tr>
                <!-- Bagian ini juga wajib dipasang inline style ARGB agar tabel rekapnya ikut biru -->
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Periode</th>
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">Kategori</th> 
                @foreach($stores as $store)
                    <th style="background-color: #FF4E73DF; color: #FFFFFF;">{{ $store->nama_toko }}</th>
                @endforeach
                <th style="background-color: #FF4E73DF; color: #FFFFFF;">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                    @php
                        $isAkhirRekap = str_contains($row->Periode, 'AKHIR REKAP');
                        $isTotalKeseluruhan = str_contains($row->Periode, 'TOTAL KESELURUHAN');
                    @endphp
                <tr>
                    <td style="{{ $isAkhirRekap ? 'background-color: #FF000000; color: #FFFFFF; font-weight: bold;' : ($isTotalKeseluruhan ? 'background-color: #FF4E73DF; color: #FFFFFF; font-weight: bold;' : '') }}">
                        {{ $row->Periode }}
                    </td>
                    <td style="{{ $isAkhirRekap ? 'background-color: #FF000000; color: #FFFFFF; font-weight: bold;' : ($isTotalKeseluruhan ? 'background-color: #FF4E73DF; color: #FFFFFF; font-weight: bold;' : '') }}">{{ $row->Kategori }}</td> 
                    @foreach($stores as $store)
                        @php 
                            $colName = str_replace('GL ', '', $store->nama_toko);
                            $val = $row->$colName; 
                        @endphp
                        
                        <td style="{{ $isAkhirRekap ? 'background-color: #FF000000; color: #FFFFFF; font-weight: bold;' : ($isTotalKeseluruhan ? 'background-color: #FF4E73DF; color: #FFFFFF; font-weight: bold;' : '') }}">{{ ($val === '' || $val === null) ? '' : $val }}</td>
                    @endforeach
                    <td align="right" style="background-color: #FF4E73DF; font-weight: bold;">
                        {{ ($row->TOTAL === '' || $row->TOTAL === null) ? '' : $row->TOTAL }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    @endif
</table>
</body>

<style>
    table { 
        width: 100%; 
        border-collapse: collapse; 
        font-family: sans-serif; 
    }
    th, td { 
        border: 1px solid #000; 
        padding: 8px; 
        font-size: 11px;
    }
</style>
</html>
