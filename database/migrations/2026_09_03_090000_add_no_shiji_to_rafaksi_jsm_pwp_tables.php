<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoShijiToRafaksiJsmPwpTables extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rafaksis')) {
            Schema::table('rafaksis', function (Blueprint $table) {
                $table->string('no_shiji')->nullable()->after('no_raf');
            });
        }

        if (Schema::hasTable('jsm')) {
            Schema::table('jsm', function (Blueprint $table) {
                $table->string('no_shiji')->nullable()->after('no_raf');
            });
        }

        if (Schema::hasTable('pwps')) {
            Schema::table('pwps', function (Blueprint $table) {
                $table->string('no_shiji')->nullable()->after('no_raf');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('rafaksis')) {
            Schema::table('rafaksis', function (Blueprint $table) {
                $table->dropColumn('no_shiji');
            });
        }

        if (Schema::hasTable('jsm')) {
            Schema::table('jsm', function (Blueprint $table) {
                $table->dropColumn('no_shiji');
            });
        }

        if (Schema::hasTable('pwps')) {
            Schema::table('pwps', function (Blueprint $table) {
                $table->dropColumn('no_shiji');
            });
        }
    }
}
