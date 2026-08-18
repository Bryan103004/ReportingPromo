<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class LocCard extends Component
{   

    public $selectedYear;

    // 2. Set nilai default saat komponen pertama kali dimuat
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
            <span class="text-gray-500 font-medium">Memuat data Loc...</span>
        </div>
        HTML;
    }

    public function render()
    {
        $data = DB::table('locs as lc')
            ->select(
                DB::raw('YEAR(lc.periode_bulan) as year'),
                DB::raw('MONTH(lc.periode_bulan) as month'),
                DB::raw('SUM(lc.nominal) as nominal'),
                DB::raw('COUNT(lc.id) as total_dokumen')
            )
            // 1. Join ke tabel pivot locs_toko dulu
            ->leftJoin('locs_toko as lt', 'lc.id', '=', 'lt.loc_id')
            // 2. Baru join ke tabel tokos
            ->leftJoin('tokos as tk', 'lt.toko_id', '=', 'tk.id')
            ->whereYear('lc.periode_bulan', $this->selectedYear)
            // Optimasi: mengurutkan langsung dari kolom tanggal asli j.periode_bulan
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
            ->groupBy('year','month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('livewire.loc-card', compact('data'));
    }
}
