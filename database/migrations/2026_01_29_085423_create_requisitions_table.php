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
        Schema::create('requisitions', function (Blueprint $table) {
            $table->increments('requisition_id');
            $table->string('requisition_number')->unique();
            $table->unsignedInteger('requested_by');
            $table->unsignedInteger('department_id');
            $table->date('date_needed');
            $table->string('priority');
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('status')->default('Pending');
            $table->text('justification');
            $table->unsignedInteger('approved_by')->nullable();
            $table->datetime('approved_date')->nullable();
            $table->text('rejected_reason')->nullable();
            $table->datetime('fulfilled_date')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requisitions');
    }
};
