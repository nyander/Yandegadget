<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Who sends it, when a supplier ships straight to Accra (not via the UK base).
        Schema::table('shipments', function (Blueprint $table) {
            $table->foreignId('supplier_id')->nullable()->after('destination_location_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('shipments', fn (Blueprint $table) => $table->dropConstrainedForeignId('supplier_id'));
    }
};
