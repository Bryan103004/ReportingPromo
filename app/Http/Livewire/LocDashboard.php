<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LocDashboard extends Component
{

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center p-6 bg-white rounded shadow">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <span class="text-gray-500 font-medium">Memuat data LOC...</span>
        </div>
        HTML;
    }

    public function render()
    {
        $data = DB::table('locs as lc')
            ->join('locs_toko as lt', 'lc.id', '=', 'lt.loc_id')
            ->leftJoin('tokos as tk', 'lt.toko_id', '=', 'tk.id')
            ->leftJoin('regions as rg', 'tk.region_id', '=', 'rg.id')
            ->leftJoin('categories as ct', 'lc.category_id', '=', 'ct.id')
            ->select([
                DB::raw("DATE_FORMAT(lc.periode_bulan, '%Y-%m') AS Periode_Pengerjaan"),
                'rg.nama_region',
                'tk.nama_toko',
                'lc.no_raf',
                'lc.supplier_code',
                'lc.supplier_name',
                'ct.nama_kategori',
                'lc.periode_awal',
                'lc.periode_akhir',
                'lc.nominal',
                'lc.remarks'
            ])
            // Mengurutkan kronologis secara efisien lewat kolom tanggal asli
            ->orderBy('lc.periode_bulan', 'asc') 
            ->customPaginate();

        return view('livewire.loc-dashboard', compact('data'));
    }
}
