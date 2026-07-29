<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiltersToNotificationRecipientsTable extends Migration
{
    public function up()
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            // Menambahkan kolom filters bertipe JSON setelah kolom modules
            $table->json('filters')->nullable()->after('modules');
        });
    }

    public function down()
    {
        Schema::table('notification_recipients', function (Blueprint $table) {
            $table->dropColumn('filters');
        });
    }
}