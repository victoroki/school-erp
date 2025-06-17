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
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->foreign(['assignment_id'], 'assignment_submissions_ibfk_1')->references(['assignment_id'])->on('assignments')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['student_id'], 'assignment_submissions_ibfk_2')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment_submissions', function (Blueprint $table) {
            $table->dropForeign('assignment_submissions_ibfk_1');
            $table->dropForeign('assignment_submissions_ibfk_2');
        });
    }
};
