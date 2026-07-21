<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class JsmDashboard extends Component
{

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center p-6 bg-white rounded shadow">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <span class="text-gray-500 font-medium">Memuat data JSM...</span>
        </div>
        HTML;
    }

    public function render()
    {
        $data = DB::table('jsm as j')
            ->join('jsm_toko as jt', 'j.id', '=', 'jt.jsm_id')
            ->leftJoin('tokos as tk', 'jt.toko_id', '=', 'tk.id')
            ->leftJoin('regions as rg', 'tk.region_id', '=', 'rg.id')
            ->leftJoin('categories as ct', 'j.category_id', '=', 'ct.id')
            ->select([
                DB::raw("DATE_FORMAT(j.periode_bulan, '%Y-%m') AS Periode_Pengerjaan"),
                'rg.nama_region',
                'tk.nama_toko',
                'j.no_raf',
                'j.supplier_code',
                'j.supplier_name',
                'ct.nama_kategori',
                'j.periode_awal',
                'j.periode_akhir',
                'j.nominal',
                'j.remarks'
            ])
            // Optimasi: mengurutkan langsung dari kolom tanggal asli j.periode_bulan
            ->orderBy('j.periode_bulan', 'asc') 
            ->customPaginate();

        return view('livewire.jsm-dashboard', compact('data'));
    }
}
