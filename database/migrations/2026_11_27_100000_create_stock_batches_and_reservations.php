<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Items bought together in bulk (e.g. 50 watches): shared details, edited together.
        Schema::create('stock_batches', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('condition_id')->constrained();
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->bigInteger('cost_amount');
            $table->char('cost_currency', 3);
            $table->bigInteger('price_amount');
            $table->char('price_currency', 3);
            $table->date('purchased_at');
            $table->text('condition_notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::table('stock_units', function (Blueprint $table) {
            $table->foreignId('batch_id')->nullable()->after('product_id')->constrained('stock_batches')->nullOnDelete();
        });

        // A customer reserving an in-store item online and paying in full or a deposit.
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 20)->unique();
            $table->foreignId('customer_id')->constrained('users');
            $table->foreignId('stock_unit_id')->constrained();
            $table->string('status', 24)->index();
            $table->string('payment_option', 16);
            $table->bigInteger('price_amount');
            $table->char('price_currency', 3);
            $table->bigInteger('due_amount');
            $table->char('due_currency', 3);
            $table->timestamp('hold_expires_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('collect_by')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 24)->nullable();
            $table->string('cancel_reason', 500)->nullable();
            $table->timestamps();
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('reservation_id')->nullable()->after('product_request_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', fn (Blueprint $table) => $table->dropConstrainedForeignId('reservation_id'));
        Schema::dropIfExists('reservations');
        Schema::table('stock_units', fn (Blueprint $table) => $table->dropConstrainedForeignId('batch_id'));
        Schema::dropIfExists('stock_batches');
    }
};
