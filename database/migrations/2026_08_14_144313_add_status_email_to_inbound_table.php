<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusEmailToInboundTable extends Migration
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
            $table->enum('status_email',['aktif','nonaktif'])->default('aktif')->after('updated_time');
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
            $table->dropColumn('status_email');
        });
    }
}
