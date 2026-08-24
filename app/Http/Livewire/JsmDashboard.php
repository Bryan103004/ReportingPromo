<?php

namespace App\Http\Livewire;

use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class JsmDashboard extends Component
{
    use WithPagination;
    public $tokoId = null;
    public $perPage = 10;

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

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function placeholder()
    {
        return <<<'HTML'
        <div class="flex items-center justify-center p-6 bg-white rounded shadow">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mr-3"></div>
            <span class="text-gray-500 font-medium">Memuat data JSM...</span>
        </div>
        HTML;
    }

    public function paginationView()
    {
        return 'vendor.pagination.livewire-tailwind';
    }

    public function render()
    {
        $query = DB::table('jsm as j')
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
                'j.remarks',
                'j.id',
                DB::raw('YEAR(j.periode_bulan) as year'),
                DB::raw('MONTH(j.periode_bulan) as month')
            ])
            ->whereRaw('j.periode_akhir <= NOW()') 
            // Optimasi: mengurutkan langsung dari kolom tanggal asli j.periode_bulan
            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('jt.toko_id', $tokoId);
            })
            ->when($this->filterByPt, function($q) {
                $q->where('tk.nama_pt', 'PT. MITRA BELANJA ANDA');
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('jt.toko_id', $allowed);
                }
            })
            ->orderBy('j.periode_bulan', 'asc')
            ->orderBy('j.periode_akhir', 'asc');
            

        $perPage = $this->perPage == -1 ? max((clone $query)->count(), 1) : $this->perPage;
        $data = $query->paginate($perPage);

        return view('livewire.jsm-dashboard', compact('data'));
    }
}
