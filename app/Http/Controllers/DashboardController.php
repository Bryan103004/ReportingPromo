<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use App\Models\Toko;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // Ambil daftar toko yang user boleh lihat (atau semua jika admin)
        $query = Toko::whereNotIn('status', ['nonaktif']);
        if (! auth()->user()->hasGlobalCompanyAccess()) {
            $ids = auth()->user()->accessibleTokoIds()->toArray();
            if (empty($ids)) {
                $tokos = collect();
            } else {
                $tokos = $query->whereIn('id', $ids)->get(['id', 'nama_toko']);
            }
        } else {
            $tokos = $query->get(['id', 'nama_toko']);
        }

        return view('dashboard', compact('tokos'));
    }
}
