<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateThirdPartyApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('third_party_applications', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->unique();
            $table->string('company_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->text('description')->nullable();
            $table->string('api_key', 64)->unique();
            $table->string('api_secret', 64)->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('discount', 5, 2)->default(0)->comment('Discount percentage (0-100)');
            $table->timestamp('last_used_at')->nullable();
            $table->integer('request_count')->default(0);
            $table->text('webhook_url')->nullable();
            $table->json('allowed_ips')->nullable(); // Array of allowed IP addresses
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('third_party_applications');
    }
}
