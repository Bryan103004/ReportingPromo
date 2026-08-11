    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class AddTokoIdToInbound extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::table('inbound', function (Blueprint $table) {
                //
                $table->string('toko_id'); 
                $table->foreign('toko_id')->references('id_alias')->on('tokos')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::table('inbound', function (Blueprint $table) {
                //
                $table->dropColumn('toko_id');
            });
        }
    }
