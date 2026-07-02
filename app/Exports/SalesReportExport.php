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
    // Tambahkan properti untuk menampung filter
    protected $year;
    protected $month;
    protected $kode_outlet;

    // Tangkap data dari Controller
    public function __construct($year, $month, $kode_outlet)
    {
        $this->year = $year;
        $this->month = $month;
        $this->kode_outlet = $kode_outlet;
    }

    public function view(): View
    {
        // 1. Buat Base Query untuk mencari D-Day sesuai Filter
        $baseQuery = SalesReport::query();

        if ($this->year) {
            $baseQuery->whereYear('transaction_date', $this->year);
        }
        if ($this->month) {
            $baseQuery->whereMonth('transaction_date', $this->month);
        }
        if ($this->kode_outlet) {
            $baseQuery->where('outlet_code', $this->kode_outlet);
        }

        // Cari Tanggal Terakhir di Database (D-Day Sunday) berdasarkan filter
        $lastDateStr = $baseQuery->max('transaction_date');
        
        // Jika data ketemu, pakai tanggal tersebut. Jika kosong, set ke akhir bulan yang dipilih form.
        if ($lastDateStr) {
            $dDayEnd = Carbon::parse($lastDateStr);
        } else {
            $dDayEnd = Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();
        }
        
        if ($dDayEnd->dayOfWeek !== Carbon::SUNDAY) {
            $dDayEnd->next(Carbon::SUNDAY);
        }

        // 2. Fungsi Label Dinamis (Tetap sama)
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

        // 3. Tentukan Rentang Waktu (Tetap sama)
        $weeks = [
            'd_day' => ['end' => $dDayEnd->copy(), 'label' => $generateLabel($dDayEnd)],
            'w1'    => ['end' => $dDayEnd->copy()->subWeeks(1), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(1))],
            'w2'    => ['end' => $dDayEnd->copy()->subWeeks(2), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(2))],
            'w3'    => ['end' => $dDayEnd->copy()->subWeeks(3), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(3))],
            'w4'    => ['end' => $dDayEnd->copy()->subWeeks(4), 'label' => $generateLabel($dDayEnd->copy()->subWeeks(4))],
        ];

        $startDate = $weeks['w4']['end']->copy()->subDays(2)->format('Y-m-d');
        $endDate   = $weeks['d_day']['end']->format('Y-m-d');

        // 4. Query Data Transaksi Final
        $salesQuery = SalesReport::whereBetween('transaction_date', [$startDate, $endDate])
            ->whereRaw('DAYOFWEEK(transaction_date) IN (1, 6, 7)');
            
        // JANGAN LUPA filter berdasarkan outlet juga di query utamanya (jika user mengisi kode outlet)
        if ($this->kode_outlet) {
            $salesQuery->where('outlet_code', $this->kode_outlet);
        }

        $rawSales = $salesQuery->get();

        // 5. Susun Data Dinamis (Tetap sama)
        $products = [];
        $no = 1;

        foreach ($rawSales as $sale) {
            $sku = $sale->sku;
            
            if (!isset($products[$sku])) {
                $products[$sku] = [
                    'category'    => 'GENERAL', 
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
                $dayKey = '';
                if ($saleDate->dayOfWeek === Carbon::FRIDAY) $dayKey = 'jumat';
                elseif ($saleDate->dayOfWeek === Carbon::SATURDAY) $dayKey = 'sabtu';
                elseif ($saleDate->dayOfWeek === Carbon::SUNDAY) $dayKey = 'minggu';

                if ($dayKey) {
                    $products[$sku]['weeks'][$targetWeek][$dayKey]['qty'] += $sale->quantity;
                    $products[$sku]['weeks'][$targetWeek][$dayKey]['val'] += $sale->gross_sales;
                    
                    $products[$sku]['weeks'][$targetWeek]['sum']['qty'] += $sale->quantity;
                    $products[$sku]['weeks'][$targetWeek]['sum']['val'] += $sale->gross_sales;
                }
            }
        }

        // 6. Kalkulasi Average & Selisih (Tetap sama)
        foreach ($products as $sku => &$data) {
            foreach (['jumat', 'sabtu', 'minggu', 'sum'] as $day) {
                $qty4Weeks = $data['weeks']['w4'][$day]['qty'] + $data['weeks']['w3'][$day]['qty'] + $data['weeks']['w2'][$day]['qty'] + $data['weeks']['w1'][$day]['qty'];
                $val4Weeks = $data['weeks']['w4'][$day]['val'] + $data['weeks']['w3'][$day]['val'] + $data['weeks']['w2'][$day]['val'] + $data['weeks']['w1'][$day]['val'];
                
                $data['avg'][$day]['qty'] = $qty4Weeks / 4;
                $data['avg'][$day]['val'] = $val4Weeks / 4;

                $data['selisih'][$day]['qty'] = $data['weeks']['d_day'][$day]['qty'] - $data['avg'][$day]['qty'];
                $data['selisih'][$day]['val'] = $data['weeks']['d_day'][$day]['val'] - $data['avg'][$day]['val'];
            }
            
            if ($data['avg']['sum']['qty'] > 0) {
                $data['selisih_pct']['qty'] = ($data['selisih']['sum']['qty'] / $data['avg']['sum']['qty']) * 100;
            }
            if ($data['avg']['sum']['val'] > 0) {
                $data['selisih_pct']['val'] = ($data['selisih']['sum']['val'] / $data['avg']['sum']['val']) * 100;
            }
        }

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