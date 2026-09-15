<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class JsmCard extends Component
{
    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year; // Default ke tahun ini (misal: 2026)
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
            <span class="text-gray-500 font-medium">Memuat data JSM...</span>
        </div>
        HTML;
    }

    public function render()
    {
        // Filter toko dilakukan lewat whereExists (bukan leftJoin ke jsm_toko)
        // supaya query tetap 1 baris per dokumen -- kalau di-join langsung,
        // dokumen yang ke-link ke N toko bakal fan-out jadi N baris dan
        // SUM(j.nominal) ikut kehitung N kali.
        $data = DB::table('jsm as j')
                ->select(
                    DB::raw('YEAR(j.periode_bulan) as year'),
                    DB::raw('MONTH(j.periode_bulan) as month'),
                    DB::raw('SUM(j.nominal) as nominal'),
                    DB::raw('COUNT(DISTINCT j.id) as total_dokumen')
                )
                ->whereYear('j.periode_bulan', $this->selectedYear)
                ->when($this->tokoId, function($q, $tokoId) {
                    $q->whereExists(function($sub) use ($tokoId) {
                        $sub->selectRaw('1')
                            ->from('jsm_toko as jt')
                            ->whereColumn('jt.jsm_id', 'j.id')
                            ->where('jt.toko_id', $tokoId);
                    });
                })
                ->when($this->filterByPt, function($q) {
                    $q->whereExists(function($sub) {
                        $sub->selectRaw('1')
                            ->from('jsm_toko as jt')
                            ->join('tokos as tk', 'jt.toko_id', '=', 'tk.id')
                            ->whereColumn('jt.jsm_id', 'j.id')
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
                                ->from('jsm_toko as jt')
                                ->whereColumn('jt.jsm_id', 'j.id')
                                ->whereIn('jt.toko_id', $allowed);
                        });
                    }
                })
                ->groupBy('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        return view('livewire.jsm-card', compact('data'));
    }
}
