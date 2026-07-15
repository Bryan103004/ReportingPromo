<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRemarksAndPeriodeBulanToLocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locs', function (Blueprint $table) {
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
        Schema::table('locs', function (Blueprint $table) {
            //
        });
    }
}
