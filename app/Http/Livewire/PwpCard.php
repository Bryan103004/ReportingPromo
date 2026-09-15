<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PwpCard extends Component
{
    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year;
    }

    public $tokoId = null;

    protected $listeners = ['filterByToko', 'filterByPt'];

    public function filterByToko($tokoId)
    {
        $this->tokoId = $tokoId ?: null;
    }

    public $filterByPt = false;

    public function filterByPt($val)
    {
        $this->filterByPt = (bool) $val;
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


    public function render()
    {
        // Filter toko dilakukan lewat whereExists (bukan leftJoin ke pwp_toko)
        // supaya query tetap 1 baris per dokumen -- kalau di-join langsung,
        // dokumen yang ke-link ke N toko bakal fan-out jadi N baris dan
        // SUM(p.nominal) ikut kehitung N kali.
        $data = DB::table('pwps as p')
            ->select(
                DB::raw('YEAR(p.periode_bulan) as year'),
                DB::raw('MONTH(p.periode_bulan) as month'),
                DB::raw('SUM(p.nominal) as nominal'),
                DB::raw('COUNT(DISTINCT p.id) as total_dokumen')
            )
            ->whereYear('p.periode_bulan', $this->selectedYear)
            ->when($this->tokoId, function($q, $tokoId) {
                $q->whereExists(function($sub) use ($tokoId) {
                    $sub->selectRaw('1')
                        ->from('pwp_toko as pt')
                        ->whereColumn('pt.pwp_id', 'p.id')
                        ->where('pt.toko_id', $tokoId);
                });
            })
            ->when($this->filterByPt, function($q) {
                $q->whereExists(function($sub) {
                    $sub->selectRaw('1')
                        ->from('pwp_toko as pt')
                        ->join('tokos as tk', 'pt.toko_id', '=', 'tk.id')
                        ->whereColumn('pt.pwp_id', 'p.id')
                        ->where('tk.nama_pt', 'PT. MITRA BELANJA ANDA');
                });
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereExists(function($sub) use ($allowed) {
                        $sub->selectRaw('1')
                            ->from('pwp_toko as pt')
                            ->whereColumn('pt.pwp_id', 'p.id')
                            ->whereIn('pt.toko_id', $allowed);
                    });
                }
            })
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('livewire.pwp-card', compact('data'));
    }
}
