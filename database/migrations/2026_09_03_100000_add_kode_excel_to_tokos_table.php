<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKodeExcelToTokosTable extends Migration
{
    // Kode singkatan toko yang dipakai di template Excel Rafaksi/Jsm/Pwp
    // (kolom "Sales {KODE}"/"Value {KODE}") -- beda dari kode_toko/id_alias yang
    // sudah ada, jadi disimpan terpisah biar eksplisit dan tidak menimpa data lain.
    public function up()
    {
        if (Schema::hasTable('tokos') && ! Schema::hasColumn('tokos', 'kode_excel')) {
            Schema::table('tokos', function (Blueprint $table) {
                $table->string('kode_excel', 20)->nullable()->unique()->after('kode_toko');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('tokos', 'kode_excel')) {
            Schema::table('tokos', function (Blueprint $table) {
                $table->dropColumn('kode_excel');
            });
        }
    }
}
