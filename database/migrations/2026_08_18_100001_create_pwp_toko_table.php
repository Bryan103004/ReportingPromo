<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pwp_toko', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pwp_id')
                  ->constrained('pwps')
                  ->cascadeOnDelete();

            $table->foreignId('toko_id')
                  ->constrained('tokos')
                  ->cascadeOnDelete();

            $table->unique(['pwp_id', 'toko_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pwp_toko');
    }
};
