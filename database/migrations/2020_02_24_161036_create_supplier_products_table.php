<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupplierProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('supplier_products', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->unsignedBigInteger('supplier_id')->nullable();            
            $table->string('name')->nullable();
            $table->unsignedBigInteger('type')->nullable();
            $table->unsignedBigInteger('condition')->nullable();
            $table->string('condition_Notes')->nullable();
            $table->unsignedDecimal('selling_Price')->nullable();            
            $table->boolean('purchased');
            $table->string('thumbnail_path')->nullable();
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
        Schema::dropIfExists('supplier_products');
    }
}
