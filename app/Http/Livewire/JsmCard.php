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
        $data = DB::table('jsm as j')
                ->select(
                    DB::raw('YEAR(j.periode_bulan) as year'),
                    DB::raw('MONTH(j.periode_bulan) as month'),
                    DB::raw('SUM(j.nominal) as nominal'),
                    DB::raw('COUNT(j.id) as total_dokumen')
                )
                // 1. Join ke tabel pivot jsm_toko dulu
                ->leftJoin('jsm_toko as jt', 'j.id', '=', 'jt.jsm_id')
                // 2. Baru join ke tabel tokos
                ->leftJoin('tokos as tk', 'jt.toko_id', '=', 'tk.id')
                ->whereYear('j.periode_bulan', $this->selectedYear)
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
                ->groupBy('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        return view('livewire.jsm-card', compact('data'));
    }
}
