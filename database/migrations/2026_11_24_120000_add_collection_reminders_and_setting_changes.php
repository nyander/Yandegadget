<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // When the customer was reminded to collect, so each request gets one reminder.
        Schema::table('product_requests', function (Blueprint $table) {
            $table->timestamp('collection_reminded_at')->nullable()->after('ready_at');
        });

        // Who changed a shop setting, from what to what.
        Schema::create('setting_changes', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->index();
            $table->json('old_value')->nullable();
            $table->json('new_value')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('setting_changes');

        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn('collection_reminded_at');
        });
    }
};
