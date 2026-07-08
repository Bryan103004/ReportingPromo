<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jsm_toko', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke tabel rafaksis
            $table->foreignId('jsm_id')
                  ->constrained('jsm')
                  ->cascadeOnDelete();

            // Foreign key ke tabel tokos
            $table->foreignId('toko_id')
                  ->constrained('tokos')
                  ->cascadeOnDelete();

            // (Opsional) Mencegah data duplikat agar 1 toko tidak bisa 
            // dimasukkan 2 kali ke dalam 1 rafaksi yang sama
            $table->unique(['jsm_id', 'toko_id']);
            
            $table->timestamps(); // Boleh dihapus jika tidak butuh fitur created_at/updated_at di pivot
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jsm_toko');
    }
};