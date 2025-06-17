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
        Schema::create('income', function (Blueprint $table) {
            $table->integer('income_id', true);
            $table->integer('category_id')->nullable()->index('category_id');
            $table->decimal('amount', 10);
            $table->date('income_date');
            $table->text('description')->nullable();
            $table->enum('payment_method', ['cash', 'check', 'bank_transfer', 'card', 'online']);
            $table->string('reference_number', 100)->nullable();
            $table->integer('received_by')->nullable()->index('received_by');
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('income');
    }
};
