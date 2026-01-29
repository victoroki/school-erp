<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Fluent;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns to inventory_categories table
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->string('category_type', 20)->default('consumable')->after('description'); // consumable or asset
            $table->string('icon', 50)->nullable()->after('category_type'); // Icon for the category
            $table->boolean('trackable')->default(true)->after('icon'); // Whether this category is trackable
            $table->string('default_location', 100)->nullable()->after('trackable'); // Default storage location
            $table->string('code', 50)->unique()->nullable()->after('default_location'); // Category code
            $table->integer('reorder_level')->default(0)->nullable()->after('code'); // Default reorder level for items in this category
            $table->integer('warranty_period')->default(0)->nullable()->after('reorder_level'); // Default warranty period for asset items in this category
        });

        // Add columns to inventory_items table
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->string('item_code', 50)->unique()->nullable()->after('description'); // Auto-generated item code
            $table->integer('reorder_quantity')->default(0)->nullable()->after('item_code'); // Quantity to reorder when low
            $table->boolean('has_expiry')->default(false)->after('reorder_quantity'); // Whether item has expiry date
            $table->string('photo', 255)->nullable()->after('has_expiry'); // Item photo
            $table->string('asset_tag', 50)->unique()->nullable()->after('photo'); // Asset tag for asset items
            $table->string('serial_number', 100)->nullable()->after('asset_tag'); // Serial number for asset items
            $table->date('purchase_date')->nullable()->after('serial_number'); // Purchase date for asset items
            $table->integer('warranty_period')->default(0)->nullable()->after('purchase_date'); // Warranty period in months for asset items
            $table->date('warranty_expiry')->nullable()->after('warranty_period'); // Warranty expiry date
            $table->enum('current_condition', ['Excellent', 'Good', 'Fair', 'Poor', 'Damaged'])->default('Good')->after('warranty_expiry'); // Current condition for asset items
            $table->integer('assigned_to')->nullable()->after('current_condition'); // Who is the item assigned to
            $table->boolean('requires_maintenance')->default(false)->after('assigned_to'); // Whether asset requires maintenance
            $table->date('next_maintenance_due')->nullable()->after('requires_maintenance'); // Next maintenance due date
            $table->string('purchase_receipt', 255)->nullable()->after('next_maintenance_due'); // Purchase receipt for asset items
        });

        // Add columns to suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('code', 50)->unique()->nullable()->after('address'); // Supplier code (auto-generated)
            $table->json('supply_categories')->nullable()->after('code'); // Categories supplied (JSON)
            $table->enum('payment_terms', ['Cash', 'Net 15', 'Net 30', 'Net 60', 'Net 90'])->default('Net 30')->after('supply_categories'); // Payment terms
            $table->boolean('is_active')->default(true)->after('payment_terms'); // Active status
            $table->integer('rating')->default(1)->after('is_active'); // Rating (1-5 stars)
            $table->string('bank_details', 500)->nullable()->after('rating'); // Bank details for payments
            $table->text('notes')->nullable()->after('bank_details'); // Notes
            $table->date('supplying_since')->nullable()->after('notes'); // Date since they started supplying
        });

        // Modify transaction_type enum to add 'adjustment' type
        // Since SQLite doesn't support changing enums directly, we'll add the column differently
        // For MySQL, we would use: $table->enum('transaction_type', ['purchase', 'issue', 'return', 'damaged', 'write_off', 'adjustment'])->change();
        
        // First drop the foreign key constraint if it exists
        if (Schema::hasTable('inventory_transactions')) {
            try {
                Schema::table('inventory_transactions', function (Blueprint $table) {
                    $table->dropForeign(['item_id']);
                });
            } catch (\Exception $e) {
                // Foreign key may not exist
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop columns from suppliers table
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'supply_categories', 'payment_terms', 'is_active', 'rating', 'bank_details', 'notes', 'supplying_since'
            ]);
        });

        // Drop columns from inventory_items table
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn([
                'item_code', 'reorder_quantity', 'has_expiry', 'photo', 'asset_tag', 'serial_number', 'purchase_date', 
                'warranty_period', 'warranty_expiry', 'current_condition', 'assigned_to', 'requires_maintenance', 'next_maintenance_due', 'purchase_receipt'
            ]);
        });

        // Drop columns from inventory_categories table
        Schema::table('inventory_categories', function (Blueprint $table) {
            $table->dropColumn([
                'category_type', 'icon', 'trackable', 'default_location', 'code', 'reorder_level', 'warranty_period'
            ]);
        });
    }
};
