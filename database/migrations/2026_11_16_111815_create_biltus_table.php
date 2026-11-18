<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBiltusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('biltus', function (Blueprint $table) {
            $table->id();
            $table->string('client')->nullable();
            $table->string('C_BPartner_ID')->index();
            $table->string('value')->nullable()->index();
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
            $table->boolean('is_mba')->default(false)->index();
            $table->timestamps();

            $table->unique(['C_BPartner_ID', 'value'], 'biltu_bp_value_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('biltus');
    }
}
