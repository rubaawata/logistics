<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferenceNumberAndSellerCostToPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->string('reference_number', 255)->nullable()->after('id');
            $table->integer('seller_cost')->nullable()->after('reference_number');
            $table->unsignedBigInteger('third_party_application_id')
                ->nullable()
                ->after('seller_cost');

            $table->foreign('third_party_application_id')
                ->references('id')
                ->on('third_party_applications')
                ->nullOnDelete(); // or ->cascadeOnDelete()
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropForeign(['third_party_application_id']);
            
            $table->dropColumn([
                'reference_number',
                'seller_cost',
                'third_party_application_id'
            ]);
            
        });
    }
}
