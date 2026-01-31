<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_items', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto_increment

            $table->unsignedBigInteger('package_id')->index();

            $table->string('name', 191);
            $table->text('description')->nullable();

            $table->decimal('price', 10, 2);
            $table->integer('quantity')->default(1);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Foreign key → packages.id
            $table->foreign('package_id')
                ->references('id')
                ->on('packages')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_items');
    }
}
