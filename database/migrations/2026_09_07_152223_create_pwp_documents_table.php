<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePwpDocumentsTable extends Migration
{
    public function up()
    {
        Schema::create('pwp_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pwp_id');
            $table->foreign('pwp_id')->references('id')->on('pwps')->onDelete('cascade');
            $table->string('filename');
            $table->string('filepath');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pwp_documents');
    }
}
