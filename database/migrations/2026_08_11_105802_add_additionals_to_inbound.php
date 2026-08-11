<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalsToInbound extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inbound', function (Blueprint $table) {
            //
            $table->foreignId('reminder_id')
                ->nullable()
                ->after('category_id')
                ->constrained('master_notifikasi_reminders');

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
        Schema::table('inbound', function (Blueprint $table) {
            //
            $table->dropColumn(['periode_bulan', 'remarks', 'reminder_id']);
        });
    }
}
