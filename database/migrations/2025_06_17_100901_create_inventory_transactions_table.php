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
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->integer('transaction_id', true);
            $table->integer('item_id')->nullable()->index('item_id');
            $table->enum('transaction_type', ['purchase', 'issue', 'return', 'damaged', 'write_off']);
            $table->integer('quantity');
            $table->date('transaction_date');
            $table->text('remarks')->nullable();
            $table->integer('issued_to')->nullable();
            $table->integer('handled_by')->nullable()->index('handled_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
