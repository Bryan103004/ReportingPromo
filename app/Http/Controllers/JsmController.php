<?php

namespace App\Http\Controllers;

use App\Exports\DetailJsmReport;
use App\Models\Category;
use App\Models\Jsm;
use App\Models\Region;
use App\Models\SupplierRafaksi;
use App\Models\Toko;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DetailJmsReport;
use Barryvdh\DomPDF\Facade\Pdf;

class JsmController extends Controller
{
    //
    public function index()
    {
        $jsmGroups = Jsm::selectRaw('
                store,  
                MAX(YEAR(periode_akhir)) as year, -- Mengambil tahun terbaru dalam grup
                MAX(MONTH(periode_akhir)) as month, -- Mengambil bulan terbaru dalam grup
                YEAR(periode_bulan) as year_kerja, 
                MONTH(periode_bulan) as month_kerja, 
                COUNT(*) as total_data, 
                SUM(nominal) as total_nominal
            ')
            ->groupBy('store', 'year_kerja', 'month_kerja')
            ->orderBy('year_kerja', 'asc')
            ->orderBy('month_kerja', 'asc')
            ->get();

        return view('jsm.index', compact('jsmGroups'));
    }

    public function showMonth($year, $month)
    {
        $jsms = Jsm::with(['tokos'])
            ->whereYear('periode_bulan', $year)
            ->whereMonth('periode_bulan', $month)
            ->orderBy('periode_akhir', 'desc') // Urutkan dari tanggal terbaru di bulan tersebut
            ->get(); 

        $periodeTitle = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        return view('jsm.show_month', compact('jsms', 'periodeTitle', 'year', 'month'));
    }

    public function create(){
        $supplierRafaksi = SupplierRafaksi::all();
        $regions = Region::all();
        return view('jsm.create', compact('supplierRafaksi','regions'));
    }

    public function store(Request $request){
        $request->validate([
            'supplier_code' => 'string|required',
            'supplier_name' => 'string|required',
            'periode_awal' => 'date|required',
            'periode_akhir' => 'date|required|after_or_equal:periode_awal',
            'no_raf' => 'string|required',
            'periode_bulan' => 'string|required',
            'store' => 'string|required',
            'nominal' => 'numeric|min:0|required',
            'remarks' => 'string|nullable',
            // 'toko_id' => 'array|required',
            'toko_id' => 'exists:tokos,id',
        ]);

        $jsm = Jsm::create($request->all());
        $jsm->tokos()->sync($request->toko_id);
       
        ActivityLogger::logCreate(
            $jsm,
            $jsm->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'store', 'nominal']),
            "Created Master JSM #{$jsm->id}: {$jsm->supplier_name} with Nominal {$jsm->nominal}"
        );

        return redirect()->route('jsm.index')->with('success', 'Data JSM berhasil disimpan.');
    }

    public function edit(Jsm $jsm){
        return view('jsm.edit', compact('jsm'));
    }

    public function update(Request $request, Jsm $jsm){
        $request->validate([
            'supplier_code' => 'string|required',
            'supplier_name' => 'string|required',
            'periode_awal' => 'date|required',
            'periode_akhir' => 'date|required|after_or_equal:periode_awal',
            'no_raf' => 'string|required',
            'periode_bulan' => 'string|required',
            'store' => 'string|required',
            'nominal' => 'numeric|min:0|required',
            'remarks' => 'string|nullable',
            // 'toko_id' => 'array|required',
            'toko_id' => 'exists:tokos,id',
        ]);

        $jsm->update($request->all());
        $jsm->tokos()->sync($request->toko_id);

        ActivityLogger::logUpdate(
            $jsm,
            $jsm->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'store', 'nominal']),
            "Updated Master JSM #{$jsm->id}: {$jsm->supplier_name} with Nominal {$jsm->nominal}"
        );

        return redirect()->route('jsm.index')->with('success', 'Data JSM berhasil diperbarui.');
    }

    public function destroy(Jsm $jsm){
        $jsm->delete();

        ActivityLogger::logDelete(
            $jsm,
            $jsm->id,
            ['supplier_name' => $jsm->supplier_name, 'nominal' => $jsm->nominal],
            "Deleted Master JSM #{$jsm->id}: {$jsm->supplier_name} with Nominal {$jsm->nominal}"
        );

        return redirect()->route('jsm.index')->with('success', 'Data JSM berhasil dihapus.');
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'export_jsm.csv';
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [];
        $data = [];

        // MODE 1: Jika request memiliki parameter tahun & bulan (Export Detail dari show_month)
        if ($request->has('year') && $request->has('month')) {
            $year = $request->year;
            $month = $request->month;
            $fileName = "detail_jsm_{$year}_{$month}.csv";
            
            $columns = ['No', 'No. RAF', 'Kode Supplier', 'Nama Supplier', 'Region', 'Store', 'Periode Awal', 'Periode Akhir', 'Nominal'];
            
            $jsms = Jsm::whereYear('periode_bulan', $year)
                ->whereMonth('periode_bulan', $month)
                ->orderBy('periode_awal', 'asc')
                ->orderBy('periode_akhir', 'asc')
                ->get();

            foreach ($jsms as $index => $row) {
                $data[] = [
                    $index + 1,
                    $row->no_raf,
                    $row->supplier_code,
                    $row->supplier_name,
                    $row->store,
                    $row->daftar_toko_formatted,
                    $row->periode_awal,
                    $row->periode_akhir,
                    $row->nominal
                ];
            }
        } 
        // MODE 2: Jika tidak ada parameter (Export Summary dari Index)
        else {
            $fileName = "rekap_jsm_all.csv";
            $columns = ['Tahun', 'Bulan', 'Total Transaksi', 'Total Nominal'];

            $jsmGroups = Jsm::selectRaw('
                    YEAR(periode_bulan) as year, 
                    MONTH(periode_awal) as month, 
                    COUNT(*) as total_data, 
                    SUM(nominal) as total_nominal
                ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            foreach ($jsmGroups as $group) {
                $data[] = [
                    $group->year,
                    Carbon::create()->month($group->month)->locale('id')->format('F'),
                    $group->total_data,
                    $group->total_nominal
                ];
            }
        }

        // Proses Generate File CSV
        $headers["Content-Disposition"] = "attachment; filename=$fileName";
        
        $callback = function() use($columns, $data) {
            $file = fopen('php://output', 'w');
            
            // Opsional: Tambahkan separator untuk mengenali format Excel Indonesia (titik koma)
            // fputs($file, $bom =(chr(0xEF) . chr(0xBB) . chr(0xBF))); 
            
            fputcsv($file, $columns);

            foreach ($data as $item) {
                fputcsv($file, $item);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request)
    {
        // Tangkap parameter dari URL (bisa ada isinya, bisa juga kosong)
        $year = $request->year;
        $month = $request->month;

        $stores = Toko::all();

        // Tentukan nama file secara dinamis berdasarkan parameter
        if ($year && $month) {
            $fileName = 'Detail_Jsm_Report_'. $year . '_' . $month . '.xlsx';
        } else {
            $fileName = 'Rekap_Jsm_Report_All.xlsx';
        }

        // PENTING: Masukkan $year dan $month ke dalam kurung kelas export-nya
        return Excel::download(new DetailJsmReport($year, $month, $stores), $fileName);
    }

    public function printPdf(Request $request){
        $year = $request->year;
        $month = $request->month;

        if($year && $month){
            $data = Jsm::with(['tokos'])
                    ->whereYear('periode_bulan', $year)
                    ->whereMonth('periode_bulan', $month)
                    ->orderBy('periode_awal', 'asc')
                    ->orderBy('periode_akhir', 'asc')
                    ->get();
                    $isDetail = true;
        }
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
                $selectFields[] = "SUM(CASE WHEN tk.nama_toko = '{$store->nama_toko}' AND j.category_id = {$category->id} THEN j.nominal ELSE 0 END) AS `{$aliasToko}`";
            }
            // Filter total juga harus spesifik kategori
            $selectFields[] = "SUM(CASE WHEN j.category_id = {$category->id} THEN IFNULL(j.nominal, 0) ELSE 0 END) AS TOTAL";

            // 2. Query Utama
            $categoryData = DB::table(DB::raw($bulanSubquery))
                ->crossJoin(DB::raw("(SELECT DISTINCT YEAR(periode_bulan) AS tahun FROM jsm WHERE periode_bulan IS NOT NULL) AS m_tahun"))
                // JOIN transaksi (j) dengan kondisi filter kategori sudah dilakukan di sini
                ->leftJoin('jsm as j', function($join) use ($category) {
                    $join->on(DB::raw('MONTH(j.periode_bulan)'), '=', 'm_bulan.id_bulan')
                        ->on(DB::raw('YEAR(j.periode_bulan)'), '=', 'm_tahun.tahun')
                        ->where('j.category_id', '=', $category->id); // <--- FILTER KATEGORI HARUS DI SINI
                })
                ->leftJoin('jsm_toko as jt', 'j.id', '=', 'jt.jsm_id')
                ->leftJoin('tokos as tk', 'jt.toko_id', '=', 'tk.id')
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

        // Load view
        $pdf = Pdf::loadView('jsm.exports_excel', compact('data', 'isDetail', 'year', 'month', 'stores'));

        if($year && $month){
            return $pdf->setPaper('A4', 'landscape')->stream('jsm-report' . $year . '-' . $month .'.pdf');
        } else{
            return $pdf->setPaper('A4', 'landscape')->stream('jsm-report-all.pdf');
        }
    }
}
