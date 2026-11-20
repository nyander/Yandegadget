<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ledger lines the owner types in: rent, cash in the bank, loans, equipment.
        // Sales, stock, item costs and shipping are never typed here; reports read
        // those from their own tables. Amounts are signed minor units.
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_type_id')->constrained()->restrictOnDelete();
            $table->date('occurred_on')->index();
            $table->string('description');
            $table->bigInteger('amount');
            $table->char('currency', 3);
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // A wage paid at a daily, weekly or monthly rate over a period. Reports
        // prorate it into whatever date range is being looked at.
        Schema::create('staff_wages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('rate_amount');
            $table->char('rate_currency', 3);
            $table->string('period', 16);
            $table->date('starts_on');
            $table->date('ends_on')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_wages');
        Schema::dropIfExists('ledger_entries');
    }
};
