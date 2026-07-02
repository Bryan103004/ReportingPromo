<?php

namespace App\Http\Controllers;

use App\Exports\DetailRafaksiReport;
use App\Models\Rafaksi;
use App\Models\SupplierRafaksi;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class RafaksiController extends Controller
{
    //
    public function index()
    {
        $rafaksiGroups = Rafaksi::selectRaw('
                store,
                YEAR(periode_akhir) as year, 
                MONTH(periode_akhir) as month, 
                COUNT(*) as total_data, 
                SUM(nominal) as total_nominal
            ')
            ->groupBy('store', 'year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        return view('rafaksi.index', compact('rafaksiGroups'));
    }

    public function showMonth($year, $month)
    {
        $rafaksis = Rafaksi::whereYear('periode_akhir', $year)
            ->whereMonth('periode_akhir', $month)
            ->orderBy('periode_akhir', 'desc') // Urutkan dari tanggal terbaru di bulan tersebut
            ->get(); 

        $periodeTitle = Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y');

        return view('rafaksi.show_month', compact('rafaksis', 'periodeTitle', 'year', 'month'));
    }

    public function create(){
        $supplierRafaksi = SupplierRafaksi::all();
        return view('rafaksi.create', compact('supplierRafaksi'));
    }

    public function store(Request $request){
        $request->validate([
            'supplier_code' => 'string|required',
            'supplier_name' => 'string|required',
            'periode_awal' => 'date|required',
            'periode_akhir' => 'date|required',
            'no_raf' => 'string|required',
            'store' => 'string|required',
            'nominal' => 'numeric|min:0|required',
        ]);

        $rafaksi = Rafaksi::create($request->all());

        ActivityLogger::logCreate(
            $rafaksi,
            $rafaksi->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'store', 'nominal']),
            "Created Master Rafaksi #{$rafaksi->id}: {$rafaksi->supplier_name} with Nominal {$rafaksi->nominal}"
        );

        return redirect()->route('rafaksi.index')->with('success', 'Data Rafaksi berhasil disimpan.');
    }

    public function edit(Rafaksi $rafaksi){
        return view('rafaksi.edit', compact('rafaksi'));
    }

    public function update(Request $request, Rafaksi $rafaksi){
        $request->validate([
            'supplier_code' => 'string|required',
            'supplier_name' => 'string|required',
            'periode_awal' => 'date|required',
            'periode_akhir' => 'date|required',
            'no_raf' => 'string|required',
            'store' => 'string|required',
            'nominal' => 'numeric|min:0|required',
        ]);

        $rafaksi->update($request->all());

        ActivityLogger::logUpdate(
            $rafaksi,
            $rafaksi->id,
            $request->only(['supplier_code', 'supplier_name', 'periode_awal', 'periode_akhir', 'no_raf', 'store', 'nominal']),
            "Updated Master Rafaksi #{$rafaksi->id}: {$rafaksi->supplier_name} with Nominal {$rafaksi->nominal}"
        );

        return redirect()->route('rafaksi.index')->with('success', 'Data Rafaksi berhasil diperbarui.');
    }

    public function destroy(Rafaksi $rafaksi){
        $rafaksi->delete();

        ActivityLogger::logDelete(
            $rafaksi,
            $rafaksi->id,
            ['supplier_name' => $rafaksi->supplier_name, 'nominal' => $rafaksi->nominal],
            "Deleted Master Rafaksi #{$rafaksi->id}: {$rafaksi->supplier_name} with Nominal {$rafaksi->nominal}"
        );

        return redirect()->route('rafaksi.index')->with('success', 'Data Rafaksi berhasil dihapus.');
    }

    public function show(Rafaksi $rafaksi){
        return view('rafaksi.show', compact('rafaksi'));
    }

    public function exportCsv(Request $request)
    {
        $fileName = 'export_rafaksi.csv';
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
            $fileName = "detail_rafaksi_{$year}_{$month}.csv";
            
            $columns = ['No', 'No. RAF', 'Kode Supplier', 'Nama Supplier', 'Store', 'Periode Awal', 'Periode Akhir', 'Nominal'];
            
            $rafaksis = Rafaksi::whereYear('periode_awal', $year)
                ->whereMonth('periode_awal', $month)
                ->orderBy('periode_awal', 'desc')
                ->get();

            foreach ($rafaksis as $index => $row) {
                $data[] = [
                    $index + 1,
                    $row->no_raf,
                    $row->supplier_code,
                    $row->supplier_name,
                    $row->store,
                    $row->periode_awal,
                    $row->periode_akhir,
                    $row->nominal
                ];
            }
        } 
        // MODE 2: Jika tidak ada parameter (Export Summary dari Index)
        else {
            $fileName = "rekap_rafaksi_all.csv";
            $columns = ['Tahun', 'Bulan', 'Total Transaksi', 'Total Nominal'];

            $rafaksiGroups = Rafaksi::selectRaw('
                    YEAR(periode_awal) as year, 
                    MONTH(periode_awal) as month, 
                    COUNT(*) as total_data, 
                    SUM(nominal) as total_nominal
                ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            foreach ($rafaksiGroups as $group) {
                $data[] = [
                    $group->year,
                    $group->month,
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

        // Tentukan nama file secara dinamis berdasarkan parameter
        if ($year && $month) {
            $fileName = 'Detail_Rafaksi_Report_'. $year . '_' . $month . '.xlsx';
        } else {
            $fileName = 'Rekap_Rafaksi_Report_All.xlsx';
        }

        // PENTING: Masukkan $year dan $month ke dalam kurung kelas export-nya
        return Excel::download(new DetailRafaksiReport($year, $month), $fileName);
    }
}
