<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A customer asking the shop to find an item it doesn't have, secured by a deposit.
        Schema::create('product_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 32)->unique();
            $table->string('status', 32)->index();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->foreignId('condition_id')->constrained()->restrictOnDelete();
            $table->string('description');
            $table->text('notes')->nullable();
            // Deposit as set on the condition when the request was made.
            $table->unsignedInteger('deposit_amount');
            $table->char('deposit_currency', 3);
            $table->foreignId('stock_unit_id')->nullable()->constrained()->restrictOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('sourced_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->morphs('payable');
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->string('provider', 32);
            $table->string('reference', 64)->unique();
            $table->string('provider_transaction_id')->nullable();
            $table->string('status', 32)->index();
            $table->unsignedInteger('amount');
            $table->char('currency', 3);
            $table->decimal('exchange_rate', 14, 6)->nullable();
            $table->string('channel', 32)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->unsignedInteger('refunded_amount')->default(0);
            $table->string('refund_status', 32)->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->json('provider_response')->nullable();
            $table->timestamps();
        });

        // Every webhook received, for auditing and to skip duplicates.
        Schema::create('webhook_calls', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 32);
            $table->string('event', 64);
            $table->string('payload_hash', 128);
            $table->json('payload');
            $table->timestamp('processed_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'payload_hash']);
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('product_request_id')->nullable()->after('customer_phone')
                ->constrained()->nullOnDelete();
            // Deposit already paid online, in the sale currency, counted towards the price.
            $table->unsignedInteger('deposit_applied_amount')->default(0)->after('price_paid_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_request_id');
            $table->dropColumn('deposit_applied_amount');
        });
        Schema::dropIfExists('webhook_calls');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('product_requests');
    }
};
