<table>
    @if($isDetail)
        {{-- ================= TABEL DETAIL ================= --}}
        <thead>
            <tr>
                <th>No</th>
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
                    <td>{{ $row->no_raf }}</td>
                    <td>{{ $row->supplier_code }}</td>
                    <td>{{ $row->supplier_name }}</td>
                    <td>{{ $row->store }}</td>
                    <td>{{ $row->daftar_toko_formatted }}</td>
                    <td>{{ Carbon\Carbon::parse($row->periode_awal)->format('d M Y') }}</td>
                    <td>{{ Carbon\Carbon::parse($row->periode_akhir)->format('d M Y') }}</td>
                    <td>{{ $row->nominal }}</td>
                </tr>
            @endforeach
        </tbody>
        @if($data->count() > 0)
        <tfoot>
            <tr>
                {{-- Gunakan colspan="7" dan align="right" untuk rata kanan bawaan HTML --}}
                <td colspan="8" align="right"><b>Grand Total:</b></td>
                {{-- Biarkan nominal mentah agar terbaca sebagai Number di Excel --}}
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
                    <td>{{ \Carbon\Carbon::create()->month($row->month)->locale('id')->format('F') }}</td>
                    <td>{{ $row->total_data }}</td>
                    <td>{{ $row->total_nominal }}</td>
                </tr>
            @endforeach
        </tbody>
    @endif
</table>