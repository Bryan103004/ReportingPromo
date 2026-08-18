<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RafaksiBadge extends Component
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
            <span class="text-gray-500 font-medium">Memuat data Rafaksi...</span>
        </div>
        HTML;
    }

    public function render()
    {
        $today = date('Y-m-d'); // Ambil tanggal hari ini saja (Y-m-d)

        $data = DB::table('rafaksis as r')
            ->select([
                DB::raw("SUM(CASE WHEN r.periode_akhir > '{$today}' AND r.periode_bulan IS NOT NULL THEN 1 ELSE 0 END) as `aktif`"),
                DB::raw("SUM(CASE WHEN r.periode_akhir <= '{$today}' AND r.periode_bulan IS NULL THEN 1 ELSE 0 END) as `expired`"),
                DB::raw("SUM(CASE WHEN r.periode_akhir <= '{$today}' AND r.periode_bulan IS NOT NULL THEN 1 ELSE 0 END) as `done`")
            ])
            ->leftJoin('rafaksi_toko as rt', 'r.id', '=', 'rt.rafaksi_id')
            ->leftJoin('tokos as tk', 'rt.toko_id', '=', 'tk.id')
            
            // Masukkan filter tambahan jika card ini butuh difilter berdasarkan toko/user seperti sebelumnya:
            ->when($this->tokoId, function($q, $tokoId) {
                $q->where('rt.toko_id', $tokoId);
            })
            ->when(! auth()->user()->hasGlobalCompanyAccess(), function($q) {
                $allowed = auth()->user()->accessibleTokoIds()->toArray();
                if (empty($allowed)) {
                    $q->whereRaw('0 = 1');
                } else {
                    $q->whereIn('rt.toko_id', $allowed);
                }
            })
            ->first();

        return view('livewire.rafaksi-badge', compact('data'));
    }
}
