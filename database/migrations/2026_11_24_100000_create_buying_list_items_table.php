<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The owner's buying list: what to look for on the next UK buying trip,
     * usually added from the dashboard's sales and stock details.
     */
    public function up(): void
    {
        Schema::create('buying_list_items', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('condition_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedInteger('target_cost_amount')->nullable();
            $table->char('target_cost_currency', 3)->default('GBP');
            $table->string('status', 16)->default('to_buy')->index();
            $table->string('source')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('bought_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('buying_list_items');
    }
};
