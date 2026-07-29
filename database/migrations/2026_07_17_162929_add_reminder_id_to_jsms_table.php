<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReminderIdToJsmsTable extends Migration
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
        $table->foreignId('reminder_id')
              ->nullable()
              ->after('category_id')
              ->constrained('master_notifikasi_reminders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jsms', function (Blueprint $table) {
            //
        });
    }
}
