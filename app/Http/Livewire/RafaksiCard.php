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
        // Filter toko dilakukan lewat whereExists (bukan leftJoin ke rafaksi_toko)
        // supaya query tetap 1 baris per dokumen -- kalau di-join langsung,
        // dokumen yang ke-link ke N toko bakal fan-out jadi N baris dan
        // SUM(r.nominal) ikut kehitung N kali (dokumen 1 toko kelipatan 10
        // misalnya, nominal-nya kebaca 10x lebih besar dari aslinya).
        $data = DB::table('rafaksis as r')
            ->select(
                DB::raw('YEAR(r.periode_bulan) as year'),
                DB::raw('MONTH(r.periode_bulan) as month'),
                DB::raw('SUM(r.nominal) as nominal'),
                DB::raw('COUNT(DISTINCT r.id) as total_dokumen')
            )
            ->whereYear('r.periode_bulan', $this->selectedYear)
            ->when($this->tokoId, function($q, $tokoId) {
                $q->whereExists(function($sub) use ($tokoId) {
                    $sub->selectRaw('1')
                        ->from('rafaksi_toko as rt')
                        ->whereColumn('rt.rafaksi_id', 'r.id')
                        ->where('rt.toko_id', $tokoId);
                });
            })
            ->when($this->filterByPt, function($q) {
                $q->whereExists(function($sub) {
                    $sub->selectRaw('1')
                        ->from('rafaksi_toko as rt')
                        ->join('tokos as tk', 'rt.toko_id', '=', 'tk.id')
                        ->whereColumn('rt.rafaksi_id', 'r.id')
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
                            ->from('rafaksi_toko as rt')
                            ->whereColumn('rt.rafaksi_id', 'r.id')
                            ->whereIn('rt.toko_id', $allowed);
                    });
                }
            })
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('livewire.rafaksi-card', compact('data'));
    }
}