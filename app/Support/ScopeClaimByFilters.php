<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait ScopeClaimByFilters
{
    /**
     * Terapkan filter Store tunggal dan filter PT (gabungan) sesuai SOP Claim By:
     * 1. Jika filter Store (toko_id) dipilih:
     *    HANYA menampilkan dokumen yang benar-benar SATU TOKO (single store) dan toko tersebut adalah toko_id yang dipilih.
     * 2. Jika filter PT (filter_pt) dipilih:
     *    HANYA menampilkan dokumen GABUNGAN (lebih dari 1 toko) yang klaimnya jatuh ke PT tersebut:
     *    - 'PT. LUCKY STRATEGIS'    -> Gabungan (>1 toko) yang ada GL SCBD / PT. LUCKY STRATEGIS, ATAU gabungan grup HTI + HB.
     *    - 'PT. HOKKY TE IT'        -> Gabungan (>1 toko) yang semuanya HOKKY TE IT (HTI).
     *    - 'PT. MITRA BELANJA ANDA' -> Gabungan (>1 toko) yang semuanya MBA.
     *    - 'PT. HOKKY BERSAMA'      -> Gabungan (>1 toko) yang semuanya HOKKY BERSAMA (HB).
     *    - PT lainnya               -> Gabungan (>1 toko) yang semua tokonya dari PT tersebut.
     */
    public function scopeApplyStoreAndPtFilter(Builder $query, $tokoId = null, $filterPt = null): Builder
    {
        // Kasus 1: Filter Store dipilih
        if (! empty($tokoId)) {
            $query->has('tokos', '=', 1)
                  ->whereHas('tokos', function (Builder $q) use ($tokoId) {
                      $q->where('tokos.id', $tokoId);
                  });
        }

        // Kasus 2: Filter PT dipilih
        if (! empty($filterPt)) {
            $query->has('tokos', '>', 1);

            $upperPt = strtoupper(trim($filterPt));

            if (str_contains($upperPt, 'LUCKY STRATEGIS')) {
                // Klaim LS adalah dokumen gabungan (>1 toko) yang:
                // - Memiliki toko dari PT. LUCKY STRATEGIS / GL SCBD
                // - ATAU gabungan grup non-LS yaitu HTI + HB (Hokky Te It + Hokky Bersama)
                $query->where(function (Builder $q) {
                    $q->whereHas('tokos', function ($sq) {
                        $sq->where('tokos.nama_pt', 'PT. LUCKY STRATEGIS')
                           ->orWhere('tokos.nama_toko', 'like', '%SCBD%');
                    })->orWhere(function (Builder $sq2) {
                        $sq2->whereDoesntHave('tokos', function ($s) {
                            $s->whereNotIn('tokos.nama_pt', ['PT. HOKKY TE IT', 'PT. HOKKY BERSAMA']);
                        })->whereHas('tokos', fn ($s) => $s->where('tokos.nama_pt', 'PT. HOKKY TE IT'))
                          ->whereHas('tokos', fn ($s) => $s->where('tokos.nama_pt', 'PT. HOKKY BERSAMA'));
                    });
                });
            } else {
                // PT lainnya (misal PT. HOKKY TE IT, PT. MITRA BELANJA ANDA, dsb):
                // Semua toko yang dipilih harus berasal dari PT tersebut.
                $query->whereDoesntHave('tokos', function (Builder $q) use ($filterPt) {
                    $q->where('tokos.nama_pt', '!=', $filterPt);
                });
            }
        }

        return $query;
    }
}
