<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJsmDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('jsm_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jsm_id');
            $table->foreign('jsm_id')->references('id')->on('jsm')->onDelete('cascade');
            $table->string('filename');
            $table->string('filepath');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jsm_documents');
    }
}
