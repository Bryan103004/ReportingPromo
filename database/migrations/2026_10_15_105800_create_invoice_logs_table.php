<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_logs', function (Blueprint $table) {
            $table->id();
            $table->string('document_type'); // 'rafaksi', 'jsm', 'pwp', 'loc'
            $table->unsignedBigInteger('document_id');
            $table->string('no_raf')->nullable();
            $table->string('status'); // 'done', 'cancel'
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name');
            $table->dateTime('action_at');
            $table->timestamps();

            $table->index(['document_type', 'document_id']);
            $table->index('no_raf');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_logs');
    }
}

