<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('loc_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loc_id');
            $table->foreign('loc_id')->references('id')->on('locs')->onDelete('cascade');
            $table->string('filename');
            $table->string('filepath');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loc_documents');
    }
}
