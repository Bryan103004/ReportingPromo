<?php

namespace App\Http\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class RafaksiCard extends Component
{
    public $selectedYear;

    public function mount()
    {
        $this->selectedYear = Carbon::now()->year; 
    }

    // TAMBAHKAN FUNGSI INI (Magic Hook Livewire 2)
    // Fungsi ini akan otomatis terpanggil saat dropdown berubah
    public function updatedSelectedYear($value)
    {
        // Sengaja dibiarkan kosong, ini hanya untuk memaksa Livewire 
        // melakukan re-render secara instan saat tahun dipilih.
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
            ->whereYear('r.periode_bulan', $this->selectedYear)
            ->groupBy('year','month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return view('livewire.rafaksi-card', compact('data'));
    }
}