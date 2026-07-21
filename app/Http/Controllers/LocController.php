<?php

namespace App\Http\Controllers;
use App\Exports\DetailLocReport;
use App\Models\Category;
use App\Models\Loc;
use App\Models\Region;
use App\Models\SupplierRafaksi;
use App\Models\Toko;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class LocController extends Controller
{
    //
    // public function index()
    // {
    //     $locGroups = Loc::selectRaw('
    //             store,  
    //             MAX(YEAR(periode_akhir)) as year, -- Mengambil tahun terbaru dalam grup
    //             MAX(MONTH(periode_akhir)) as month, -- Mengambil bulan terbaru dalam grup
    //             YEAR(periode_bulan) as year_kerja, 
    //             MONTH(periode_bulan) as month_kerja, 
    //             COUNT(*) as total_data, 
    //             SUM(nominal) as total_nominal
    //         ')
    //         ->groupBy('store', 'year_kerja', 'month_kerja')
    //         ->orderBy('year_kerja', 'asc')
    //         ->orderBy('month_kerja', 'asc')
    //         ->customPaginate();

    //     return view('loc.index', compact('locGroups'));
    // }

    public function index(Request $request)
    {
        // 1. Ambil data supplier untuk dikirim ke komponen filter dropdown
        $suppliers = SupplierRafaksi::all();

        // 2. Siapkan Query Dasar
        $query = Loc::selectRaw('
                store,  
                MAX(YEAR(periode_akhir)) as year,
                MAX(MONTH(periode_akhir)) as month,
                YEAR(periode_bulan) as year_kerja, 
                MONTH(periode_bulan) as month_kerja, 
                COUNT(*) as total_data, 
                SUM(nominal) as total_nominal
            ');

        // 3. Terapkan Filter Jika Ada
        if ($request->filled('supplier_code')) {
            $query->where('supplier_code', $request->supplier_code);
        }
        
        if ($request->filled('start_date')) {
            $query->where('periode_awal', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('periode_akhir', '<=', $request->end_date);
        }

        // 4. Eksekusi Query dengan Group By & Pagination
        $locGroups = $query->groupBy('store', 'year_kerja', 'month_kerja')
            ->orderBy('year_kerja', 'asc')
            ->orderBy('month_kerja', 'asc')
            ->customPaginate();

        // 5. Appends Request (SANGAT PENTING!)
        // Ini agar saat kamu pindah ke Halaman 2, filter tidak hilang/reset
        $locGroups->appends($request->all());

        return view('loc.index', compact('locGroups', 'suppliers'));
    }

    // public function showMonth($year, $month)
    // {
    //     $locs = Loc::with(['tokos'])
    //         ->whereYear('periode_bulan', $year)
    //         ->whereMonth('periode_bulan', $month)
    //         ->orderBy('periode_akhir', 'desc') // Urutkan dari tanggal terbaru di bulan tersebut
    //         ->customPaginate(); 

    //     $periodeTitle = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

    //     return view('loc.show_month', compact('locs', 'periodeTitle', 'year', 'month'));
    // }

    public function showMonth(Request $request, $year, $month)
    {
        // 1. Ambil data supplier untuk dikirim ke komponen filter dropdown
        $suppliers = SupplierRafaksi::all();

        // 2. Siapkan Query Builder Dasar (JANGAN panggil customPaginate di sini)
        $query = Loc::with(['tokos'])
            ->whereYear('periode_bulan', $year)
            ->whereMonth('periode_bulan', $month)
            ->orderBy('periode_akhir', 'desc'); 

        $periodeTitle = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        // 3. Terapkan Filter Jika Ada
        if ($request->filled('supplier_code')) {
            $query->where('supplier_code', $request->supplier_code);
        }
        
        if ($request->filled('start_date')) {
            $query->where('periode_awal', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('periode_akhir', '<=', $request->end_date);
        }

        // 4. Eksekusi Query dengan memanggil Pagination di bagian akhir
        $locs = $query->customPaginate();

        // 5. Appends Request (SANGAT PENTING!)
        // Ini agar saat kamu pindah ke Halaman 2, filter tidak hilang/reset
        $locs->appends($request->all());

        return view('loc.show_month', compact('locs', 'periodeTitle', 'year', 'month', 'suppliers'));
    }

    public function create(){
        $supplierRafaksi = SupplierRafaksi::all();
        $regions = Region::whereNotIn('status',['nonaktif'])->get();
        $categories = Category::all();
        return view('loc.create', compact('supplierRafaksi', 'regions', 'categories'));
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
            'category_id' => 'exists:categories,id',
        ]);

        $loc = Loc::create($request->except('toko_id'));
        $loc->tokos()->sync($request->toko_id);

        ActivityLogger::logCreate(
            $loc,
            $loc->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'nominal', 'toko_id']),
            "Created Loc #{$loc->id}: {$loc->supplier_name} with Nominal {$loc->nominal}"
        );

        return redirect()->route('loc.index')->with('success', 'Data Loc berhasil disimpan.');
    }

    public function edit(Loc $loc){
        $supplierRafaksi = SupplierRafaksi::all();
        $regions = Region::whereNotIn('status',['nonaktif'])->get();
        $categories = Category::all();
        $tokos = Toko::all();

        return view('loc.edit', compact('loc', 'supplierRafaksi', 'regions', 'categories', 'tokos'));
    }

    public function update(Request $request, Loc $loc){
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
            'category_id' => 'exists:categories,id',
        ]);

        $loc->update($request->except('toko_id'));
        $loc->tokos()->sync($request->toko_id);
        
        ActivityLogger::logUpdate(
            $loc,
            $loc->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'nominal', 'toko_id']),
            "Updated Loc #{$loc->id}: {$loc->supplier_name} with Nominal {$loc->nominal}"
        );

        return redirect()->route('loc.index')->with('success', 'Data Loc berhasil diperbarui.');
    }

    public function destroy(Loc $loc){
        $loc->delete();

        ActivityLogger::logDelete(
            $loc,
            $loc->id,
            ['supplier_name' => $loc->supplier_name, 'nominal' => $loc->nominal],
            "Deleted Master Loc #{$loc->id}: {$loc->supplier_name} with Nominal {$loc->nominal}"
        );

        return redirect()->route('loc.index')->with('success', 'Data Loc berhasil dihapus.');
    }

    public function show(Loc $loc){
        return view('loc.show', compact('loc'));
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'export_loc.csv';
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
            $fileName = "detail_loc_{$year}_{$month}.csv";
            
            $columns = ['No', 'No. RAF', 'Kode Supplier', 'Nama Supplier', 'Region', 'Store', 'Periode Awal', 'Periode Akhir', 'Nominal'];
            
            $locs = Loc::with(['tokos'])
                ->whereYear('periode_bulan', $year)
                ->whereMonth('periode_bulan', $month)
                ->orderBy('periode_awal', 'asc')
                ->orderBy('periode_akhir', 'asc')
                ->get();

            foreach ($locs as $index => $row) {
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
                ->leftJoin('locs_toko as lt', 'l.id', '=', 'lt.loc_id')
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
        $stores = Toko::all();
        $year = $request->year;
        $month = $request->month;

        // Tentukan nama file secara dinamis berdasarkan parameter
        if ($year && $month) {
            $fileName = 'Detail_Loc_Report_'. $year . '_' . $month . '.xlsx';
        } elseif ($year) {
            $fileName = 'Rekap_Loc_Report_'. $year . 'xlsx'; 
        } else {
            $fileName = 'Rekap_Loc_Report_All.xlsx';
        }

        // PENTING: Masukkan $year dan $month ke dalam kurung kelas export-nya
        return Excel::download(new DetailLocReport($year, $month, $stores), $fileName);
    }

    public function printPdf(Request $request){
        $stores = Toko::all();
        $year = $request->year;
        $month = $request->month;

        if($year && $month){
            $data = Loc::with(['tokos','categories'])
                    ->whereYear('periode_bulan', $year)
                    ->whereMonth('periode_bulan', $month)
                    ->orderBy('category_id')
                    ->orderBy('periode_awal', 'asc')
                    ->orderBy('periode_akhir', 'asc')
                    ->get();
                    $isDetail = true;
                    $stores = null;
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
                $selectFields = [
                    "'{$category->nama_kategori}' AS Kategori",
                    "m_bulan.id_bulan AS urutan_bulan",
                    "m_tahun.tahun AS Tahun",
                    "m_bulan.nama_bulan AS Periode"
                ];

                foreach ($allStores as $store) {
                    $aliasToko = str_replace('GL ', '', $store->nama_toko);
                    $selectFields[] = "SUM(CASE WHEN tk.nama_toko = '{$store->nama_toko}' THEN l.nominal ELSE 0 END) AS `{$aliasToko}`";
                }
                $selectFields[] = "SUM(IFNULL(l.nominal, 0)) AS TOTAL";

                $categoryData = DB::table(DB::raw($bulanSubquery))
                    ->crossJoin(DB::raw("(SELECT DISTINCT YEAR(periode_bulan) AS tahun FROM locs WHERE periode_bulan IS NOT NULL) AS m_tahun"))
                    ->leftJoin('locs as l', function($join) {
                        $join->on(DB::raw('MONTH(l.periode_bulan)'), '=', 'm_bulan.id_bulan')
                            ->on(DB::raw('YEAR(l.periode_bulan)'), '=', 'm_tahun.tahun');
                    })
                    ->leftJoin('locs_toko as lt', 'l.id', '=', 'lt.loc_id')
                    ->leftJoin('tokos as tk', 'lt.toko_id', '=', 'tk.id')
                    ->leftJoin('categories as ct', function($join) use ($category) {
                        $join->on('l.category_id', '=', 'ct.id')
                            ->where('ct.nama_kategori', '=', $category->nama_kategori);
                    })
                    ->selectRaw(implode(', ', $selectFields))
                    ->where('m_tahun.tahun', $year) 
                    ->groupBy('m_tahun.tahun', 'm_bulan.id_bulan', 'm_bulan.nama_bulan')
                    ->orderBy('m_bulan.id_bulan', 'ASC')
                    ->get();

                $finalData = $finalData->concat($categoryData);

                // Baris Pembatas (Sekat 99)
                $pembatasArray = [
                    'Kategori'     => $category->nama_kategori,
                    'urutan_bulan' => 99,
                    'Tahun'        => null,
                    'Periode'      => "--- AKHIR REKAP {$category->nama_kategori} ---",
                    'TOTAL'        => 0
                ];
                
                foreach ($allStores as $store) {
                    $aliasToko = str_replace('GL ', '', $store->nama_toko);
                    $pembatasArray[$aliasToko] = 0;
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
        $pdf = Pdf::loadView('loc.exports_excel', compact('data', 'isDetail', 'year', 'month', 'stores'));

        if($year && $month){
            return $pdf->setPaper('A4', 'landscape')->stream('loc-report' . $year . '-' . $month .'.pdf');
        } else{
            return $pdf->setPaper('A4', 'landscape')->stream('loc-report-all.pdf');
        }
    }
}
