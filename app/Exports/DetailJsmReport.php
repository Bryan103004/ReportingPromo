<?php
namespace App\Exports;

use App\Models\Jsm;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DetailJsmReport implements FromView, ShouldAutoSize, WithStyles
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
            $data = Jsm::with('tokos')
                ->whereYear('periode_bulan', $this->year)
                ->whereMonth('periode_bulan', $this->month)
                ->orderBy('periode_bulan', 'desc')
                ->get();
                
            $isDetail = true;
        } 
        // MODE 2: Jika kosong (Export Rekap All)
        else {
            $data = Jsm::selectRaw('
                    YEAR(periode_bulan) as year, 
                    MONTH(periode_bulan) as month, 
                    COUNT(*) as total_data, 
                    SUM(nominal) as total_nominal
                ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();
                
            $isDetail = false;
        }

        // Lempar data ke satu file Blade yang sama
        return view('jsm.exports_excel', [
            'data' => $data,
            'isDetail' => $isDetail,
            'year' => $this->year,
            'month' => $this->month
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