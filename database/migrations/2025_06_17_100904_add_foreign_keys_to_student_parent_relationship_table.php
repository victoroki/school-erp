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
        Schema::table('student_parent_relationship', function (Blueprint $table) {
            $table->foreign(['student_id'], 'student_parent_relationship_ibfk_1')->references(['student_id'])->on('students')->onUpdate('restrict')->onDelete('restrict');
            $table->foreign(['parent_id'], 'student_parent_relationship_ibfk_2')->references(['parent_id'])->on('parents')->onUpdate('restrict')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_parent_relationship', function (Blueprint $table) {
            $table->dropForeign('student_parent_relationship_ibfk_1');
            $table->dropForeign('student_parent_relationship_ibfk_2');
        });
    }
};
