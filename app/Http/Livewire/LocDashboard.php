<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
class LocDashboard extends Component
{
    use WithPagination;
    public $tokoId = null;

    protected $listeners = ['filterByToko', 'filterByPt'];

    public function filterByToko($tokoId)
    {
        $this->tokoId = $tokoId ?: null;
        $this->resetPage();
    }

    public $filterByPt = false;

    public function filterByPt($val)
    {
        $this->filterByPt = (bool) $val;
        $this->resetPage();
    }


    public function paginationView()
    {
        return 'vendor.pagination.tailwind'; // Sesuaikan dengan nama file view pagination kamu sebelumnya
    }

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
                'lc.remarks',
                'lc.id',
                DB::raw('YEAR(lc.periode_bulan) as year'),
                DB::raw('MONTH(lc.periode_bulan) as month')
            ])
            ->whereRaw('lc.periode_akhir <= NOW()')
            // Mengurutkan kronologis secara efisien lewat kolom tanggal asli
            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('lt.toko_id', $tokoId);
            })
            ->when($this->filterByPt, function($q) {
                $q->where('tk.nama_pt', 'PT. MITRA BELANJA ANDA');
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('lt.toko_id', $allowed);
                }
            })
            ->orderBy('lc.periode_bulan', 'asc') 
            ->customPaginate();

        return view('livewire.loc-dashboard', compact('data'));
    }
}
