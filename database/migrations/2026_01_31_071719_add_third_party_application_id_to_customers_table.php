<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThirdPartyApplicationIdToCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->unsignedBigInteger('third_party_application_id')
                ->nullable();

            $table->foreign('third_party_application_id')
                ->references('id')
                ->on('third_party_applications')
                ->nullOnDelete(); // use cascadeOnDelete() if you want delete to cascade
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['third_party_application_id']);
            $table->dropColumn('third_party_application_id');
        });
    }
}
