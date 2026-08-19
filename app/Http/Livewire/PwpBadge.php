<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PwpBadge extends Component
{
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
        $today = date('Y-m-d'); // Ambil tanggal hari ini saja (Y-m-d)

        $data = DB::table('pwps as p')
            ->select([
                DB::raw("SUM(CASE WHEN p.periode_akhir > '{$today}' AND p.periode_bulan IS NOT NULL THEN 1 ELSE 0 END) as `aktif`"),
                DB::raw("SUM(CASE WHEN p.periode_akhir <= '{$today}' AND p.periode_bulan IS NULL THEN 1 ELSE 0 END) as `expired`"),
                DB::raw("SUM(CASE WHEN p.periode_akhir <= '{$today}' AND p.periode_bulan IS NOT NULL THEN 1 ELSE 0 END) as `done`")
            ])
            ->leftJoin('pwp_toko as pt', 'p.id', '=', 'pt.pwp_id')
            ->leftJoin('tokos as tk', 'pt.toko_id', '=', 'tk.id')

            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('pt.toko_id', $tokoId);
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('pt.toko_id', $allowed);
                }
            })
            ->first();

        return view('livewire.pwp-badge', compact('data'));
    }
}
