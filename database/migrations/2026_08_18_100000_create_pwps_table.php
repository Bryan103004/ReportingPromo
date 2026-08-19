<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePwpsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pwps', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_code', 50)->index();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('supplier_name', 150);
            $table->date('periode_awal')->index();
            $table->date('periode_akhir')->index();
            $table->date('periode_bulan')->nullable()->index();
            $table->string('no_raf')->index();
            $table->enum('status_email', ['tidak_aktif', 'aktif'])->default('aktif');
            $table->unsignedInteger('raf_sequence')->nullable()->index();
            $table->text('store')->index();
            $table->decimal('nominal', 15, 2)->default(0);
            $table->string('remarks')->nullable();
            $table->foreignId('reminder_id')->nullable()->constrained('master_notifikasi_reminders');
            $table->timestamps();

            $table->foreign('category_id', 'fk_pwps_category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pwps');
    }
}
