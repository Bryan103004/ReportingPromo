<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationRecipientsTable extends Migration
{
    public function up()
    {
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();

            // Array modul yang akan diterima, contoh: ["stnk","perizinan","kontrak","oss"]
            // Isi semua modul jika ingin menerima semua reminder.
            $table->json('modules');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notification_recipients');
    }
}