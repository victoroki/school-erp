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
        Schema::table('student_fees', function (Blueprint $table) {
            $table->foreign(['student_id'], 'student_fees_ibfk_1')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['fee_structure_id'], 'student_fees_ibfk_2')->references(['fee_structure_id'])->on('fee_structures')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_fees', function (Blueprint $table) {
            $table->dropForeign('student_fees_ibfk_1');
            $table->dropForeign('student_fees_ibfk_2');
        });
    }
};
