<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rafaksi_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('rafaksi_id')
                  ->constrained('rafaksis')
                  ->cascadeOnDelete();

            $table->unsignedInteger('line_no')->default(0);
            $table->string('article', 100);
            $table->string('shiji_code', 100)->nullable();
            $table->string('description')->nullable();
            $table->decimal('disc_nominal', 15, 2)->nullable();
            $table->decimal('promo_disc', 8, 4)->nullable();
            $table->decimal('reg', 15, 2)->default(0);
            $table->decimal('promo', 15, 2)->nullable();
            $table->decimal('claim', 15, 2)->default(0);
            $table->decimal('sales_total', 15, 2)->default(0);
            $table->decimal('value_total', 15, 2)->default(0);

            $table->timestamps();

            $table->index(['rafaksi_id', 'line_no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rafaksi_items');
    }
};
