<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rafaksi_item_stores', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rafaksi_item_id')
                  ->constrained('rafaksi_items')
                  ->cascadeOnDelete();

            $table->foreignId('toko_id')
                  ->constrained('tokos')
                  ->cascadeOnDelete();

            $table->decimal('sales_qty', 15, 2)->default(0);
            $table->decimal('value', 15, 2)->default(0);

            $table->unique(['rafaksi_item_id', 'toko_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rafaksi_item_stores');
    }
};
