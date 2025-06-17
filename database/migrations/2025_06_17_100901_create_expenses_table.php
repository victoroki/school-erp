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
        Schema::create('expenses', function (Blueprint $table) {
            $table->integer('expense_id', true);
            $table->integer('category_id')->nullable()->index('category_id');
            $table->decimal('amount', 10);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'card', 'online']);
            $table->string('reference_number', 100)->nullable();
            $table->integer('approved_by')->nullable()->index('approved_by');
            $table->integer('created_by')->nullable()->index('created_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
