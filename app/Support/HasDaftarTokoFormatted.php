<?php

namespace App\Support;

trait HasDaftarTokoFormatted
{
    public function getDaftarTokoFormattedAttribute()
    {
        // Jika tidak ada toko yang berelasi, kembalikan strip
        if ($this->tokos->isEmpty()) {
            return '-';
        }

        // Kalau cuma 1 toko yang dipilih, tampilkan nama toko itu sendiri (contoh: "GL PARAGON", "GL SCBD")
        if ($this->tokos->count() === 1) {
            return $this->tokos->first()->nama_toko;
        }

        // Ambil kode / singkatan PT unik untuk setiap toko yang dipilih
        $ptCodes = $this->tokos->map(function ($toko) {
            $upper = strtoupper(trim($toko->nama_pt ?? ''));
            if (str_contains($upper, 'MITRA BELANJA ANDA')) {
                return 'MBA';
            }
            if (str_contains($upper, 'HOKKY TE IT') || str_contains($upper, 'TE IT')) {
                return 'HTI';
            }
            if (str_contains($upper, 'HOKKY BERSAMA')) {
                return 'HB';
            }
            if (str_contains($upper, 'LUCKY STRATEGIS')) {
                return 'LS';
            }
            if (str_contains($upper, 'BODEGA BALI')) {
                return 'BBB';
            }
            return $upper ?: '-';
        })->unique()->values();

        // 1. Jika SEMUA toko yang dipilih berasal dari PT yang sama (misal sama-sama HTI, atau sama-sama MBA, dsb)
        if ($ptCodes->count() === 1) {
            return $ptCodes->first();
        }

        // 2. Jika toko yang dipilih lintas PT:
        // Pokoknya kalau tokonya lintas PT tapi ada GL SCBD / PT. LUCKY STRATEGIS (LS),
        // atau gabungan grup HTI + HB, maka claim by jatuh ke "LS" (Lucky Strategis).
        $hasLsStore = $this->tokos->contains(function ($toko) {
            $namaPt = strtoupper(trim($toko->nama_pt ?? ''));
            $namaToko = strtoupper(trim($toko->nama_toko ?? ''));
            return str_contains($namaPt, 'LUCKY STRATEGIS') || str_contains($namaToko, 'SCBD');
        });

        if ($hasLsStore) {
            return 'LS';
        }

        // 3. Spesial untuk grup LS non-SCBD (misal gabungan HTI + HB):
        $lsGroup = ['LS', 'HTI', 'HB'];
        $allInLsGroup = $ptCodes->every(fn ($code) => in_array($code, $lsGroup, true));
        if ($allInLsGroup) {
            return 'LS';
        }

        // 4. Jika ada kombinasi lintas PT lainnya tanpa GL SCBD (misal HTI + MBA),
        // fallback tampilkan daftar nama toko dipisah koma.
        return $this->tokos->pluck('nama_toko')->implode(', ');
    }
}
