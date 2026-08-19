<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PwpDashboard extends Component
{
    use WithPagination;
    public $tokoId = null;
    public $filterByPt = false;
    public $perPage = 10;

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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center p-6 bg-white rounded shadow">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <span class="text-gray-500 font-medium">Memuat data PWP...</span>
        </div>
        HTML;
    }

    public function paginationView()
    {
        return 'vendor.pagination.livewire-tailwind';
    }

    public function render()
    {
        $query = DB::table('pwps as p')
            ->join('pwp_toko as pt', 'p.id', '=', 'pt.pwp_id')
            ->leftJoin('tokos as tk', 'pt.toko_id', '=', 'tk.id')
            ->leftJoin('regions as rg', 'tk.region_id', '=', 'rg.id')
            ->leftJoin('categories as ct', 'p.category_id', '=', 'ct.id')
            ->select([
                DB::raw("DATE_FORMAT(p.periode_bulan, '%Y-%m') AS Periode_Pengerjaan"),
                'rg.nama_region',
                'tk.nama_toko',
                'p.no_raf',
                'p.supplier_code',
                'p.supplier_name',
                'ct.nama_kategori',
                'p.periode_awal',
                'p.periode_akhir',
                'p.nominal',
                'p.remarks',
                'p.id',
                DB::raw('YEAR(p.periode_bulan) as year'),
                DB::raw('MONTH(p.periode_bulan) as month')
            ])
            ->whereRaw('p.periode_akhir <= NOW()')
            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('pt.toko_id', $tokoId);
            })
            ->when($this->filterByPt, function($q) {
                $q->where('tk.nama_pt', 'PT. MITRA BELANJA ANDA');
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('pt.toko_id', $allowed);
                }
            })
            ->orderBy('p.periode_bulan', 'asc');

        $perPage = $this->perPage == -1 ? max((clone $query)->count(), 1) : $this->perPage;
        $data = $query->paginate($perPage);

        return view('livewire.pwp-dashboard', compact('data'));
    }
}
