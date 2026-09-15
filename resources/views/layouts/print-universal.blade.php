<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{$title}}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 8px; color: #333; }
        .container { width: 100%; max-width: fit-content; margin: 0 auto; padding: 0px; }
        .header { text-align: left;}
        .info-table, .item-table { width: fit-content; border-collapse: collapse;}
        .item-table th, .item-table td { border: 1px solid #ddd; padding: 4px; text-align: center; }
        .item-table th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        @media print {
            body { print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="container">
        <!-- Tombol Aksi (Hilang saat diprint) -->
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()" style="padding: 8px 15px; background: #2563eb; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Cetak Ulang</button>
            <a href="{{ url()->previous() }}" style="margin-left: 10px; text-decoration: none; color: #555;">Kembali</a>
        </div>

        <h2 style="text-align: center; font-size: 10px; font-weight: bold;">{{ $title }}</h2>

        <!-- Header -->
        <div class="header">
            <p>Periode Awal: {{ Carbon::parse($document->periode_awal)->format('d/m/Y')  }}</p>
            <p>Periode Akhir: {{ Carbon::parse($document->periode_akhir)->format('d/m/Y') }}</p>
            <p>No. Dokumen: <strong>{{ $document->no_raf }}</strong></p>
            <p>Supplier Name: {{ $document->supplier_name }}</p>
            <p>Toko: {{ $document->tokos->pluck('kode_excel')->implode(', ') }}</p>
            <p>No. Shiji: {{ $document->no_shiji }}</p>
        </div>

        <!-- Tabel Item -->
        <table class="item-table">
            @php
                // Mengumpulkan semua ID toko unik yang ada di dalam item-item dokumen ini
                $allTokoIds = $document->items->flatMap->stores->pluck('toko_id')->unique();
                // Ambil data master tokonya (pastikan relasi toko terpanggil atau query manual)
                $tokosHeader = \App\Models\Toko::whereIn('id', $allTokoIds)->get();
            @endphp
            <thead>
                <tr>
                    <th>No</th>
                    <th>Article</th>
                    <th>Shiji Code</th>
                    <th>Description</th>
                    <th>Discount Nominal</th>
                    <th>Promo Discount %</th>
                    <th>Reg</th>
                    <th>Promo</th>
                    <th>Claim</th>
                    @foreach($tokosHeader as $toko)
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: center;">
                            Sales {{ $toko->kode_excel }}
                        </th>
                    @endforeach
                    <th>Sales Total</th>
                    @foreach($tokosHeader as $toko)
                        <th style="border: 1px solid #ddd; padding: 6px; text-align: center;">
                            Value {{ $toko->kode_excel }}
                        </th>
                    @endforeach
                    <th>Value Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($document->items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->article }}</td>
                        <td>{{ $item->shiji_code }}</td>
                        <td>{{ $item->description }}</td>
                        <td>{{ number_format($item->disc_nominal, 0, ',', '.') }}</td>
                        <td>{{ number_format($item->promo_disc, 0, ',', '.') ?? '-'}}</td>
                        <td>{{ number_format($item->reg, 0, ',', '.') ?? '-' }}</td>
                        <td>{{ number_format($item->promo, 0, ',', '.') ?? '-'}}</td>
                        <td>{{ number_format($item->claim, 0, ',', '.') ?? '-' }}</td>
                        @foreach($tokosHeader as $toko)
                            @php
                                // Cari data toko terkait untuk item ini
                                $itemToko = $item->stores->firstWhere('toko_id', $toko->id);
                            @endphp
                            <td class="text-right">{{ number_format(optional($itemToko)->sales_qty, 0, ',', '.') ?? 0 }}</td>
                        @endforeach
                        <td>{{ number_format($item->sales_total, 0, ',', '.') }}</td>
                        @foreach($tokosHeader as $toko)
                            @php
                                // Cari data toko terkait untuk item ini
                                $itemToko = $item->stores->firstWhere('toko_id', $toko->id);
                            @endphp
                            <td class="text-right">{{ number_format(optional($itemToko)->value, 0, ',', '.') ?? 0 }}</td>
                        @endforeach
                        <td>{{ number_format($item->value_total, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ 9 + (count($tokosHeader) * 2) + 2 }}" class="text-center" style="color: #777; padding: 15px;">
                            Tidak ada item detail terkait.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @php
                $TotalValueSatuToko = 0;
                $totalSalesQtySatuToko = 0;
                // $allTokoIds = $document->items->flatMap->stores->pluck('toko_id')->unique();
                // // Ambil data master tokonya (pastikan relasi toko terpanggil atau query manual)
                // $tokosHeader = \App\Models\Toko::whereIn('id', $allTokoIds)->get();
                // // Cari data toko terkait untuk item ini
                // $itemToko = $document->items->flatMap->stores->firstWhere('toko_id', $toko->id);
            @endphp
            <tfoot>
                <tr>
                    <td colspan="9" align="right"><b>Grand Total:</b></td>
                    
                    @foreach($tokosHeader as $toko)
                        @php
                            $totalSalesQtySatuToko = $document->items->flatMap->stores->where('toko_id', $toko->id)->pluck('sales_qty')->sum();
                        @endphp    
                        <td class="text-right">{{ number_format($totalSalesQtySatuToko, 0, ',', '.') ?? 0 }}</td>
                    @endforeach
                    <td class="text-right">{{ $document->items->sum('sales_total') }}</td>

                    @foreach($tokosHeader as $toko)
                        @php
                            $totalValueSatuToko = $document->items->flatMap->stores->where('toko_id', $toko->id)->pluck('value')->sum(); 
                        @endphp
                        <td class="text-right">{{ number_format($totalValueSatuToko, 0, ',', '.') ?? 0 }}</td>
                    @endforeach

                    @php
                        $totalValueAll = $document->items->flatMap->stores->whereIn('toko_id', $tokosHeader->pluck('id'))->pluck('value')->sum();
                    @endphp

                    <td class="text-right">{{ number_format($document->items->sum('value_total'), 0, ',', '.') ?? 0 }}</td>
                </tr>
            </tfoot>
        </table>

        <!-- Kotak Catatan Modern -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 40px;">
            <div style="background-color: #dedede9a; margin-top: 4px; border-left: 4px solid #000000; padding: 4px; border-radius: 2spx; width: fit-content;">
                <p style="margin: 4px 0 4px 0; font-size: 10px; font-weight: bold; letter-spacing: 0.5px; text-align: right;">NOTE: {{ $document->remarks }}</p>
            </div>
        </div>

        <!-- Area Tanda Tangan Proproporsional -->
        <div style="display: flex; justify-content: flex-start; gap: 80px; margin-top: 50px; font-family: sans-serif;">
            <!-- Kolom Pembuat -->
            <div style="text-align: left; width: 180px; display: flex; flex-direction: column; justify-content: space-between; height: 120px;">
                <p style="margin: 0; color: #495057; font-size: 14px; text-align: center;">Prepared by,</p>
                <p style="margin: 0; font-weight: bold; color: #212529; font-size: 14px; text-align: center;">{{ $prepared_by }}</p>
            </div>

            <!-- Kolom Mengetahui -->
            <div style="text-align: left; width: 180px; display: flex; flex-direction: column; justify-content: space-between; height: 120px;">
                <p style="margin: 0; color: #495057; font-size: 14px; text-align: center;">Acknowledged by,</p>
                <p style="margin: 0; font-weight: bold; color: #212529; font-size: 14px; text-align: center;">{{ $acknowledged_by }}</p>
            </div>
        </div>

    </div>

</body>
</html>