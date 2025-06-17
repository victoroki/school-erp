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
        Schema::create('book_issues', function (Blueprint $table) {
            $table->integer('issue_id', true);
            $table->integer('book_id')->nullable()->index('book_id');
            $table->integer('member_id')->nullable()->index('member_id');
            $table->date('issue_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->decimal('fine_amount', 10)->nullable()->default(0);
            $table->enum('status', ['issued', 'returned', 'overdue', 'lost'])->nullable()->default('issued');
            $table->text('remarks')->nullable();
            $table->integer('issued_by')->nullable()->index('issued_by');
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
        Schema::dropIfExists('book_issues');
    }
};
