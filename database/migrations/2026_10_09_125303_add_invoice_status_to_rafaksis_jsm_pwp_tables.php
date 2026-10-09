<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceStatusToRafaksisJsmPwpTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rafaksis', function (Blueprint $table) {
            $table->boolean('invoice_status')->nullable();
            $table->integer('invoice_done_count')->nullable();
            $table->dateTime('invoice_done_at')->nullable();
            $table->string('invoice_done_by')->nullable();
            $table->integer('invoice_cancel_count')->nullable();
            $table->dateTime('invoice_cancel_at')->nullable();
            $table->string('invoice_cancel_by')->nullable();
        });

        Schema::table('jsm', function (Blueprint $table) {
            $table->boolean('invoice_status')->nullable();
            $table->integer('invoice_done_count')->nullable();
            $table->dateTime('invoice_done_at')->nullable();
            $table->string('invoice_done_by')->nullable();
            $table->integer('invoice_cancel_count')->nullable();
            $table->dateTime('invoice_cancel_at')->nullable();
            $table->string('invoice_cancel_by')->nullable();
        });

        Schema::table('pwps', function (Blueprint $table) {
            $table->boolean('invoice_status')->nullable();
            $table->integer('invoice_done_count')->nullable();
            $table->dateTime('invoice_done_at')->nullable();
            $table->string('invoice_done_by')->nullable();
            $table->integer('invoice_cancel_count')->nullable();
            $table->dateTime('invoice_cancel_at')->nullable();
            $table->string('invoice_cancel_sby')->nullable();
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
            //
            $table->dropColumn(['invoice_status','invoice_done_count', 'invoice_done_at', 'invoice_done_by', 'invoice_cancel_count', 'invoice_cancel_at', 'invoice_cancel_by']);
        });

        Schema::table('jsm', function (Blueprint $table) {
            $table->dropColumn(['invoice_status','invoice_done_count', 'invoice_done_at', 'invoice_done_by', 'invoice_cancel_count', 'invoice_cancel_at', 'invoice_cancel_by']);
        });

        Schema::table('pwps', function (Blueprint $table) {
            $table->dropColumn(['invoice_status','invoice_done_count', 'invoice_done_at', 'invoice_done_by', 'invoice_cancel_count', 'invoice_cancel_at', 'invoice_cancel_by']);
        });
    }
}
