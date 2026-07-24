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
        $data = DB::table('jsm as j')
                ->select(
                    DB::raw('YEAR(j.periode_bulan) as year'),
                    DB::raw('MONTH(j.periode_bulan) as month'),
                    DB::raw('SUM(j.nominal) as nominal'),
                    DB::raw('COUNT(j.id) as total_dokumen')
                )
                ->whereYear('j.periode_bulan', $this->selectedYear)
                ->groupBy('year','month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

        return view('livewire.jsm-card', compact('data'));
    }
}
