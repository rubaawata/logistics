<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCancellationFeePercentageToThirdPartyApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('third_party_applications', function (Blueprint $table) {
            $table->unsignedTinyInteger('cancellation_fee_percentage')
            ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('third_party_applications', function (Blueprint $table) {
            $table->dropColumn('cancellation_fee_percentage');
        });
    }
}
