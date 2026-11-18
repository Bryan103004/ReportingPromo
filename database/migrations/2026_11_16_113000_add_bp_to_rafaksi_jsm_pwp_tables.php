<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rafaksis', function (Blueprint $table) {
            $table->string('bp')->nullable()->after('supplier_name');
        });

        Schema::table('jsm', function (Blueprint $table) {
            $table->string('bp')->nullable()->after('supplier_name');
        });

        Schema::table('pwps', function (Blueprint $table) {
            $table->string('bp')->nullable()->after('supplier_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rafaksis', function (Blueprint $table) {
            $table->dropColumn('bp');
        });

        Schema::table('jsm', function (Blueprint $table) {
            $table->dropColumn('bp');
        });

        Schema::table('pwps', function (Blueprint $table) {
            $table->dropColumn('bp');
        });
    }
};
