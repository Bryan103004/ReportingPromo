<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RafaksiCard extends Component
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
            <span class="text-gray-500 font-medium">Memuat data Rafaksi...</span>
        </div>
        HTML;
    }


    public function render()
    {
        $data = DB::table('rafaksis as r')
            ->select(
                DB::raw('YEAR(r.periode_bulan) as year'),
                DB::raw('MONTH(r.periode_bulan) as month'),
                DB::raw('SUM(r.nominal) as nominal'),
                DB::raw('COUNT(r.id) as total_dokumen') 
            )
            // 1. Join ke tabel pivot rafaksi_toko dulu
            ->leftJoin('rafaksi_toko as rt', 'r.id', '=', 'rt.rafaksi_id')
            // 2. Baru join ke tabel tokos
            ->leftJoin('tokos as tk', 'rt.toko_id', '=', 'tk.id')
            ->whereYear('r.periode_bulan', $this->selectedYear)
            
            // 3. Filter berdasarkan tokoId lewat tabel pivot
            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('rt.toko_id', $tokoId);
            })
            ->when($this->filterByPt, function($q) {
                $q->where('tk.nama_pt', 'PT. MITRA BELANJA ANDA');
            })
            
            // 4. Pembatasan akses user berdasarkan toko lewat tabel pivot
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('rt.toko_id', $allowed);
                }
            })
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('livewire.rafaksi-card', compact('data'));
    }
}