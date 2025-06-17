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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->integer('item_id', true);
            $table->string('name');
            $table->integer('category_id')->nullable()->index('category_id');
            $table->integer('quantity')->default(0);
            $table->string('unit', 20)->nullable();
            $table->integer('minimum_quantity')->nullable()->default(0);
            $table->decimal('cost_per_unit', 10)->nullable();
            $table->integer('supplier_id')->nullable();
            $table->string('location', 100)->nullable();
            $table->text('description')->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
