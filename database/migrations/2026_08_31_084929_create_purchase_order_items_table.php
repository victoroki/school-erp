<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * The original "final" migration accidentally created a table named
     * `purchase_order_items_table_final` instead of `purchase_order_items`.
     * This migration creates the correctly named table that the model and
     * controller expect, and drops the erroneous one.
     */
    public function up(): void
    {
        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->integer('po_item_id', true);
                $table->integer('po_id')->index('po_id');
                $table->integer('item_id')->nullable()->index('item_id');
                $table->string('item_name')->nullable();
                $table->text('description')->nullable();
                $table->integer('quantity')->default(0);
                $table->decimal('unit_price', 10, 2)->default(0);
                $table->decimal('total_price', 10, 2)->default(0);
                $table->dateTime('created_at')->nullable()->useCurrent();
                $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
            });
        }

        if (Schema::hasTable('purchase_order_items_table_final')) {
            Schema::dropIfExists('purchase_order_items_table_final');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
    }
};
