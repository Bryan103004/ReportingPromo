<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInboundTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbound', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->index();
            $table->unsignedBigInteger('category_id')->nullable();

            $table->foreign('category_id', 'fk_inbound_category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('category_id');

            $table->string('reminder_id')->nullable(); // Tambahan kolom reminder_id
            $table->string('supplier_name', 150);
            $table->date('periode_awal')->nullable()->index();
            $table->date('periode_akhir')->nullable()->index();
            $table->date('periode_bulan')->nullable(); // Tambahan kolom periode_bulan
            $table->string('no_raf')->index();
            $table->string('no_raf_referensi')->nullable()->index();
            $table->string('toko_id')->nullable()->index(); // Tambahan kolom toko_id (Tanpa Foreign Key agar aman)
            $table->text('store')->nullable();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('remarks')->nullable(); // Tambahan kolom remarks jika ada di CSV

            // Untuk data baru dibuat
            $table->date('created_date')->nullable();
            $table->time('created_time')->nullable();

            // Untuk data saat diperbarui
            $table->date('updated_date')->nullable();
            $table->time('updated_time')->nullable();
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
        Schema::dropIfExists('inbound');
    }
}