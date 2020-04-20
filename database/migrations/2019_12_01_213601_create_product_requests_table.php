<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('customer_id')->nullable();
                $table->foreign('customer_id')->references('id')->on('users');
            $table->string('name');
            $table->unsignedBigInteger('type')->nullable();  
                $table->foreign('type')->references('id')->on('categories');          
            $table->unsignedBigInteger('condition')->nullable(); 
                $table->foreign('condition')->references('id')->on('conditions');
            $table->boolean('deposit_paid')->default('0');
            $table->boolean('acquired')->dafault('0');
            $table->unsignedDecimal('deposit')->nullable();
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
        Schema::dropIfExists('product_requests');
    }
}
