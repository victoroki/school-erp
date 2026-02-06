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
        Schema::rename('student_class_enrollment', 'student_class_enrollments');

        Schema::table('student_class_enrollments', function (Blueprint $table) {
            if (!Schema::hasColumn('student_class_enrollments', 'is_current')) {
                $table->boolean('is_current')->default(true)->after('academic_year_id');
            }
            if (!Schema::hasColumn('student_class_enrollments', 'created_at')) {
                $table->timestamps();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_class_enrollments', function (Blueprint $table) {
            $table->dropColumn(['is_current', 'created_at', 'updated_at']);
        });
        Schema::rename('student_class_enrollments', 'student_class_enrollment');
    }
};
