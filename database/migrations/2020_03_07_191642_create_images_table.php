<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('images', function (Blueprint $table) {
            $table->bigIncrements('id');            
            $table->unsignedInteger('product_id')->nullable();    
                $table->foreign('product_id')->references('id')->on('products');
            $table->unsignedInteger('supplierproduct_id')->nullable();
                $table->foreign('supplierproduct_id')->references('id')->on('supplier_products');
            $table->string('path');
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
        Schema::dropIfExists('images');
    }
}
