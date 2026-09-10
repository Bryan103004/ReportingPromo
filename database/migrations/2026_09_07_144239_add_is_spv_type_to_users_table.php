<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSpvTypeToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->boolean('is_rafaksi_spv')->default(0)->after('is_type');
            $table->boolean('is_jsm_spv')->default(0)->after('is_type');
            $table->boolean('is_pwp_spv')->default(0)->after('is_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('is_rafaksi_spv');
            $table->dropColumn('is_jsm_spv');
            $table->dropColumn('is_pwp_spv');
        });
    }
}
