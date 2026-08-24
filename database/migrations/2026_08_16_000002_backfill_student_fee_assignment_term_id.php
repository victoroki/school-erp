<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('student_fee_assignments')
            ->join('terms', function ($join) {
                $join->on('terms.academic_year_id', '=', 'student_fee_assignments.academic_year_id')
                    ->on('terms.code', '=', 'student_fee_assignments.term');
            })
            ->whereNull('student_fee_assignments.term_id')
            ->update(['student_fee_assignments.term_id' => DB::raw('terms.id')]);
    }

    public function down(): void
    {
        DB::table('student_fee_assignments')
            ->whereNotNull('term_id')
            ->update(['term_id' => null]);
    }
};
