<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalsToLocsTable extends Migration
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
            $table->string('no_raf_referensi')->after('no_raf')->nullable()->index();
            $table->date('created_date')->after('created_at')->nullable();
            $table->time('created_time')->after('created_date')->nullable();

            $table->date('updated_date')->after('updated_at')->nullable();
            $table->time('updated_time')->after('updated_date')->nullable();
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
            $table->dropColumn(['created_date', 'created_time', 'updated_date', 'updated_time']);
        });
    }
}
