<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('conditions', function (Blueprint $table) {
            $table->id();
            $table->string('grade', 8)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            // Deposit a customer pays to request an item in this condition, in minor units.
            $table->unsignedInteger('deposit_amount');
            $table->char('deposit_currency', 3);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('transaction_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category', 32)->index();
            $table->text('description')->nullable();
            // True when reports compute this figure from stock or sales rather than ledger lines.
            $table->boolean('derived_in_reports')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_types');
        Schema::dropIfExists('conditions');
        Schema::dropIfExists('categories');
    }
};
