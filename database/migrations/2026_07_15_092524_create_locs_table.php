<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locs', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->index();
            // Tambahkan ->nullable() agar data lama tidak error saat kolom dibuat
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('category_id', 'fk_loc_category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('category_id');

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
        Schema::dropIfExists('locs');
    }
}
