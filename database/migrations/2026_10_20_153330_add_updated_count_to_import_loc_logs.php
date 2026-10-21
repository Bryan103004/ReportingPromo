<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedCountToImportLocLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_loc_logs', function (Blueprint $table) {
            $table->integer('updated_count')->default(0)->after('success_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_loc_logs', function (Blueprint $table) {
            $table->dropColumn('updated_count');
        });
    }
}
