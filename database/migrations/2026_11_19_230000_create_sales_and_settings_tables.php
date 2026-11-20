<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Small admin-editable settings, e.g. the negotiation floor.
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->json('value');
            $table->timestamps();
        });

        // One in-store sale of one stock unit. Prices and the rate are copied in
        // at the time of sale so reports never change when the rate does.
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('stock_unit_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->foreignId('sold_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 32)->nullable();

            // List price (GBP) and what it was worth in the sale currency at the time.
            $table->unsignedInteger('list_price_amount');
            $table->char('list_price_currency', 3);
            $table->unsignedInteger('listed_amount');
            $table->unsignedInteger('floor_amount');
            $table->unsignedInteger('price_paid_amount');
            $table->char('currency', 3);
            $table->foreignId('exchange_rate_id')->nullable()->constrained()->restrictOnDelete();
            $table->decimal('rate', 14, 6)->nullable();
            $table->unsignedTinyInteger('floor_percent');

            $table->string('payment_method', 32);
            $table->string('payment_reference')->nullable();
            $table->timestamp('sold_at')->index();
            $table->text('notes')->nullable();

            $table->timestamp('voided_at')->nullable();
            $table->foreignId('voided_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
        Schema::dropIfExists('settings');
    }
};
