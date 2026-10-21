<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodwillTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Staging Inbound Invoice
        Schema::create('inbound_invoice_goodwills', function (Blueprint $table) {
            $table->id();
            $table->string('client', 100)->nullable();
            $table->string('inv_no', 100)->index();
            $table->date('inv_date')->nullable();
            $table->time('inv_time')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->date('last_updated_date')->nullable();
            $table->time('last_updated_time')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('so_no', 100)->index(); // = no_raf di LOC
            $table->date('bill_date')->nullable();
            $table->time('bill_time')->nullable();
            $table->string('paid', 10)->nullable();
            $table->timestamps();
        });

        // 2. Tabel Staging Inbound Payment
        Schema::create('inbound_payment_goodwills', function (Blueprint $table) {
            $table->id();
            $table->string('client', 100)->nullable();
            $table->string('inv_no', 100)->index();
            $table->date('inv_date')->nullable();
            $table->time('inv_time')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->date('last_updated_date')->nullable();
            $table->time('last_updated_time')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('so_no', 100)->index();
            $table->date('bill_date')->nullable();
            $table->time('bill_time')->nullable();
            $table->string('allocation_no', 100)->index();
            $table->date('allocation_date')->nullable();
            $table->time('allocation_time')->nullable();
            $table->decimal('allocation_amt', 15, 2)->default(0);
            $table->string('payment_no', 100)->nullable()->index();
            $table->date('payment_date')->nullable();
            $table->time('payment_time')->nullable();
            $table->string('paid', 10)->nullable();
            $table->timestamps();
        });

        // 3. Tabel Utama Invoice Goodwill
        Schema::create('invoice_goodwills', function (Blueprint $table) {
            $table->id();
            $table->string('client', 100)->nullable();
            $table->string('inv_no', 100)->index();
            $table->date('inv_date')->nullable();
            $table->time('inv_time')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->date('last_updated_date')->nullable();
            $table->time('last_updated_time')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('so_no', 100)->index(); // = no_raf di LOC
            $table->date('bill_date')->nullable();
            $table->time('bill_time')->nullable();
            $table->string('paid', 10)->nullable();
            $table->timestamps();
        });

        // 4. Tabel Utama Payment Goodwill
        Schema::create('payment_goodwills', function (Blueprint $table) {
            $table->id();
            $table->string('client', 100)->nullable();
            $table->string('inv_no', 100)->index();
            $table->date('inv_date')->nullable();
            $table->time('inv_time')->nullable();
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->date('last_updated_date')->nullable();
            $table->time('last_updated_time')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('so_no', 100)->index();
            $table->date('bill_date')->nullable();
            $table->time('bill_time')->nullable();
            $table->string('allocation_no', 100)->index();
            $table->date('allocation_date')->nullable();
            $table->time('allocation_time')->nullable();
            $table->decimal('allocation_amt', 15, 2)->default(0);
            $table->string('payment_no', 100)->nullable()->index();
            $table->date('payment_date')->nullable();
            $table->time('payment_time')->nullable();
            $table->string('paid', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_goodwills');
        Schema::dropIfExists('invoice_goodwills');
        Schema::dropIfExists('inbound_payment_goodwills');
        Schema::dropIfExists('inbound_invoice_goodwills');
    }
}
