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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->integer('po_id', true); // Primary key
            $table->string('po_number', 50)->unique(); // Auto-generated: PO-YYYY-NNN
            $table->integer('supplier_id'); // Foreign key to suppliers
            $table->date('order_date'); // Date order was placed
            $table->date('expected_delivery_date'); // Expected delivery date
            $table->text('delivery_address'); // Where items should be delivered
            $table->decimal('sub_total', 10, 2)->default(0.00); // Subtotal amount
            $table->decimal('tax_amount', 10, 2)->default(0.00); // Tax amount
            $table->decimal('delivery_charges', 10, 2)->default(0.00); // Delivery charges
            $table->decimal('grand_total', 10, 2)->default(0.00); // Total amount
            $table->text('terms_conditions')->nullable(); // Terms and conditions
            $table->text('special_instructions')->nullable(); // Special instructions
            $table->enum('status', ['Draft', 'Pending_Approval', 'Approved', 'Sent', 'Partially_Received', 'Fully_Received', 'Cancelled'])->default('Draft');
            $table->integer('approved_by')->nullable(); // User who approved
            $table->timestamp('approved_date')->nullable(); // When approved
            $table->integer('received_by')->nullable(); // User who received
            $table->timestamp('received_date')->nullable(); // When received
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });

        // Create foreign key constraints
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
