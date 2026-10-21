<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPpnAmountInclPpnToLocs extends Migration
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
            $table->decimal('ppn', 15, 2)->default(0)->after('nominal');
            $table->decimal('amount_incl_ppn', 15, 2)->default(0)->after('ppn');
        });

        Schema::table('inbound', function (Blueprint $table) {
            //
            $table->decimal('ppn', 15, 2)->default(0)->after('nominal');
            $table->decimal('amount_incl_ppn', 15, 2)->default(0)->after('ppn');
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
            $table->dropColumn(['ppn','amount_incl_ppn']);
        });

        Schema::table('inbound', function (Blueprint $table) {
            //
            $table->dropColumn(['ppn','amount_incl_ppn']);
        });
    }
}
