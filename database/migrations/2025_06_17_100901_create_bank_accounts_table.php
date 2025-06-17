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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->integer('account_id', true);
            $table->string('account_name', 100);
            $table->string('account_number', 50)->unique('account_number');
            $table->string('bank_name', 100);
            $table->string('branch_name', 100)->nullable();
            $table->string('ifsc_code', 20)->nullable();
            $table->decimal('opening_balance', 12);
            $table->decimal('current_balance', 12);
            $table->string('account_type', 50)->nullable();
            $table->dateTime('created_at')->nullable()->useCurrent();
            $table->dateTime('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
