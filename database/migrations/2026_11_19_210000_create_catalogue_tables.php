<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact_name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->text('address')->nullable();
            $table->char('country_code', 2)->default('GB');
            // The supplier-portal login, when the supplier has one.
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('base_currency', 3);
            $table->char('quote_currency', 3);
            // 1 unit of base = rate units of quote, e.g. 1 GBP = 15.250000 GHS.
            $table->decimal('rate', 14, 6);
            $table->timestamp('effective_at');
            $table->foreignId('set_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['base_currency', 'quote_currency', 'effective_at']);
        });

        // A catalogue entry, e.g. "iPhone 13 128GB". Physical items are stock units.
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->text('description')->nullable();
            $table->json('specs')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // One row per physical item, as in 2020, with its own condition, cost and price.
        Schema::create('stock_units', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('condition_id')->constrained()->restrictOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->string('status', 32)->index();
            $table->unsignedInteger('cost_amount');
            $table->char('cost_currency', 3);
            $table->unsignedInteger('price_amount');
            $table->char('price_currency', 3);
            $table->text('condition_notes')->nullable();
            $table->string('serial_number')->nullable()->index();
            $table->date('purchased_at');
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_units');
        Schema::dropIfExists('products');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('suppliers');
    }
};
