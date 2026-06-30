<?php

namespace App\Exports;

use App\Models\SalesReport;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class SalesReportExport implements FromView, ShouldAutoSize, WithStyles
{
    public function view(): View
    {
        // 1. Cari Tanggal Terakhir di Database (D-Day Sunday)
        $lastDateStr = SalesReport::max('transaction_date');
        $dDayEnd = $lastDateStr ? Carbon::parse($lastDateStr) : Carbon::now();
        
        // Pastikan ujungnya adalah hari Minggu (7)
        if ($dDayEnd->dayOfWeek !== Carbon::SUNDAY) {
            $dDayEnd->next(Carbon::SUNDAY);
        }

        // 2. Fungsi untuk membuat label dinamis (Contoh: "8-10 MEI" atau "29 APR - 1 MEI")
        $generateLabel = function($sunday) {
            $friday = $sunday->copy()->subDays(2);
            $monthEnd = strtoupper($sunday->translatedFormat('F'));
            
            if ($friday->month === $sunday->month) {
                return $friday->format('d') . '-' . $sunday->format('d') . ' ' . $monthEnd;
            } else {
                $monthStart = strtoupper($friday->translatedFormat('M'));
                return $friday->format('d') . ' ' . $monthStart . ' - ' . $sunday->format('d') . ' ' . $monthEnd;
            }
        };

        // 3. Tentukan Rentang Waktu
        $weeks = [
            'd_day' => ['end' => $dDayEnd->copy(), 'label' => $generateLabel($dDayEnd)],
            'w1'    => ['end' => $dDayEnd->copy()->subWeeks(1), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(1))],
            'w2'    => ['end' => $dDayEnd->copy()->subWeeks(2), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(2))],
            'w3'    => ['end' => $dDayEnd->copy()->subWeeks(3), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(3))],
            'w4'    => ['end' => $dDayEnd->copy()->subWeeks(4), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(4))],
        ];

        // Batas penarikan data SQL (Dari Jumat 4 Minggu lalu sampai Minggu D-Day)
        $startDate = $weeks['w4']['end']->copy()->subDays(2)->format('Y-m-d');
        $endDate   = $weeks['d_day']['end']->format('Y-m-d');

        // 4. Query Data Transaksi
        $rawSales = SalesReport::whereBetween('transaction_date', [$startDate, $endDate])
            ->whereRaw('DAYOFWEEK(transaction_date) IN (1, 6, 7)') // 1=Minggu, 6=Jumat, 7=Sabtu
            ->get();

        // 5. Susun Data Dinamis (Grouping per SKU)
        $products = [];
        $no = 1;

        foreach ($rawSales as $sale) {
            $sku = $sale->sku;
            
            // Inisialisasi struktur array jika SKU baru
            if (!isset($products[$sku])) {
                $products[$sku] = [
                    'category'    => '', // Sesuaikan jika ada master kategori
                    'no'          => $no++,
                    'sku'         => $sku,
                    'description' => $sale->product_name,
                    'weeks'       => [
                        'w4'    => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                        'w3'    => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                        'w2'    => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                        'w1'    => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                        'd_day' => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                    ],
                    'avg'         => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                    'selisih'     => ['jumat' => ['qty'=>0,'val'=>0], 'sabtu' => ['qty'=>0,'val'=>0], 'minggu' => ['qty'=>0,'val'=>0], 'sum' => ['qty'=>0,'val'=>0]],
                    'selisih_pct' => ['qty'=>0, 'val'=>0]
                ];
            }

            // Tentukan data ini masuk ke minggu mana
            $saleDate = Carbon::parse($sale->transaction_date);
            $targetWeek = null;

            foreach ($weeks as $key => $weekData) {
                $friday = $weekData['end']->copy()->subDays(2);
                if ($saleDate->between($friday, $weekData['end'])) {
                    $targetWeek = $key;
                    break;
                }
            }

            if ($targetWeek) {
                // Tentukan Hari
                $dayKey = '';
                if ($saleDate->dayOfWeek === Carbon::FRIDAY) $dayKey = 'jumat';
                elseif ($saleDate->dayOfWeek === Carbon::SATURDAY) $dayKey = 'sabtu';
                elseif ($saleDate->dayOfWeek === Carbon::SUNDAY) $dayKey = 'minggu';

                // Tambahkan Qty & Value
                if ($dayKey) {
                    $products[$sku]['weeks'][$targetWeek][$dayKey]['qty'] += $sale->quantity;
                    $products[$sku]['weeks'][$targetWeek][$dayKey]['val'] += $sale->gross_sales;
                    
                    // Sum per Minggu
                    $products[$sku]['weeks'][$targetWeek]['sum']['qty'] += $sale->quantity;
                    $products[$sku]['weeks'][$targetWeek]['sum']['val'] += $sale->gross_sales;
                }
            }
        }

        // 6. Kalkulasi Average 4 Minggu & Selisih (D-Day vs Avg)
        foreach ($products as $sku => &$data) {
            foreach (['jumat', 'sabtu', 'minggu', 'sum'] as $day) {
                // Total 4 minggu masa lalu
                $qty4Weeks = $data['weeks']['w4'][$day]['qty'] + $data['weeks']['w3'][$day]['qty'] + $data['weeks']['w2'][$day]['qty'] + $data['weeks']['w1'][$day]['qty'];
                $val4Weeks = $data['weeks']['w4'][$day]['val'] + $data['weeks']['w3'][$day]['val'] + $data['weeks']['w2'][$day]['val'] + $data['weeks']['w1'][$day]['val'];
                
                // Average
                $data['avg'][$day]['qty'] = $qty4Weeks / 4;
                $data['avg'][$day]['val'] = $val4Weeks / 4;

                // Selisih = D-Day - Average
                $data['selisih'][$day]['qty'] = $data['weeks']['d_day'][$day]['qty'] - $data['avg'][$day]['qty'];
                $data['selisih'][$day]['val'] = $data['weeks']['d_day'][$day]['val'] - $data['avg'][$day]['val'];
            }
            
            // Persentase Selisih (Selisih SUM / Avg SUM * 100)
            if ($data['avg']['sum']['qty'] > 0) {
                $data['selisih_pct']['qty'] = ($data['selisih']['sum']['qty'] / $data['avg']['sum']['qty']) * 100;
            }
            if ($data['avg']['sum']['val'] > 0) {
                $data['selisih_pct']['val'] = ($data['selisih']['sum']['val'] / $data['avg']['sum']['val']) * 100;
            }
        }

        dd($products);

        return view('report.export_excel', [
            'headers'  => $weeks,
            'products' => $products
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            5 => ['font' => ['bold' => true]],
        ];
    }
}