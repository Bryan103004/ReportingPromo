<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FixInvoiceCountDefaultsAndPwpTypo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Fix typo: kolom di tabel pwps kebuat "invoice_cancel_sby" bukan
        // "invoice_cancel_by" (lihat migrasi add_invoice_status_to_rafaksis_jsm_pwp_tables).
        // doctrine/dbal belum terinstall jadi gak bisa pakai renameColumn()/change() --
        // jadi bikin kolom baru, pindahin data yang ada, baru drop kolom lama.
        if (Schema::hasColumn('pwps', 'invoice_cancel_sby') && ! Schema::hasColumn('pwps', 'invoice_cancel_by')) {
            Schema::table('pwps', function (Blueprint $table) {
                $table->string('invoice_cancel_by')->nullable()->after('invoice_cancel_at');
            });

            DB::table('pwps')->whereNotNull('invoice_cancel_sby')->update([
                'invoice_cancel_by' => DB::raw('invoice_cancel_sby'),
            ]);

            Schema::table('pwps', function (Blueprint $table) {
                $table->dropColumn('invoice_cancel_sby');
            });
        }

        // Counter invoice_done_count/invoice_cancel_count dibuat nullable tanpa default,
        // jadi baris lama/baru yang belum pernah ditoggle nilainya NULL. Controller sudah
        // dibungkus COALESCE() biar +1 gak macet di NULL, tapi backfill ke 0 di sini juga
        // biar tampilan di tabel (yang nge-print count-nya langsung) gak nunjukin kosong.
        foreach (['rafaksis', 'jsm', 'pwps'] as $tableName) {
            DB::table($tableName)->whereNull('invoice_done_count')->update(['invoice_done_count' => 0]);
            DB::table($tableName)->whereNull('invoice_cancel_count')->update(['invoice_cancel_count' => 0]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('pwps', 'invoice_cancel_by') && ! Schema::hasColumn('pwps', 'invoice_cancel_sby')) {
            Schema::table('pwps', function (Blueprint $table) {
                $table->string('invoice_cancel_sby')->nullable()->after('invoice_cancel_at');
            });

            DB::table('pwps')->whereNotNull('invoice_cancel_by')->update([
                'invoice_cancel_sby' => DB::raw('invoice_cancel_by'),
            ]);

            Schema::table('pwps', function (Blueprint $table) {
                $table->dropColumn('invoice_cancel_by');
            });
        }
    }
}
