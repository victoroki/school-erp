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
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->foreign(['student_fee_id'], 'fee_payments_ibfk_1')->references(['student_fee_id'])->on('student_fees')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['collected_by'], 'fee_payments_ibfk_2')->references(['staff_id'])->on('staff')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign('fee_payments_ibfk_1');
            $table->dropForeign('fee_payments_ibfk_2');
        });
    }
};
