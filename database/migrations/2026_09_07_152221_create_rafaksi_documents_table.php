<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRafaksiDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rafaksi_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rafaksi_id');
            $table->foreign('rafaksi_id')->references('id')->on('rafaksis')->onDelete('cascade');
            $table->string('filename');
            $table->string('filepath');
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
        Schema::dropIfExists('rafaksi_documents');
    }
}
