<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('type')->nullable();
            $table->unsignedDecimal('cost')->nullable();;
            $table->unsignedBigInteger('supplier')->nullable();
            $table->foreign('supplier')->references('id')->on('suppliers');
            $table->date('purchase_Date')->nullable();;
            $table->unsignedBigInteger('condition')->nullable();
            $table->string('condition_Notes')->nullable();
            $table->unsignedDecimal('selling_Price')->nullable();;
            $table->boolean('recieved')->nullable();;
            $table->unsignedBigInteger('shipment')->nullable();
            $table->unsignedBigInteger('request_from')->nullable();
            $table->boolean('sold')->nullable();;
            $table->unsignedBigInteger('sold_To')->nullable();
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
        Schema::dropIfExists('products');
    }
}
