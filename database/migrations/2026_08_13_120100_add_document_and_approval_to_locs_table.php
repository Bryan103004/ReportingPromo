<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDocumentAndApprovalToLocsTable extends Migration
{
    public function up()
    {
        Schema::table('locs', function (Blueprint $table) {
            $table->string('document_path')->nullable()->after('no_raf');
            $table->string('document_original_name')->nullable()->after('document_path');
            $table->unsignedBigInteger('approved_by')->nullable()->after('document_original_name')->index();
            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('locs', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['document_path', 'document_original_name', 'approved_by', 'approved_at']);
        });
    }
}
