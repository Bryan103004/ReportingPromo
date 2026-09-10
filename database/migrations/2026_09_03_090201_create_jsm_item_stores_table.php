<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jsm_item_stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('jsm_item_id')
                  ->constrained('jsm_items')
                  ->cascadeOnDelete();

            $table->foreignId('toko_id')
                  ->constrained('tokos')
                  ->cascadeOnDelete();

            $table->decimal('sales_qty', 15, 2)->default(0);
            $table->decimal('value', 15, 2)->default(0);

            $table->unique(['jsm_item_id', 'toko_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jsm_item_stores');
    }
};
