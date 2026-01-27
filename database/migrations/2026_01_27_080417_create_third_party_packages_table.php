<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartyPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_party_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('third_party_application_id');
            $table->foreign('third_party_application_id')->references('id')->on('third_party_applications')->onDelete('cascade');
            
            // Seller Information
            $table->string('seller_name');
            $table->string('seller_company')->nullable();
            $table->string('seller_phone')->nullable();
            $table->string('seller_email')->nullable();
            
            // Customer Information
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            
            // Package Information
            $table->unsignedBigInteger('area_id')->nullable();
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->decimal('delivery_cost', 10, 2)->default(0);
            $table->decimal('seller_price', 10, 2)->comment('السعر الذي يدفعه التاجر');
            $table->decimal('customer_price', 10, 2)->comment('السعر الذي يدفعه الزبون');
            $table->decimal('price_per_piece', 10, 2)->nullable()->comment('سعر كل قطعة');
            $table->decimal('discount_amount', 10, 2)->default(0)->comment('مبلغ الخصم');
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('نسبة الخصم');
            
            // Shipping Information
            $table->date('delivery_date');
            $table->date('receipt_date')->nullable();
            $table->string('location_link');
            $table->text('location_text');
            $table->string('building_number')->nullable();
            $table->string('floor_number')->nullable();
            $table->string('apartment_number')->nullable();
            
            // Package Details
            $table->string('image')->nullable()->comment('صورة الشحنة');
            $table->text('description')->nullable();
            $table->integer('pieces_count')->default(1);
            $table->text('notes')->nullable();
            $table->boolean('open_package')->default(false);
            
            // Status and Tracking
            $table->string('status')->default('5')->comment('1=تم التوصيل, 2=RTO, 3=ملغاة, 4=معدلة, 5=بالانتظار');
            $table->integer('number_of_attempts')->default(0);
            $table->string('failure_reason')->nullable();
            $table->date('reschedule_date')->nullable();
            $table->string('custom_reason')->nullable();
            $table->integer('delivered_pieces_count')->default(0);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('delivery_fee_payer')->default('customer')->comment('customer or seller');
            
            // Delivery dates for retry attempts
            $table->date('delivery_date_1')->nullable();
            $table->date('delivery_date_2')->nullable();
            $table->date('delivery_date_3')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index('third_party_application_id');
            $table->index('status');
            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('third_party_packages');
    }
}
