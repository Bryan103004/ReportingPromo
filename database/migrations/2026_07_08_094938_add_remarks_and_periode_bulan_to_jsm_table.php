<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksAndPeriodeBulanToJsmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jsm', function (Blueprint $table) {
            //
            $table->date('periode_bulan')->nullable()->after('periode_akhir')->index();
            $table->string('remarks')->nullable()->after('nominal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jsm', function (Blueprint $table) {
            //
            $table->dropColumn(['periode_akhir','remarks']);
        });
    }
}
