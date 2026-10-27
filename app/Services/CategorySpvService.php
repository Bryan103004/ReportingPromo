<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategorySignatory;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

class CategorySpvService
{
    /**
     * Pilih Prepared By & Acknowledged By buat kategori ini, berdasarkan daftar
     * SPV yang diatur lewat halaman Pengaturan SPV Kategori (bukan hardcoded lagi).
     *
     * Prioritas Prepared By:
     * 1. Orang yang BIKIN record ini ($document, dicari dari activity_log action 'create')
     *    -- asal dia kebetulan salah satu SPV kategori ini.
     * 2. Kalau pembuatnya gak ketemu / bukan SPV, fallback ke SPV pertama di daftar kategori.
     *    (Tidak fallback ke "siapa yang lagi login" -- di alur nyatanya yang buka halaman
     *    print selalu role AR/OI, bukan SPV, jadi fallback itu gak pernah efektif kepake.)
     *
     * @return array{0: ?User, 1: ?User}
     */
    public static function resolveSignatories(Category $category, $document = null): array
    {
        $preparedIds = CategorySignatory::where('category_id', $category->id)
            ->where('role', 'prepared_by')
            ->pluck('user_id')
            ->all();

        $acknowledgedIds = CategorySignatory::where('category_id', $category->id)
            ->where('role', 'acknowledged_by')
            ->pluck('user_id')
            ->all();

        $prepared_by = null;

        if ($document) {
            $creatorId = static::findCreatorUserId($document);
            if ($creatorId && in_array($creatorId, $preparedIds, true)) {
                $prepared_by = User::find($creatorId);
            }
        }

        if (! $prepared_by && ! empty($preparedIds)) {
            $prepared_by = User::find($preparedIds[0]);
        }

        $acknowledged_by = ! empty($acknowledgedIds) ? User::find($acknowledgedIds[0]) : null;

        return [$prepared_by, $acknowledged_by];
    }

    /**
     * Cari user_id yang bikin record ini, dari activity_log (action 'create') --
     * tabel rafaksis/jsm/pwps/locs sendiri gak punya kolom created_by.
     */
    public static function findCreatorUserId($document): ?int
    {
        $log = Activity::where('subject_type', get_class($document))
            ->where('subject_id', $document->id)
            ->whereJsonContains('properties->action', 'create')
            ->orderBy('created_at')
            ->first();

        return $log ? $log->causer_id : null;
    }

    /**
     * Pengecekan hak akses untuk tombol "Sudah Siap / Belum Siap Kirim ke AR".
     * User yang memiliki permission 'can_update' (termasuk role Buyer) atau role admin/superadmin
     * memiliki wewenang untuk mengubah status kesiapan dokumen ke AR.
     */
    public static function userCanToggleReadiness(?User $user, ?Category $category = null): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        return $user->can('can_update');
    }
}
