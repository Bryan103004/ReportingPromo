<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MakeNoRafOldNullableOnJsmTable extends Migration
{
    // Pakai raw SQL (bukan Schema::table()->change()) karena doctrine/dbal
    // tidak terinstall di project ini.
    public function up()
    {
        if (Schema::hasColumn('jsm', 'no_raf_old')) {
            DB::statement('ALTER TABLE `jsm` MODIFY `no_raf_old` VARCHAR(255) NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('jsm', 'no_raf_old')) {
            DB::statement('ALTER TABLE `jsm` MODIFY `no_raf_old` VARCHAR(255) NOT NULL');
        }
    }
}
