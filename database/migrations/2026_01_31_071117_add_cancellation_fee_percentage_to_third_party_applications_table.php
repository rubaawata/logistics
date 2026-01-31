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
            $table->decimal('cancellation_fee_percentage', 5, 2)
                ->default(25)
                ->nullable()
                ->after('discount')
                ->comment('Percentage of delivery cost that third party pays if order is cancelled (0-100)');
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
