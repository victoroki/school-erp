<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Check if column exists first or just add it (standard approach is to add if missing)
            if (!Schema::hasColumn('inventory_transactions', 'balance_after')) {
                $table->integer('balance_after')->nullable()->after('quantity');
            }
            
            // Update enum types if supported, but mysql change column is better for enums
            // For simplicity in this env, we might just use string or keep existing enum if it's restrictive.
            // Let's change transaction_type to string to be more flexible
            $table->string('transaction_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('balance_after');
        });
    }
};
