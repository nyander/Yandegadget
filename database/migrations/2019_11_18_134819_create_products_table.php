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
            $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('type')->nullable();
                // $table->foreign('type')->references('id')->on('categories');
            $table->unsignedDecimal('cost')->nullable();;
            $table->unsignedBigInteger('supplier')->nullable();
                // $table->foreign('supplier')->references('id')->on('suppliers');
            $table->date('purchase_Date')->nullable();;
            $table->unsignedBigInteger('condition')->nullable();
                // $table->foreign('condition')->references('id')->on('conditions');
            $table->string('condition_Notes')->nullable();
            $table->unsignedDecimal('selling_Price')->nullable();
            $table->boolean('received')->default(false)->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
                // $table->foreign('requested_by')->references('id')->on('users');
            $table->boolean('sold')->default(false)->nullable();
            $table->date('sold_Date')->nullable();
            $table->boolean('featured')->default(false);
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
        Schema::dropIfExists('products');
    }
}
