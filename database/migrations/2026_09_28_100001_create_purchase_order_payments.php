<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Purchase-order payment / credit workflow.
 *
 * Receiving goods never moves money on its own any more — it records a
 * PAYABLE when the order was placed on credit, and the money only leaves a
 * bank account when a purchase_order_payments row is written.
 *
 * Legacy POs keep NULL payment_arrangement: they were received before the
 * concept existed, so the UI shows them without a payment demand rather than
 * marking them "unpaid" (which would invent payables out of history).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // immediate = paid on receipt; credit = pay later. NULL = legacy row.
            $table->enum('payment_arrangement', ['immediate', 'credit'])->nullable()->after('status');
            $table->date('payment_due_date')->nullable()->after('payment_arrangement');
            $table->string('credit_terms', 50)->nullable()->after('payment_due_date');
            $table->timestamp('paid_date')->nullable()->after('credit_terms');
        });

        Schema::create('purchase_order_payments', function (Blueprint $table) {
            $table->id();
            $table->integer('po_id');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'card', 'online'])->default('cash');
            $table->integer('bank_account_id')->nullable();
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->integer('created_by')->nullable();
            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('bank_account_id')->references('account_id')->on('bank_accounts')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['po_id', 'payment_date']);
        });

        // Paid-in-full legacy POs (paid expenses exist for none of them, but
        // guard anyway) gain no backfill: paid_date stays NULL for history.
        DB::statement("UPDATE purchase_orders SET payment_arrangement = NULL WHERE payment_arrangement IS NULL");
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_payments');

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_arrangement', 'payment_due_date', 'credit_terms', 'paid_date']);
        });
    }
};
