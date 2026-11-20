<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            // App\Enums\CancellationCause: decides how much of the deposit was refunded.
            $table->string('cancelled_by', 32)->nullable()->after('cancel_reason');
        });
    }

    public function down(): void
    {
        Schema::table('product_requests', function (Blueprint $table) {
            $table->dropColumn('cancelled_by');
        });
    }
};
