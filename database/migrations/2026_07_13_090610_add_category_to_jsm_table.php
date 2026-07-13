<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCategoryToJsmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('jsm', function (Blueprint $table) {
            // Tambahkan ->nullable() agar data lama tidak error saat kolom dibuat
            $table->unsignedBigInteger('category_id')->nullable()->after('supplier_code');

            $table->foreign('category_id', 'fk_jsm_category_id')
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
        Schema::table('jsm', function (Blueprint $table) {
            //
            $table->dropColumn('category_id');
        });
    }
}
