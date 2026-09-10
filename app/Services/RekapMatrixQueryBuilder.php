<?php

namespace App\Services;

class RekapMatrixQueryBuilder
{
    /**
     * Derived table gabungan nilai per-toko yang benar, buat dipakai gantiin
     * join rafaksi_toko/tokos yang lama di query rekap matrix.
     *
     * Dua cabang, saling eksklusif lewat NOT EXISTS jadi aman di-UNION ALL:
     *  - Dokumen yang SUDAH punya item baris (rafaksi_items dst): pakai value
     *    asli per-toko dari {itemStoresTable} (akurat, tidak dobel).
     *  - Dokumen lama yang BELUM punya item sama sekali: tetap pakai cara lama
     *    (nominal penuh dikali jumlah toko yang dipilih lewat pivot), biar data
     *    historis tetap muncul di rekap tanpa perlu backfill.
     *
     * $headerTable/$headerFk/$itemsTable/$itemStoresTable/$itemsFk/$pivotTable
     * semuanya nama tabel/kolom tetap yang dikontrol sendiri (bukan input user),
     * jadi aman diinterpolasi langsung ke SQL.
     */
    public static function combinedStoreValuesSql(
        string $headerTable,
        string $headerFk,
        string $itemsTable,
        string $itemStoresTable,
        string $itemsFk,
        string $pivotTable
    ): string {
        return "(
            SELECT r.category_id AS category_id,
                   MONTH(r.periode_bulan) AS mo,
                   YEAR(r.periode_bulan) AS yr,
                   tk.nama_toko AS toko_nama,
                   ris.value AS val
            FROM {$headerTable} r
            JOIN {$itemsTable} ri ON ri.{$headerFk} = r.id
            JOIN {$itemStoresTable} ris ON ris.{$itemsFk} = ri.id
            JOIN tokos tk ON tk.id = ris.toko_id
            WHERE r.periode_bulan IS NOT NULL

            UNION ALL

            SELECT r.category_id AS category_id,
                   MONTH(r.periode_bulan) AS mo,
                   YEAR(r.periode_bulan) AS yr,
                   tk.nama_toko AS toko_nama,
                   r.nominal AS val
            FROM {$headerTable} r
            JOIN {$pivotTable} rt ON rt.{$headerFk} = r.id
            JOIN tokos tk ON tk.id = rt.toko_id
            WHERE r.periode_bulan IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM {$itemsTable} ri2 WHERE ri2.{$headerFk} = r.id)
        ) AS csv";
    }
}
