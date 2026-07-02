<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRafaksisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rafaksis', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->index();
            $table->string('supplier_name', 150);
            $table->date('periode_awal')->index();
            $table->date('periode_akhir')->index();
            $table->string('no_raf')->index();
            $table->text('store')->index();
            $table->decimal('nominal', 15, 2)->default(0);


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
        Schema::dropIfExists('rafaksis');
    }
}
