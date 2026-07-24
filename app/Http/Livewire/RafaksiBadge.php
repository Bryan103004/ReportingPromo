<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RafaksiBadge extends Component
{

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
        $data = DB::table('rafaksis')
                ->select([
                    DB::raw("SUM(CASE WHEN periode_akhir > '" . Carbon::now() . "' AND periode_bulan IS NOT NULL THEN 1 ELSE 0 END) as `aktif`"),
                    DB::raw("SUM(CASE WHEN periode_akhir <= '" . Carbon::now() . "' AND periode_bulan IS NULL THEN 1 ELSE 0 END) as `expired`"),
                    DB::raw("SUM(CASE WHEN periode_akhir <= '" . Carbon::now() . "' AND periode_bulan IS NOT NULL THEN 1 ELSE 0 END) as `done`")
                ])
                ->first();

        return view('livewire.rafaksi-badge', compact('data'));
    }
}
