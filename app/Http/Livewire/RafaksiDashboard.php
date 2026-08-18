<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class RafaksiDashboard extends Component
{
    use WithPagination;
    public $tokoId = null;
    public $filterByPt = false;

    protected $listeners = ['filterByToko', 'filterByPt'];

    public function filterByToko($tokoId)
    {
        $this->tokoId = $tokoId ?: null;
        $this->resetPage();
    }

    public function filterByPt($val)
    {
        $this->filterByPt = (bool) $val;
        $this->resetPage();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center p-6 bg-white rounded shadow">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <span class="text-gray-500 font-medium">Memuat data Rafaksi...</span>
        </div>
        HTML;
    }

    public function paginationView()
    {
        return 'vendor.pagination.tailwind'; // Sesuaikan dengan nama file view pagination kamu sebelumnya
    }

    public function render()
    {
        $data = DB::table('rafaksis as r')
            ->join('rafaksi_toko as rt', 'r.id', '=', 'rt.rafaksi_id')
            ->leftJoin('tokos as tk', 'rt.toko_id', '=', 'tk.id')
            ->leftJoin('regions as rg', 'tk.region_id', '=', 'rg.id')
            ->leftJoin('categories as ct', 'r.category_id', '=', 'ct.id')
            ->select([
                DB::raw("DATE_FORMAT(r.periode_bulan, '%Y-%m') AS Periode_Pengerjaan"),
                'rg.nama_region',
                'tk.nama_toko',
                'r.no_raf',
                'r.supplier_code',
                'r.supplier_name',
                'ct.nama_kategori',
                'r.periode_awal',
                'r.periode_akhir',
                'r.nominal',
                'r.remarks',
                'r.id',
                DB::raw('YEAR(r.periode_bulan) as year'),
                DB::raw('MONTH(r.periode_bulan) as month')
            ])
            ->whereRaw('r.periode_akhir <= NOW()') 
            // Menggunakan kolom asli untuk optimasi kecepatan database
            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('rt.toko_id', $tokoId);
            })
            ->when($this->filterByPt, function($q) {
                $q->where('tk.nama_pt', 'PT. MITRA BELANJA ANDA');
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('rt.toko_id', $allowed);
                }
            })
            ->orderBy('r.periode_bulan', 'asc') 
            ->customPaginate();

        return view('livewire.rafaksi-dashboard', compact('data'));
    }
}
