<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->unsignedBigInteger('student_fee_assignment_id')->nullable()->after('student_fee_id');
            $table->foreign('student_fee_assignment_id')->references('id')->on('student_fee_assignments')->nullOnDelete();
        });

        DB::statement('
            UPDATE fee_payments fp
            INNER JOIN student_fee_assignments sfa ON fp.student_fee_id = (
                SELECT student_fee_id FROM student_fees sf
                WHERE sf.student_id = sfa.student_id
                AND sf.fee_structure_id = sfa.fee_structure_id
                LIMIT 1
            )
            SET fp.student_fee_assignment_id = sfa.id
        ');
    }

    public function down(): void
    {
        Schema::table('fee_payments', function (Blueprint $table) {
            $table->dropForeign(['student_fee_assignment_id']);
            $table->dropColumn('student_fee_assignment_id');
        });
    }
};
