<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusEmailUpdatedByToDocumentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Nyimpen siapa & kapan terakhir toggle status_email (Sudah Siap/Belum Siap Kirim
        // ke AR) -- dipakai CategorySpvService buat milih Prepared By yang BENERAN
        // nandain dokumen siap, bukan asal fallback ke SPV pertama waktu yang nge-print
        // (role AR/OI) bukan salah satu SPV kategori itu sendiri.
        // Sengaja TANPA foreign key constraint: nambah FK constraint ke tabel existing
        // di MySQL butuh ALTER pakai algoritma COPY (rebuild seluruh tabel), yang mana
        // di tabel ini kejegal data lama yang gak valid (periode_awal = '0000-00-00')
        // sehingga ALTER-nya gagal total. Kolom plain (tanpa constrained()) cukup pakai
        // algoritma INPLACE yang gak perlu validasi ulang kolom lain, jadi aman.
        foreach (['rafaksis', 'jsm', 'pwps', 'locs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->unsignedBigInteger('status_email_updated_by')->nullable()->after('status_email');
                $table->dateTime('status_email_updated_at')->nullable()->after('status_email_updated_by');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (['rafaksis', 'jsm', 'pwps', 'locs'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['status_email_updated_by', 'status_email_updated_at']);
            });
        }
    }
}
