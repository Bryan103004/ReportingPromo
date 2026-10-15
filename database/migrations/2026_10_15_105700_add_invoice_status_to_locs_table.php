<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceStatusToLocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locs', function (Blueprint $table) {
            $table->boolean('invoice_status')->nullable();
            $table->integer('invoice_done_count')->default(0);
            $table->dateTime('invoice_done_at')->nullable();
            $table->string('invoice_done_by')->nullable();
            $table->integer('invoice_cancel_count')->default(0);
            $table->dateTime('invoice_cancel_at')->nullable();
            $table->string('invoice_cancel_by')->nullable();
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
            $table->dropColumn([
                'invoice_status',
                'invoice_done_count',
                'invoice_done_at',
                'invoice_done_by',
                'invoice_cancel_count',
                'invoice_cancel_at',
                'invoice_cancel_by',
            ]);
        });
    }
}

