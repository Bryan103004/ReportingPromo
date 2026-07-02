<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReportsTable extends Migration
{
    public function up()
    {
        Schema::create('sales_reports', function (Blueprint $table) {
            $table->id();
            // Data Header
            $table->string('outlet_code', 20)->index(); 
            $table->string('outlet_name', 100);         

            // Data Baris 1 (SKU, Product, dsb)
            $table->string('sku', 50)->index();
            $table->string('product_name', 150);
            $table->string('size', 50)->nullable();
            $table->string('uom', 20)->nullable();
            $table->date('transaction_date')->index(); 

            // Data Baris 2 (Metrics & Details)
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('gross_sales', 15, 2)->default(0);
            $table->decimal('sls_discount', 15, 2)->default(0);
            $table->decimal('sales_return', 15, 2)->default(0);
            $table->decimal('sales_incl_tax', 15, 2)->default(0);
            $table->string('pct_sales', 20)->nullable(); // Pakai string untuk menyimpan "%" atau bisa decimal jika di-trim
            $table->decimal('sales_tax', 15, 2)->default(0);
            $table->decimal('sales_excl_tax', 15, 2)->default(0);
            $table->decimal('cogs', 15, 2)->default(0);
            $table->decimal('gp_amount', 15, 2)->default(0);
            $table->string('pct_gp', 20)->nullable();
            $table->string('contribu', 20)->nullable();
            $table->decimal('soh', 15, 2)->default(0);
            
            // Merchandising & Supplier Info
            $table->string('md1', 50)->nullable();
            $table->string('desc_m1', 150)->nullable();
            $table->string('md2', 50)->nullable();
            $table->string('desc_m2', 150)->nullable();
            $table->string('md3', 50)->nullable();
            $table->string('desc_m3', 150)->nullable();
            $table->string('md4', 50)->nullable();
            $table->string('desc_m4', 150)->nullable();
            $table->string('sku_2', 50)->nullable(); // Dari kolom SKU yang ada di belakang
            $table->string('supl', 50)->nullable();
            $table->string('supplier_name', 150)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sales_reports');
    }
}