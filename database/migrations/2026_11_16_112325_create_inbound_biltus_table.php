<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInboundBiltusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inbound_biltus', function (Blueprint $table) {
            $table->id();
            $table->string('client')->nullable();
            $table->string('C_BPartner_ID')->nullable()->index();
            $table->string('value')->nullable();
            $table->string('name')->nullable();
            $table->string('name2')->nullable();
            $table->string('taxid')->nullable();
            $table->string('Address1')->nullable();
            $table->string('Address2')->nullable();
            $table->string('Address3')->nullable();
            $table->string('Address4')->nullable();
            $table->string('City')->nullable();
            $table->string('regionName')->nullable();
            $table->string('postal')->nullable();
            $table->string('vendor_no')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('NITKU')->nullable();
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
        Schema::dropIfExists('inbound_biltus');
    }
}
