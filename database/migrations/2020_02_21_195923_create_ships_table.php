<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned()->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('set null');
            $table->boolean('shipped')->default(false); 
            $table->string('shipment_company')->nullable();
                $table->foreign('shipment_company')->references('id')->on('ship_companies');
            $table->date('shipment_date')->nullable(); 
            $table->unsignedDecimal('shipment_cost')->nullable();  
            $table->string('shipment_notes')->nullable();
            $table->boolean('received')->default(false);      
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
        Schema::dropIfExists('ships');
    }
}
