<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Remaps existing role_permissions from old permission names to new consolidated
 * permission names, deduplicates where multiple old permissions map to one new
 * permission, backfills newly-introduced permissions (fees.collect, etc.), and
 * drops old permission rows that have been consolidated.
 *
 * WRAPPED IN A TRANSACTION — if any step fails, nothing is committed.
 *
 * The same mapping array is used by up() and down() to guarantee deterministic rollback.
 */
return new class extends Migration
{
    /**
     * old_permission_name => new_permission_name
     * Every old permission that is being consolidated must appear here.
     * Old permissions NOT in this array are left untouched (not dropped).
     */
    private array $mapping = [
        // ── Users ──────────────────────────────────────────────
        'users.index'       => 'users.view',
        'users.create'      => 'users.manage',
        'users.edit'        => 'users.manage',
        'users.delete'      => 'users.manage',
        'roles.index'       => 'roles.view',
        'roles.create'      => 'roles.manage',
        'roles.edit'        => 'roles.manage',
        'roles.delete'      => 'roles.manage',
        'permissions.index'  => 'permissions.view',
        'permissions.create' => 'permissions.manage',
        'permissions.edit'   => 'permissions.manage',
        'permissions.delete' => 'permissions.manage',

        // ── Students ───────────────────────────────────────────
        'students.index'                    => 'students.view',
        'students.create'                   => 'students.manage',
        'students.edit'                     => 'students.manage',
        'students.delete'                   => 'students.manage',
        'students.view'                     => 'students.view',
        'students.import'                   => 'students.import',
        'students.export'                   => 'students.export',
        'students.id-card'                  => 'students.export',
        'student-class-enrollments.index'   => 'students.view',
        'student-class-enrollments.create'  => 'students.manage',
        'student-class-enrollments.edit'    => 'students.manage',
        'student-class-enrollments.delete'  => 'students.manage',
        'student-documents.index'           => 'students.view',
        'student-documents.create'          => 'students.manage',
        'student-documents.delete'          => 'students.manage',
        'student-attendance.index'          => 'students.view',
        'student-attendance.create'         => 'students.manage',
        'student-attendance.report'         => 'students.view',
        'student-promotion.index'           => 'students.view',
        'student-promotion.create'          => 'students.manage',
        'student-transfer.index'            => 'students.view',
        'student-transfer.create'           => 'students.manage',
        'student-dashboard.index'           => 'students.view',
        'student-reports.index'             => 'students.view',
        'student-reports.strength'          => 'students.view',
        'student-reports.gender'            => 'students.view',
        'student-reports.attendance'        => 'students.view',
        'emergency-contacts.index'          => 'students.view',
        'emergency-contacts.create'         => 'students.manage',
        'emergency-contacts.edit'           => 'students.manage',
        'emergency-contacts.delete'         => 'students.manage',

        // ── Academics ──────────────────────────────────────────
        'academic-years.index'       => 'academics.view',
        'academic-years.create'      => 'academics.manage',
        'academic-years.edit'        => 'academics.manage',
        'academic-years.delete'      => 'academics.manage',
        'school-classes.index'       => 'academics.view',
        'school-classes.create'      => 'academics.manage',
        'school-classes.edit'        => 'academics.manage',
        'school-classes.delete'      => 'academics.manage',
        'sections.index'            => 'academics.view',
        'sections.create'           => 'academics.manage',
        'sections.edit'             => 'academics.manage',
        'sections.delete'           => 'academics.manage',
        'class-sections.index'      => 'academics.view',
        'class-sections.create'     => 'academics.manage',
        'class-sections.edit'       => 'academics.manage',
        'class-sections.delete'     => 'academics.manage',
        'subjects.index'            => 'academics.view',
        'subjects.create'           => 'academics.manage',
        'subjects.edit'             => 'academics.manage',
        'subjects.delete'           => 'academics.manage',
        'class-subjects.index'      => 'academics.view',
        'class-subjects.create'     => 'academics.manage',
        'class-subjects.edit'       => 'academics.manage',
        'class-subjects.delete'     => 'academics.manage',
        'teacher-subjects.index'    => 'academics.view',
        'teacher-subjects.create'   => 'academics.manage',
        'teacher-subjects.edit'     => 'academics.manage',
        'teacher-subjects.delete'   => 'academics.manage',
        'periods.index'             => 'academics.view',
        'periods.create'            => 'academics.manage',
        'periods.edit'              => 'academics.manage',
        'periods.delete'            => 'academics.manage',
        'classrooms.index'          => 'academics.view',
        'classrooms.create'         => 'academics.manage',
        'classrooms.edit'           => 'academics.manage',
        'classrooms.delete'         => 'academics.manage',
        'timetables.index'          => 'academics.view',
        'timetables.create'         => 'academics.manage',
        'timetables.edit'           => 'academics.manage',
        'timetables.delete'         => 'academics.manage',
        'academic-dashboard.index'  => 'academics.view',
        'class-teachers.index'      => 'academics.view',
        'class-teachers.edit'       => 'academics.manage',
        'teacher-workload.index'    => 'academics.view',
        'academic-calendar.index'   => 'academics.view',
        'academic-calendar.create'  => 'academics.manage',
        'academic-calendar.edit'    => 'academics.manage',
        'academic-calendar.delete'  => 'academics.manage',
        // departments.* mapped under HR section (lines 297-300)

        // ── Exams ──────────────────────────────────────────────
        'exam-types.index'            => 'exams.view',
        'exam-types.create'           => 'exams.manage',
        'exam-types.edit'             => 'exams.manage',
        'exam-types.delete'           => 'exams.manage',
        'grading-scales.index'        => 'exams.view',
        'grading-scales.create'       => 'exams.manage',
        'grading-scales.edit'         => 'exams.manage',
        'grading-scales.delete'       => 'exams.manage',
        'exams.index'                 => 'exams.view',
        'exams.create'                => 'exams.manage',
        'exams.edit'                  => 'exams.manage',
        'exams.delete'                => 'exams.manage',
        'exam-schedules.index'        => 'exams.view',
        'exam-schedules.create'       => 'exams.manage',
        'exam-schedules.edit'         => 'exams.manage',
        'exam-schedules.delete'       => 'exams.manage',
        'exam-results.index'          => 'exams.view',
        'exam-results.create'         => 'exams.manage',
        'exam-results.edit'           => 'exams.manage',
        'exam-results.delete'         => 'exams.manage',
        'exam-results.import'         => 'exams.import',
        'exam-dashboard.index'        => 'exams.view',
        'grade-book.index'            => 'exams.view',
        'mark-sheets.index'           => 'exams.view',
        'marks-approval.index'        => 'exams.view',
        'marks-approval.approve'      => 'exams.approve',
        'exam-reports.individual'     => 'exams.view',
        'exam-reports.generate'       => 'exams.export',
        'exam-reports.bulk'           => 'exams.export',
        'exam-analysis.performance'   => 'exams.view',
        'exam-analysis.subject'       => 'exams.view',
        'exam-analysis.rankings'      => 'exams.view',
        'assessment-types.index'      => 'exams.view',
        'assessment-types.create'     => 'exams.manage',
        'assessment-types.edit'       => 'exams.manage',
        'assessment-types.delete'     => 'exams.manage',
        'exam-rooms.index'            => 'exams.view',
        'exam-rooms.create'           => 'exams.manage',
        'exam-rooms.edit'             => 'exams.manage',
        'exam-rooms.delete'           => 'exams.manage',
        'report-card-templates.index' => 'exams.view',
        'report-card-templates.create'=> 'exams.manage',
        'report-card-templates.edit'  => 'exams.manage',
        'report-card-templates.delete'=> 'exams.manage',
        'learning-areas.index'        => 'exams.view',
        'learning-areas.create'       => 'exams.manage',
        'learning-areas.edit'         => 'exams.manage',
        'learning-areas.delete'       => 'exams.manage',
        'strands.index'               => 'exams.view',
        'strands.create'              => 'exams.manage',
        'strands.edit'                => 'exams.manage',
        'strands.delete'              => 'exams.manage',
        'sub-strands.index'           => 'exams.view',
        'sub-strands.create'          => 'exams.manage',
        'sub-strands.edit'            => 'exams.manage',
        'sub-strands.delete'          => 'exams.manage',
        'cbc-assessments.index'       => 'exams.view',
        'cbc-assessments.create'      => 'exams.manage',

        // ── Fees ───────────────────────────────────────────────
        'fees.dashboard'                      => 'fees.view',
        'fees.assignments.index'              => 'fees.view',
        'fees.assignments.create'             => 'fees.manage',
        'fees.assignments.store'              => 'fees.manage',
        'fees.assignments.destroy'            => 'fees.manage',
        'fees.assignments.student-summary'    => 'fees.view',
        'fees.assignments.unassigned'         => 'fees.view',
        'fees.adjustments.index'              => 'fees.view',
        'fees.adjustments.create'             => 'fees.manage',
        'fees.adjustments.store'              => 'fees.manage',
        'fees.adjustments.show'               => 'fees.view',
        'fees.adjustments.approve'            => 'fees.approve',
        'fees.adjustments.reject'             => 'fees.approve',
        'fees.adjustments.pending'            => 'fees.view',
        'fees.adjustments.student-adjustments'=> 'fees.view',
        'fees.adjustments.audit-log'          => 'fees.view',
        'fees.terms.index'                    => 'fees.view',
        'fees.terms.create'                   => 'fees.manage',
        'fees.terms.store'                    => 'fees.manage',
        'fees.terms.show'                     => 'fees.view',
        'fees.terms.edit'                     => 'fees.manage',
        'fees.terms.update'                   => 'fees.manage',
        'fees.terms.destroy'                  => 'fees.manage',
        'fees.terms.activate'                 => 'fees.view',
        'fees.discounts.index'                => 'fees.view',
        'fees.discounts.create'               => 'fees.manage',
        'fees.discounts.store'                => 'fees.manage',
        'fees.discounts.show'                 => 'fees.view',
        'fees.discounts.edit'                 => 'fees.manage',
        'fees.discounts.update'               => 'fees.manage',
        'fees.discounts.destroy'              => 'fees.manage',
        'fees.reports.expected-revenue'       => 'fees.view',
        'fees.reports.assignment-status'      => 'fees.view',
        'fees.reports.discount-summary'       => 'fees.view',
        'fee-management.index'                => 'fees.view',
        'fee-management.show'                 => 'fees.view',
        'fee-management.collect-payment'      => 'fees.collect',
        'fee-management.store-payment'        => 'fees.collect',
        'fee-management.print'                => 'fees.print',
        'fee-categories.index'                => 'fees.view',
        'fee-categories.create'               => 'fees.manage',
        'fee-categories.edit'                 => 'fees.manage',
        'fee-categories.delete'               => 'fees.manage',
        'fee-structures.index'                => 'fees.view',
        'fee-structures.create'               => 'fees.manage',
        'fee-structures.edit'                 => 'fees.manage',
        'fee-structures.delete'               => 'fees.manage',

        // ── Finance ────────────────────────────────────────────
        'finance.dashboard'              => 'finance.view',
        'finance.reports.index'          => 'finance.view',
        'finance.reports.cashflow'       => 'finance.view',
        'finance.reports.p-and-l'        => 'finance.view',
        'income.index'                   => 'finance.view',
        'income.create'                  => 'finance.manage',
        'income.edit'                    => 'finance.manage',
        'income.delete'                  => 'finance.manage',
        'expenses.index'                 => 'finance.view',
        'expenses.create'                => 'finance.manage',
        'expenses.edit'                  => 'finance.manage',
        'expenses.delete'                => 'finance.manage',
        'expenses.pending'               => 'finance.view',
        'expenses.approve'               => 'finance.approve',
        'expenses.pay'                   => 'finance.manage',
        'expense-categories.index'       => 'finance.view',
        'expense-categories.create'      => 'finance.manage',
        'expense-categories.edit'        => 'finance.manage',
        'expense-categories.delete'      => 'finance.manage',
        'income-categories.index'        => 'finance.view',
        'income-categories.create'       => 'finance.manage',
        'income-categories.edit'         => 'finance.manage',
        'income-categories.delete'       => 'finance.manage',
        'bank-accounts.index'            => 'finance.view',
        'bank-accounts.create'           => 'finance.manage',
        'bank-accounts.edit'             => 'finance.manage',
        'bank-accounts.delete'           => 'finance.manage',
        'bank-transactions.index'        => 'finance.view',
        'bank-transactions.create'       => 'finance.manage',
        'bank-transactions.edit'         => 'finance.manage',
        'bank-transactions.delete'       => 'finance.manage',
        'bank-reconciliations.index'     => 'finance.view',
        'bank-reconciliations.create'    => 'finance.approve',
        'bank-reconciliations.edit'      => 'finance.approve',
        'bank-reconciliations.delete'    => 'finance.approve',
        'budgets.index'                  => 'finance.view',
        'budgets.create'                 => 'finance.manage',
        'budgets.edit'                   => 'finance.manage',
        'budgets.delete'                 => 'finance.manage',
        'budgets.vs-actual'              => 'finance.view',
        'financial-reports.index'        => 'finance.view',
        'financial-reports.cashflow'     => 'finance.view',
        'financial-reports.p-and-l'      => 'finance.view',
        'financial-years.index'          => 'finance.view',
        'financial-years.create'         => 'finance.manage',
        'financial-years.edit'           => 'finance.manage',
        'financial-years.delete'         => 'finance.manage',
        'suppliers.index'                => 'finance.view',
        'suppliers.create'               => 'finance.manage',
        'suppliers.edit'                 => 'finance.manage',
        'suppliers.delete'               => 'finance.manage',
        'audit-trail.index'              => 'finance.view',

        // ── HR ─────────────────────────────────────────────────
        'staff.index'                    => 'hr.view',
        'staff.create'                   => 'hr.manage',
        'staff.edit'                     => 'hr.manage',
        'staff.delete'                   => 'hr.manage',
        'staff.directory'                => 'hr.view',
        'staff-documents.index'          => 'hr.view',
        'staff-documents.create'         => 'hr.manage',
        'staff-documents.delete'         => 'hr.manage',
        'departments.index'              => 'hr.view',
        'departments.create'             => 'hr.manage',
        'departments.edit'               => 'hr.manage',
        'departments.delete'             => 'hr.manage',
        'job-positions.index'            => 'hr.view',
        'job-positions.create'           => 'hr.manage',
        'job-positions.edit'             => 'hr.manage',
        'job-positions.delete'           => 'hr.manage',
        'leave-types.index'              => 'hr.view',
        'leave-types.create'             => 'hr.manage',
        'leave-types.edit'               => 'hr.manage',
        'leave-types.delete'             => 'hr.manage',
        'leave-applications.index'       => 'hr.view',
        'leave-applications.create'      => 'hr.manage',
        'leave-applications.edit'        => 'hr.manage',
        'leave-applications.delete'      => 'hr.manage',
        'leave-applications.approve'     => 'hr.approve',
        'leave-applications.reject'      => 'hr.approve',
        'staff-attendance.index'         => 'hr.view',
        'staff-attendance.create'        => 'hr.manage',
        'staff-attendance.edit'          => 'hr.manage',
        'staff-attendance.delete'        => 'hr.manage',
        'payrolls.index'                 => 'hr.view',
        'payrolls.create'                => 'hr.manage',
        'payrolls.edit'                  => 'hr.manage',
        'payrolls.delete'                => 'hr.manage',
        'payroll-processing.index'       => 'hr.view',
        'payroll-processing.create'      => 'hr.manage',
        'payroll-processing.calculate'   => 'hr.manage',
        'payroll-processing.review'      => 'hr.approve',
        'payroll-processing.finalize'    => 'hr.approve',
        'hr.dashboard'                   => 'hr.view',
        'hr.reports.headcount'           => 'hr.view',
        'hr.reports.payroll'             => 'hr.view',
        'hr.reports.leave'               => 'hr.view',
        'hr.reports.attendance'          => 'hr.view',
        'hr.onboarding'                  => 'hr.view',
        'hr.onboarding.show'             => 'hr.view',
        'hr.onboarding.complete-item'    => 'hr.manage',
        'hr.exit'                        => 'hr.view',
        'hr.exit.create'                 => 'hr.manage',
        'hr.exit.store'                  => 'hr.manage',

        // ── Inventory ──────────────────────────────────────────
        'inventory.dashboard'                   => 'inventory.view',
        'inventory-items.index'                 => 'inventory.view',
        'inventory-items.create'                => 'inventory.manage',
        'inventory-items.edit'                  => 'inventory.manage',
        'inventory-items.delete'                => 'inventory.manage',
        'inventory-categories.index'            => 'inventory.view',
        'inventory-categories.create'           => 'inventory.manage',
        'inventory-categories.edit'             => 'inventory.manage',
        'inventory-categories.delete'           => 'inventory.manage',
        'inventory.add-stock'                   => 'inventory.manage',
        'inventory.issue-stock'                 => 'inventory.manage',
        'inventory.adjust-stock'                => 'inventory.manage',
        'inventory.stock-movement-history'      => 'inventory.view',
        'inventory.requisitions.index'          => 'inventory.view',
        'inventory.requisitions.create'         => 'inventory.manage',
        'inventory.requisitions.edit'           => 'inventory.manage',
        'inventory.requisitions.delete'         => 'inventory.manage',
        'inventory.requisitions.approve'        => 'inventory.approve',
        'inventory.purchase-orders.index'       => 'inventory.view',
        'inventory.purchase-orders.create'      => 'inventory.manage',
        'inventory.purchase-orders.edit'        => 'inventory.manage',
        'inventory.purchase-orders.delete'      => 'inventory.manage',
        'inventory.purchase-orders.receive'     => 'inventory.manage',

        // ── Library ────────────────────────────────────────────
        'books.index'              => 'library.view',
        'books.create'             => 'library.manage',
        'books.edit'               => 'library.manage',
        'books.delete'             => 'library.manage',
        'book-issues.index'        => 'library.view',
        'book-issues.create'       => 'library.manage',
        'book-issues.edit'         => 'library.manage',
        'book-issues.delete'       => 'library.manage',
        'book-issues.return'       => 'library.manage',
        'book-categories.index'    => 'library.view',
        'book-categories.create'   => 'library.manage',
        'book-categories.edit'     => 'library.manage',
        'book-categories.delete'   => 'library.manage',
        'library-members.index'    => 'library.view',
        'library-members.create'   => 'library.manage',
        'library-members.edit'     => 'library.manage',
        'library-members.delete'   => 'library.manage',
        'library.dashboard'        => 'library.view',

        // ── Hostel ─────────────────────────────────────────────
        'hostels.index'                      => 'hostel.view',
        'hostels.create'                     => 'hostel.manage',
        'hostels.edit'                       => 'hostel.manage',
        'hostels.delete'                     => 'hostel.manage',
        'hostel-rooms.index'                 => 'hostel.view',
        'hostel-rooms.create'                => 'hostel.manage',
        'hostel-rooms.edit'                  => 'hostel.manage',
        'hostel-rooms.delete'                => 'hostel.manage',
        'hostel-allocations.index'           => 'hostel.view',
        'hostel-allocations.create'          => 'hostel.manage',
        'hostel-allocations.edit'            => 'hostel.manage',
        'hostel-allocations.delete'          => 'hostel.manage',
        'hostel-allocations.checkout'        => 'hostel.manage',
        'hostel-allocations.bulk-form'       => 'hostel.view',
        'hostel-allocations.bulk-store'      => 'hostel.manage',
        'hostel-allocations.transfer-form'   => 'hostel.view',
        'hostel-allocations.transfer-store'  => 'hostel.manage',
        'hostel.dashboard'                   => 'hostel.view',
        'hostel.reports'                     => 'hostel.view',
        'hostel.vacancy-report'              => 'hostel.view',
        'hostel.student-list'                => 'hostel.view',

        // ── Transport ──────────────────────────────────────────
        'routes.index'                       => 'transport.view',
        'routes.create'                      => 'transport.manage',
        'routes.edit'                        => 'transport.manage',
        'routes.delete'                      => 'transport.manage',
        'route-stops.index'                  => 'transport.view',
        'route-stops.create'                 => 'transport.manage',
        'route-stops.edit'                   => 'transport.manage',
        'route-stops.delete'                 => 'transport.manage',
        'vehicles.index'                     => 'transport.view',
        'vehicles.create'                    => 'transport.manage',
        'vehicles.edit'                      => 'transport.manage',
        'vehicles.delete'                    => 'transport.manage',
        'transport-assignments.index'        => 'transport.view',
        'transport-assignments.create'       => 'transport.manage',
        'transport-assignments.edit'         => 'transport.manage',
        'transport-assignments.delete'       => 'transport.manage',
        'transport-registrations.index'      => 'transport.view',
        'transport-registrations.create'     => 'transport.manage',
        'transport-registrations.edit'       => 'transport.manage',
        'transport-registrations.delete'     => 'transport.manage',
        'student-transport-assignments.index'    => 'transport.view',
        'student-transport-assignments.create'   => 'transport.manage',
        'student-transport-assignments.edit'     => 'transport.manage',
        'student-transport-assignments.delete'   => 'transport.manage',
        'transportation.dashboard'            => 'transport.view',
        'transportation.reports.index'        => 'transport.view',
        'transportation.reports.route-wise'   => 'transport.view',
        'transportation.reports.occupancy'    => 'transport.view',

        // ── Communication ──────────────────────────────────────
        'communication.dashboard'    => 'communication.view',
        'communication.compose'      => 'communication.manage',
        'communication.send'         => 'communication.manage',
        'communication.history.index'=> 'communication.view',
        'communication.history.show' => 'communication.view',
        'notifications.index'        => 'communication.view',
        'notifications.create'       => 'communication.manage',
        'notifications.edit'         => 'communication.manage',
        'notifications.delete'       => 'communication.manage',
        'messages.index'             => 'communication.view',
        'messages.create'            => 'communication.manage',
        'messages.edit'              => 'communication.manage',
        'messages.delete'            => 'communication.manage',
        'sms-templates.index'        => 'communication.view',
        'sms-templates.create'       => 'communication.manage',
        'sms-templates.edit'         => 'communication.manage',
        'sms-templates.delete'       => 'communication.manage',
        'email-templates.index'      => 'communication.view',
        'email-templates.create'     => 'communication.manage',
        'email-templates.edit'       => 'communication.manage',
        'email-templates.delete'     => 'communication.manage',

        // ── Discipline ─────────────────────────────────────────
        'disciplinary-records.index'   => 'discipline.view',
        'disciplinary-records.create'  => 'discipline.manage',
        'disciplinary-records.edit'    => 'discipline.manage',
        'disciplinary-records.delete'  => 'discipline.manage',
        'medical-incidents.index'      => 'discipline.view',
        'medical-incidents.create'     => 'discipline.manage',
        'medical-incidents.edit'       => 'discipline.manage',
        'medical-incidents.delete'     => 'discipline.manage',

        // ── Parents ────────────────────────────────────────────
        'parents.index'                       => 'parents.view',
        'parents.create'                      => 'parents.manage',
        'parents.edit'                        => 'parents.manage',
        'parents.delete'                      => 'parents.manage',
        'student-parent-relationships.index'  => 'parents.view',
        'student-parent-relationships.create' => 'parents.manage',
        'student-parent-relationships.edit'   => 'parents.manage',
        'student-parent-relationships.delete' => 'parents.manage',
    ];

    public function up(): void
    {
        DB::transaction(function () {
            // Build lookup: old_permission_id => old_permission_name
            $oldPerms = DB::table('permissions')
                ->pluck('permission_name', 'permission_id')
                ->toArray();

            // Build lookup: new_permission_name => new_permission_id
            $newPerms = DB::table('permissions')
                ->whereIn('permission_name', array_values($this->mapping))
                ->pluck('permission_id', 'permission_name')
                ->toArray();

            // Get all existing role_permissions rows
            $rows = DB::table('role_permissions')
                ->select('role_id', 'permission_id')
                ->get();

            foreach ($rows as $row) {
                $oldName = $oldPerms[$row->permission_id] ?? null;
                if ($oldName === null || !isset($this->mapping[$oldName])) {
                    continue; // not a mapped permission — leave untouched
                }

                $newName = $this->mapping[$oldName];
                if (!isset($newPerms[$newName])) {
                    continue; // new permission doesn't exist — skip
                }

                $newPermissionId = $newPerms[$newName];

                // Check if this (role_id, new_permission_id) pair already exists
                $exists = DB::table('role_permissions')
                    ->where('role_id', $row->role_id)
                    ->where('permission_id', $newPermissionId)
                    ->exists();

                if ($exists) {
                    // Duplicate — delete the old row
                    DB::table('role_permissions')
                        ->where('role_id', $row->role_id)
                        ->where('permission_id', $row->permission_id)
                        ->delete();
                } else {
                    // Reuse the existing row — update its permission_id
                    DB::table('role_permissions')
                        ->where('role_id', $row->role_id)
                        ->where('permission_id', $row->permission_id)
                        ->update(['permission_id' => $newPermissionId]);
                }
            }

            // Drop old permissions that have been consolidated
            // (Only those that appear as KEYS in the mapping — values stay)
            $oldNames = array_keys($this->mapping);
            DB::table('permissions')
                ->whereIn('permission_name', $oldNames)
                ->delete();
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            // 1. Recreate old permission rows
            $now = now();
            foreach (array_keys($this->mapping) as $oldName) {
                DB::table('permissions')->insertOrIgnore([
                    'permission_name' => $oldName,
                    'description'     => "[Legacy] {$oldName}",
                    'created_at'      => $now,
                    'updated_at'      => $now,
                ]);
            }

            // 2. Reverse-map role_permissions: new_permission_id => first old_permission_id
            //    We map each new permission back to ONE old permission (the canonical
            //    representative) so the reverse is deterministic.
            $oldPerms = DB::table('permissions')
                ->whereIn('permission_name', array_keys($this->mapping))
                ->pluck('permission_id', 'permission_name')
                ->toArray();

            $newPerms = DB::table('permissions')
                ->whereIn('permission_name', array_values($this->mapping))
                ->pluck('permission_id', 'permission_name')
                ->toArray();

            // Build reverse map: new_permission_name => first old_permission_name
            // (deterministic — always picks the first old permission for each new one)
            $reverseMap = [];
            foreach ($this->mapping as $oldName => $newName) {
                if (!isset($reverseMap[$newName])) {
                    $reverseMap[$newName] = $oldName;
                }
            }

            // Remap role_permissions rows
            $rows = DB::table('role_permissions')
                ->select('id', 'role_id', 'permission_id')
                ->get();

            foreach ($rows as $row) {
                $currentName = null;
                foreach ($newPerms as $name => $pid) {
                    if ($pid === $row->permission_id) {
                        $currentName = $name;
                        break;
                    }
                }

                if ($currentName === null || !isset($reverseMap[$currentName])) {
                    continue;
                }

                $oldName = $reverseMap[$currentName];
                if (!isset($oldPerms[$oldName])) {
                    continue;
                }

                $oldPermissionId = $oldPerms[$oldName];

                $exists = DB::table('role_permissions')
                    ->where('role_id', $row->role_id)
                    ->where('permission_id', $oldPermissionId)
                    ->exists();

                if ($exists) {
                    DB::table('role_permissions')->where('id', $row->id)->delete();
                } else {
                    DB::table('role_permissions')
                        ->where('id', $row->id)
                        ->update(['permission_id' => $oldPermissionId]);
                }
            }

            // 3. Remove the new consolidated permissions
            DB::table('permissions')
                ->whereIn('permission_name', array_values($this->mapping))
                ->delete();
        });
    }
};
