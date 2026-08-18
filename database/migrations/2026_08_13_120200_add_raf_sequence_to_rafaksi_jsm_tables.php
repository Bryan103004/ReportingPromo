<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRafSequenceToRafaksiJsmTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rafaksis')) {
            Schema::table('rafaksis', function (Blueprint $table) {
                $table->unsignedInteger('raf_sequence')->nullable()->after('no_raf')->index();
            });
        }

        if (Schema::hasTable('jsm')) {
            Schema::table('jsm', function (Blueprint $table) {
                $table->unsignedInteger('raf_sequence')->nullable()->after('no_raf')->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('rafaksis')) {
            Schema::table('rafaksis', function (Blueprint $table) {
                $table->dropColumn('raf_sequence');
            });
        }

        if (Schema::hasTable('jsm')) {
            Schema::table('jsm', function (Blueprint $table) {
                $table->dropColumn('raf_sequence');
            });
        }
    }
}
