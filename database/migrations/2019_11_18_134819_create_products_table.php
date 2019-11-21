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
            $table->string('name');
            $table ->string('type');
            $table->unsignedDecimal('cost');
            $table->unsignedBigInteger('supplier');
            $table->foreign('supplier')->references('id')->on('suppliers');
            $table->date('purchase_Date');
            $table->unsignedBigInteger('condition');
            $table->foreign('condition')->references('id')->on('conditions');
            $table->string('condition_Notes');
            $table->unsignedDecimal('selling_Price');
            $table->boolean('recieved');
            $table->unsignedBigInteger('shipment');
            $table->foreign('shipment')->references('id')->on('shipments');
            $table->boolean('sold');
            $table->unsignedBigInteger('sold_To');
            $table->foreign('sold_To')->references('id')->on('customers');
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
