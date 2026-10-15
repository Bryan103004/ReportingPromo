<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategorySignatory;
use App\Models\User;

class CategorySpvService
{
    /**
     * Pilih Prepared By & Acknowledged By buat kategori ini, berdasarkan daftar
     * SPV yang diatur lewat halaman Pengaturan SPV Kategori (bukan hardcoded lagi).
     * Prepared By: kalau user yang lagi login ada di daftar SPV kategori ini, pakai
     * dia; kalau enggak (atau bukan siapa-siapa), fallback ke SPV pertama di daftar.
     *
     * @return array{0: ?User, 1: ?User}
     */
    public static function resolveSignatories(Category $category, ?int $authId): array
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
        if (! empty($preparedIds)) {
            $prepId = in_array($authId, $preparedIds, true) ? $authId : $preparedIds[0];
            $prepared_by = User::find($prepId);
        }

        $acknowledged_by = ! empty($acknowledgedIds) ? User::find($acknowledgedIds[0]) : null;

        return [$prepared_by, $acknowledged_by];
    }

    /**
     * Dipakai buat batasi tombol "Sudah Siap / Belum Siap Kirim ke AR" -- cuma SPV
     * buyer (prepared_by) kategori yang bersangkutan yang boleh klik, kecuali
     * admin/superadmin yang selalu boleh. Kalau kategori ini belum ada SPV-nya sama
     * sekali di pengaturan (belum pernah diatur admin), gak usah dibatasi dulu --
     * daripada keyword semua orang ke-lock out dari dokumen yang categorinya belum
     * sempat di-setup.
     */
    public static function userCanToggleReadiness(?User $user, ?Category $category): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        if (! $category) {
            return true;
        }

        $preparedIds = CategorySignatory::where('category_id', $category->id)
            ->where('role', 'prepared_by')
            ->pluck('user_id')
            ->all();

        if (empty($preparedIds)) {
            return true;
        }

        return in_array($user->id, $preparedIds, true);
    }
}
