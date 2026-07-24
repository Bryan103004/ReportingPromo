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
        $data = DB::table('locs as lc')
            ->select(
                DB::raw('YEAR(lc.periode_bulan) as year'),
                DB::raw('MONTH(lc.periode_bulan) as month'),
                DB::raw('SUM(lc.nominal) as nominal'),
                DB::raw('COUNT(lc.id) as total_dokumen')
            )
            ->whereYear('lc.periode_bulan', $this->selectedYear)
            ->groupBy('year','month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('livewire.loc-card', compact('data'));
    }
}
