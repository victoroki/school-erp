<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update transaction_type enum to add 'adjustment' type
        // Since changing enums directly is not supported in all databases, we need to recreate the column
        
        // First, add a temporary column to hold the new values
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('transaction_type_new')->nullable();
        });
        
        // Copy existing values to the new column
        DB::statement("UPDATE inventory_transactions SET transaction_type_new = transaction_type");
        
        // Drop the old column
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
        
        // Rename the new column to the original name and set it as enum with the new values
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['purchase', 'issue', 'return', 'damaged', 'write_off', 'adjustment'])->default('issue');
        });
        
        // Copy values back
        DB::statement("UPDATE inventory_transactions SET transaction_type = transaction_type_new");
        
        // Drop the temporary column
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type_new');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert transaction_type enum back to original
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->string('transaction_type_old')->nullable();
        });
        
        DB::statement("UPDATE inventory_transactions SET transaction_type_old = transaction_type");
        
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type');
        });
        
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->enum('transaction_type', ['purchase', 'issue', 'return', 'damaged', 'write_off'])->default('issue');
        });
        
        DB::statement("UPDATE inventory_transactions SET transaction_type = transaction_type_old");
        
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropColumn('transaction_type_old');
        });
    }
};
