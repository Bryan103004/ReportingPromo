<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocsToko extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locs_toko', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke tabel loc
            $table->foreignId('loc_id')
                  ->constrained('locs')
                  ->cascadeOnDelete();

            // Foreign key ke tabel tokos
            $table->foreignId('toko_id')
                  ->constrained('tokos')
                  ->cascadeOnDelete();

            // (Opsional) Mencegah data duplikat agar 1 toko tidak bisa 
            // dimasukkan 2 kali ke dalam 1 loc yang sama
            $table->unique(['loc_id', 'toko_id']);
            
            $table->timestamps(); // Boleh dihapus jika tidak butuh fitur created_at/updated_at di pivot
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locs_toko');
    }
}
