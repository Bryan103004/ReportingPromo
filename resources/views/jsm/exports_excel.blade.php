<html>
<table>
    @if($isDetail)
        {{-- ================= TABEL DETAIL ================= --}}
        <thead>
            <tr>
                <th>No</th>
                <th>Category</th>
                <th>No. RAF</th>
                <th>Kode Supplier</th>
                <th>Nama Supplier</th>
                <th>Region</th>
                <th>Store</th>
                <th>Periode Awal</th>
                <th>Periode Akhir</th>
                <th>Nominal</th>
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
                {{-- FIX: Ubah colspan ke 9 agar Grand Total sejajar dengan kolom Nominal --}}
                <td colspan="9" align="right"><b>Grand Total:</b></td>
                <td align="right"><b>{{ $data->sum('nominal') }}</b></td>
            </tr>
        </tfoot>
        @endif
    @else
        {{-- ================= TABEL REKAP ================= --}}
        <thead>
            <tr>
                <th>Tahun</th>
                <th>Bulan</th>
                <th>Total Transaksi</th>
                <th>Total Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $row)
                <tr>
                    <td>{{ $row->year }}</td>
                    {{-- FIX: Ditambahkan backslash \ sebelum Carbon --}}
                    <td>{{ \Carbon\Carbon::create()->month($row->month)->locale('id')->format('F') }}</td>
                    <td>{{ $row->total_data }}</td>
                    <td align="right">{{ $row->total_nominal }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</table>

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
    th { 
        background-color: #f3f4f6; 
        text-align: center; 
        font-weight: bold;
    }
</style>
</html>
