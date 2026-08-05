<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusEmailToJsm extends Migration
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
            $table->enum('status_email', ['tidak_aktif', 'aktif'])->default('aktif')->after('no_raf');
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
            $table->dropcolumn('status_email');
        });
    }
}
