<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateCategorySignatoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_signatories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            // 'prepared_by' = SPV buyer kategori ini (juga dipakai buat batasi siapa yang
            // boleh toggle status "Siap Kirim ke AR"). 'acknowledged_by' = atasan yang
            // nge-acknowledge dokumen cetak.
            $table->enum('role', ['prepared_by', 'acknowledged_by']);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['category_id', 'role', 'user_id']);
        });

        // Backfill dari mapping lama yang sebelumnya hardcoded di
        // UtilityController::resolveCategorySpv(), biar perilaku existing gak berubah
        // begitu fitur ini di-deploy -- admin bisa atur ulang lewat UI abis ini.
        $categories = DB::table('categories')->get(['id', 'nama_kategori']);
        $now = now();
        $rows = [];

        foreach ($categories as $category) {
            $name = strtoupper($category->nama_kategori);

            if (str_contains($name, 'NON')) {
                $prepared = [5, 8];
                $acknowledged = [4];
            } elseif (str_contains($name, 'FOOD')) {
                $prepared = [6, 9];
                $acknowledged = [13];
            } elseif (str_contains($name, 'PERIS') || str_contains($name, 'FRESH')) {
                $prepared = [10, 12];
                $acknowledged = [13];
            } else {
                continue;
            }

            foreach ($prepared as $userId) {
                if (DB::table('users')->where('id', $userId)->exists()) {
                    $rows[] = ['category_id' => $category->id, 'role' => 'prepared_by', 'user_id' => $userId, 'created_at' => $now, 'updated_at' => $now];
                }
            }
            foreach ($acknowledged as $userId) {
                if (DB::table('users')->where('id', $userId)->exists()) {
                    $rows[] = ['category_id' => $category->id, 'role' => 'acknowledged_by', 'user_id' => $userId, 'created_at' => $now, 'updated_at' => $now];
                }
            }
        }

        if (! empty($rows)) {
            DB::table('category_signatories')->insert($rows);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_signatories');
    }
}
