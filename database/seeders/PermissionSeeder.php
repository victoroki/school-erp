<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * The 43 consolidated permissions grouped by module.
     * Uses insertOrIgnore for idempotency — safe to re-run.
     */
    private array $permissions = [
        // Users (6)
        'users.view'          => 'View users list',
        'users.manage'        => 'Create, edit, and delete users',
        'roles.view'          => 'View roles list',
        'roles.manage'        => 'Create, edit, and delete roles',
        'permissions.view'    => 'View permissions list',
        'permissions.manage'  => 'Create, edit, and delete permissions',

        // Students (4)
        'students.view'       => 'View students, dashboards, reports, emergency contacts',
        'students.manage'     => 'Create, edit, delete students, enrollments, documents, promotions, transfers',
        'students.import'     => 'Bulk import / enroll students',
        'students.export'     => 'Export student data and generate ID cards',

        // Academics (3)
        'academics.view'      => 'View academic structure, schedules, calendars, dashboards',
        'academics.settings.manage' => 'Manage academic years, classes, sections, subjects, timetables, departments, terms — admin only',
        'academics.attendance.manage' => 'Mark and manage student attendance for own assigned classes',

        // Exams (10)
        'exams.marks.enter-own'    => 'Enter marks for own assigned classes and subjects',
        'exams.schedule.view'      => 'View exam schedules for own classes',
        'exams.results.view-own'   => 'View exam results for own students',
        'exams.publish'            => 'Publish and lock exam results',
        'exams.grading.manage'     => 'Manage grading scales and weighting schemes',
        'exams.results.view-all'   => 'View exam results across all classes (bypasses scoping)',
        'exams.analysis.view'      => 'View exam analysis, rankings, and performance reports',
        'exams.report-cards.export' => 'Generate and export report cards',
        'exams.approve'            => 'Approve marks and exam results',
        'exams.import'             => 'Bulk import exam marks',

        // Fees (5)
        'fees.view'           => 'View fee dashboards, assignments, adjustments, terms, discounts, reports',
        'fees.manage'         => 'Manage fee categories, structures, assignments, terms, discounts',
        'fees.approve'        => 'Approve fee adjustments',
        'fees.collect'        => 'Collect and record fee payments',
        'fees.print'          => 'Print fee receipts',

        // Finance (3)
        'finance.view'        => 'View income, expenses, bank accounts, budgets, reports, audit trail',
        'finance.manage'      => 'Manage income, expenses, bank accounts, budgets, financial years',
        'finance.approve'     => 'Approve expenses and bank reconciliations',

        // HR (3)
        'hr.view'             => 'View staff, attendance, leave, payroll, onboarding, exit, reports',
        'hr.manage'           => 'Manage staff records, documents, attendance, leave, payroll, onboarding, exit',
        'hr.approve'          => 'Approve leave applications and finalize payroll',

        // Inventory (3)
        'inventory.view'      => 'View inventory items, stock history, requisitions, purchase orders',
        'inventory.manage'    => 'Manage inventory items, stock, requisitions, purchase orders',
        'inventory.approve'   => 'Approve inventory requisitions',

        // Library (2)
        'library.view'        => 'View books, issues, categories, members',
        'library.manage'      => 'Manage books, issues, returns, categories, members',

        // Hostel (2)
        'hostel.view'         => 'View hostels, rooms, allocations, vacancy reports',
        'hostel.manage'       => 'Manage hostels, rooms, allocations',

        // Transport (2)
        'transport.view'      => 'View routes, vehicles, assignments, registrations, reports',
        'transport.manage'    => 'Manage routes, vehicles, assignments, registrations',

        // Communication (8)
        'communication.view'  => 'View communication dashboard, history, notifications, messages, templates',
        'communication.manage'=> 'Manage communication providers, templates and triggers',
        'communication.dashboard' => 'View communication dashboard',
        'communication.compose' => 'Compose and send messages',
        'communication.send' => 'Execute sending of messages',
        'communication.history.index' => 'View message history',
        'communication.history.show' => 'View specific message details',
        'audit-trail.index' => 'View system audit trails',

        // Discipline (2)
        'discipline.view'     => 'View disciplinary records and medical incidents',
        'discipline.manage'   => 'Manage disciplinary records and medical incidents',

        // Parents (2)
        'parents.view'        => 'View parents and student-parent relationships',
        'parents.manage'      => 'Manage parents and student-parent relationships',
    ];

    public function run(): void
    {
        $now = now();

        foreach ($this->permissions as $name => $description) {
            Permission::firstOrCreate(
                ['permission_name' => $name],
                ['description' => $description, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }
}
