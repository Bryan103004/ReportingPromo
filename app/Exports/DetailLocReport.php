<?php
namespace App\Exports;

use App\Models\Category;
use App\Models\Loc;
use App\Models\Toko;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailLocReport implements FromView, ShouldAutoSize, WithStyles
{
    protected $year;
    protected $month;

    // Terima parameter dari Controller
    public function __construct($year = null, $month = null)
    {
        $this->year = $year;
        $this->month = $month;
    }

    public function view(): View
    {
        // MODE 1: Jika tahun dan bulan diisi (Export Detail)
        if ($this->year && $this->month) {
                // Tambahkan with('tokos') di sini
            $data = Loc::with(['tokos','categories'])
                ->whereYear('periode_bulan', $this->year)
                ->whereMonth('periode_bulan', $this->month)
                ->orderBy('category_id')
                ->orderBy('periode_awal', 'asc')
                ->orderBy('periode_akhir', 'asc')
                ->get();
                
            $isDetail = true;
            $stores = null;
        } 
        // MODE 2: Jika kosong (Export Rekap All)
        else {
            $year = $this->year ?? date('Y');
            $isDetail = false;
            $allStores = Toko::all();
            $allCategories = Category::all();

            // 1. Bangun Subquery Bulan Statis
            $bulanSubquery = "(SELECT 1 AS id_bulan, 'JANUARI' AS nama_bulan 
                                UNION ALL SELECT 2, 'FEBRUARI' UNION ALL SELECT 3, 'MARET' 
                                UNION ALL SELECT 4, 'APRIL' UNION ALL SELECT 5, 'MEI' 
                                UNION ALL SELECT 6, 'JUNI' UNION ALL SELECT 7, 'JULI' 
                                UNION ALL SELECT 8, 'AGUSTUS' UNION ALL SELECT 9, 'SEPTEMBER' 
                                UNION ALL SELECT 10, 'OKTOBER' UNION ALL SELECT 11, 'NOVEMBER' 
                                UNION ALL SELECT 12, 'DESEMBER') AS m_bulan";

            $finalData = collect();

            // 2. Loop per Kategori
            foreach ($allCategories as $category) {
            // 1. Bangun SELECT Fields
            $selectFields = [
                "'{$category->nama_kategori}' AS Kategori",
                "m_bulan.id_bulan AS urutan_bulan",
                "m_tahun.tahun AS Tahun",
                "m_bulan.nama_bulan AS Periode"
            ];

            foreach ($allStores as $store) {
                $aliasToko = str_replace('GL ', '', $store->nama_toko);
                // Tambahkan filter category_id langsung di dalam CASE
                $selectFields[] = "SUM(CASE WHEN tk.nama_toko = '{$store->nama_toko}' AND l.category_id = {$category->id} THEN l.nominal ELSE 0 END) AS `{$aliasToko}`";
            }
            // Filter total juga harus spesifik kategori
            $selectFields[] = "SUM(CASE WHEN l.category_id = {$category->id} THEN IFNULL(l.nominal, 0) ELSE 0 END) AS TOTAL";

            // 2. Query Utama
            $categoryData = DB::table(DB::raw($bulanSubquery))
                ->crossJoin(DB::raw("(SELECT DISTINCT YEAR(periode_bulan) AS tahun FROM locs WHERE periode_bulan IS NOT NULL) AS m_tahun"))
                // JOIN transaksi (l) dengan kondisi filter kategori sudah dilakukan di sini
                ->leftJoin('locs as l', function($join) use ($category) {
                    $join->on(DB::raw('MONTH(l.periode_bulan)'), '=', 'm_bulan.id_bulan')
                        ->on(DB::raw('YEAR(l.periode_bulan)'), '=', 'm_tahun.tahun')
                        ->where('l.category_id', '=', $category->id); // <--- FILTER KATEGORI HARUS DI SINI
                })
                ->leftJoin('locs_toko as lt', 'l.id', '=', 'lt.Loc_id')
                ->leftJoin('tokos as tk', 'lt.toko_id', '=', 'tk.id')
                ->selectRaw(implode(', ', $selectFields))
                ->where('m_tahun.tahun', $year) 
                ->groupBy('m_tahun.tahun', 'm_bulan.id_bulan', 'm_bulan.nama_bulan')
                ->orderBy('m_bulan.id_bulan', 'ASC')
                ->get();

            $finalData = $finalData->concat($categoryData);

                // Baris Pembatas (Sekat 99)
                $pembatasArray = [
                    'Kategori'     => '',
                    'urutan_bulan' => 99,
                    'Tahun'        => null,
                    'Periode'      => "--- AKHIR REKAP {$category->nama_kategori} --- \n",
                    'TOTAL'        => ''
                ];
                
                foreach ($allStores as $store) {
                    $aliasToko = str_replace('GL ', '', $store->nama_toko);
                    $pembatasArray[$aliasToko] = '';
                }
                $finalData->push((object) $pembatasArray);
            }

            // 3. Menghitung GRAND TOTAL
            $grandTotalArray = [
                'Kategori'     => 'GRAND TOTAL',
                'urutan_bulan' => 100,
                'Tahun'        => null,
                'Periode'      => 'TOTAL KESELURUHAN'
            ];

            foreach ($allStores as $store) {
                $aliasToko = str_replace('GL ', '', $store->nama_toko);
                // Menjumlahkan kolom toko dari data yang bukan baris sekat (urutan_bulan < 99)
                $grandTotalArray[$aliasToko] = $finalData->where('urutan_bulan', '<', 99)->sum($aliasToko);
            }
            $grandTotalArray['TOTAL'] = $finalData->where('urutan_bulan', '<', 99)->sum('TOTAL');

            $finalData->push((object) $grandTotalArray);

            $stores = $allStores;
            $data = $finalData;
        }
     
        return view('Loc.exports_excel', [
            'data' => $data,
            'isDetail' => $isDetail,
            'year' => $this->year,
            'month' => $this->month,
            'stores' => $stores
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Cukup bold di baris pertama (Header Tabel)
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}