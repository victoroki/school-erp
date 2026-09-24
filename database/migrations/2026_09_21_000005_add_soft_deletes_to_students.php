<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Approved migration C — soft deletes on students.
 *
 * 18 constraints reference `students`, and they split badly:
 *
 *   CASCADE (7)  ledger_entries, refunds, fee_adjustments, disciplinary_records,
 *                emergency_contacts, medical_incidents, student_siblings x2
 *                -> deleting a learner silently ERASES the fee ledger and refunds
 *   RESTRICT (10) exam_results, student_attendance, student_class_enrollments,
 *                student_documents, student_parent_relationship,
 *                transport_registrations, assignment_submissions,
 *                hostel_allocations, hostel_fee
 *                -> deleting a learner throws an uncaught QueryException (500)
 *   NO ACTION (1) student_notices
 *
 * So StudentController::destroy either 500s or destroys financial history.
 * Soft deletes fix both: the row survives, so no cascade fires and nothing is
 * blocked.
 *
 * Confirmed with the user: a re-admitted learner RESTORES the existing record
 * rather than creating a new one (the unique indexes on admission_no,
 * nemis_number and upi_number still apply to soft-deleted rows), and
 * soft-deleted learners REMAIN visible in historical reports while being
 * excluded from the active roster, attendance register and fee collection.
 *
 * Additive and reversible: one nullable timestamp plus an index.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->softDeletes();
            $table->index('deleted_at', 'students_deleted_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropIndex('students_deleted_at_index');
            $table->dropSoftDeletes();
        });
    }
};
