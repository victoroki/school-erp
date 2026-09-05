<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sidebar Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Each section contains menu items. Each item has:
    |   - key:        Unique identifier
    |   - label:      Display text
    |   - icon:       FontAwesome class (without 'fas fa-' prefix for brevity)
    |   - color:      Text color class (e.g., 'text-primary')
    |   - route:      Route name for the link
    |   - active:     URL pattern(s) for active state detection (string or array)
    |   - permission: Required permission name(s) — string or array (OR logic)
    |   - children:   Nested sub-items (same structure)
    |   - header:     Sub-section header text (rendered as <li class="nav-header small">)
    |
    | Permission check: item is visible if user has ANY of the listed permissions.
    | Parent item auto-hides when ALL children are hidden.
    |
    */

    'sections' => [

        // ─── CORE DASHBOARD ───────────────────────────────────────
        [
            'header' => 'CORE DASHBOARD',
        ],
        [
            'key'      => 'dashboard',
            'label'    => 'Dashboard',
            'icon'     => 'fas fa-tachometer-alt',
            'color'    => 'text-primary',
            'route'    => 'home',
            'active'   => ['home', 'dashboard*'],
            'permission' => [], // always visible
        ],

        // ─── USER MANAGEMENT ──────────────────────────────────────
        [
            'key'      => 'user-management',
            'label'    => 'User Management',
            'icon'     => 'fas fa-user-shield',
            'color'    => 'text-indigo',
            'active'   => ['users*', 'roles*'],
            'permission' => ['users.view', 'users.manage'],
            'children' => [
                [
                    'key'        => 'users',
                    'label'      => 'Users',
                    'icon'       => 'fas fa-users',
                    'color'      => 'text-indigo',
                    'route'      => 'users.index',
                    'active'     => 'users*',
                    'permission' => ['users.view', 'users.manage'],
                ],
                [
                    'key'        => 'roles',
                    'label'      => 'Roles',
                    'icon'       => 'fas fa-user-tag',
                    'color'      => 'text-indigo',
                    'route'      => 'roles.index',
                    'active'     => 'roles*',
                    'permission' => ['users.view', 'users.manage'],
                ],
            ],
        ],

        // ─── EDUCATIONAL UNITS ────────────────────────────────────
        [
            'header' => 'EDUCATIONAL UNITS',
        ],

        // Academic Management
        [
            'key'      => 'academics',
            'label'    => 'Academic Management',
            'icon'     => 'fas fa-graduation-cap',
            'color'    => 'text-info',
            'active'   => ['academic-dashboard*', 'academic-years*', 'school-classes*', 'sections*',
                           'class-sections*', 'subjects*', 'class-subjects*', 'teacher-subjects*',
                           'periods*', 'classrooms*', 'timetables*', 'academic-calendar*',
                           'class-teachers*', 'teacher-workload*', 'teacher-onboarding*',
                           'api/subjects*', 'api/academic-years*'],
            'permission' => ['academics.view', 'academics.settings.manage', 'academics.attendance.manage'],
            'children' => [
                ['key' => 'academic-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line', 'color' => 'text-info',
                 'route' => 'academic-dashboard.index', 'active' => 'academic-dashboard*', 'permission' => ['academics.view']],

                ['header' => 'Structure', 'color' => 'text-info'],
                ['key' => 'academic-years', 'label' => 'Academic Years', 'icon' => 'far fa-calendar', 'color' => 'text-info',
                 'route' => 'academic-years.index', 'active' => 'academic-years*', 'permission' => ['academics.settings.manage']],
                ['key' => 'school-classes', 'label' => 'Classes', 'icon' => 'fas fa-chalkboard', 'color' => 'text-info',
                 'route' => 'school-classes.index', 'active' => ['school-classes*', 'sections*'], 'permission' => ['academics.settings.manage']],
                // Class Rosters removed — duplicates Classes functionality
                ['key' => 'classrooms', 'label' => 'Classrooms', 'icon' => 'fas fa-door-open', 'color' => 'text-info',
                 'route' => 'classrooms.index', 'active' => 'classrooms*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Curriculum', 'color' => 'text-info'],
                ['key' => 'subjects', 'label' => 'Subjects', 'icon' => 'fas fa-book-open', 'color' => 'text-info',
                 'route' => 'subjects.index', 'active' => 'subjects*', 'permission' => ['academics.settings.manage']],
                ['key' => 'class-subjects', 'label' => 'Class Subjects', 'icon' => 'fas fa-link', 'color' => 'text-info',
                 'route' => 'class-subjects.index', 'active' => 'class-subjects*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Scheduling', 'color' => 'text-info'],
                ['key' => 'periods', 'label' => 'Periods', 'icon' => 'fas fa-clock', 'color' => 'text-info',
                 'route' => 'periods.index', 'active' => 'periods*', 'permission' => ['academics.settings.manage']],
                ['key' => 'timetables', 'label' => 'Master Timetables', 'icon' => 'fas fa-calendar-alt', 'color' => 'text-info',
                 'route' => 'timetables.index', 'active' => 'timetables*', 'permission' => ['academics.settings.manage']],
                ['key' => 'timetables-teacher', 'label' => 'Teacher Timetables', 'icon' => 'fas fa-user-clock', 'color' => 'text-info',
                 'route' => 'timetables.teacher', 'active' => 'timetables/teacher*', 'permission' => ['academics.settings.manage']],
                ['key' => 'academic-calendar', 'label' => 'Academic Calendar', 'icon' => 'fas fa-calendar-check', 'color' => 'text-info',
                 'route' => 'academic-calendar.index', 'active' => 'academic-calendar*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Administration', 'color' => 'text-info'],
                ['key' => 'departments-academic', 'label' => 'Departments', 'icon' => 'far fa-building', 'color' => 'text-info',
                 'route' => 'departments.index', 'active' => 'departments*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Teachers', 'color' => 'text-info'],
                // Teacher Onboarding removed — covered by Teacher Management Add button
                ['key' => 'teacher-management', 'label' => 'Teacher Management', 'icon' => 'fas fa-user-tie', 'color' => 'text-info',
                 'route' => 'teacher-management.index', 'active' => 'teacher-management*', 'module' => 'academic-teacher-management', 'permission' => ['academics.settings.manage']],
                ['key' => 'teacher-subjects', 'label' => 'Teacher Subjects', 'icon' => 'fas fa-user-tie', 'color' => 'text-info',
                 'route' => 'teacher-subjects.index', 'active' => 'teacher-subjects*', 'permission' => ['academics.settings.manage']],
                ['key' => 'class-teachers', 'label' => 'Class Teachers', 'icon' => 'fas fa-chalkboard-teacher', 'color' => 'text-info',
                 'route' => 'class-teachers.index', 'active' => 'class-teachers*', 'permission' => ['academics.settings.manage']],
                ['key' => 'teacher-workload', 'label' => 'Teacher Workload', 'icon' => 'fas fa-tasks', 'color' => 'text-info',
                 'route' => 'teacher-workload.index', 'active' => 'teacher-workload*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Teacher Tools', 'color' => 'text-info'],
                ['key' => 'my-timetable', 'label' => 'My Timetable', 'icon' => 'fas fa-calendar-alt', 'color' => 'text-info',
                 'route' => 'timetables.teacher', 'active' => 'timetables/teacher*', 'permission' => ['academics.view', 'exams.schedule.view'], 'roles' => ['Teacher']],
                ['key' => 'leave-apply', 'label' => 'Apply for Leave', 'icon' => 'fas fa-calendar-plus', 'color' => 'text-info',
                 'route' => 'leave-applications.create', 'active' => 'leave-applications*', 'permission' => ['hr.leave.apply'], 'roles' => ['Teacher']],
            ],
        ],

        // Student Management
        [
            'key'      => 'students',
            'label'    => 'Student Management',
            'icon'     => 'fas fa-user-graduate',
            'color'    => 'text-warning',
            'active'   => ['student-dashboard*', 'students*', 'student-class-enrollments*', 'student-unassigned*',
                           'student-parent-relationships*', 'parents*', 'disciplinary-records*',
                           'medical-incidents*', 'emergency-contacts*', 'student-transfer*',
                           'student-attendance*', 'student-reports*',
                           'student-promotion*', 'student-documents*'],
            'permission' => ['students.view', 'students.manage', 'students.import', 'students.export'],
            'children' => [
                ['key' => 'student-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line', 'color' => 'text-warning',
                 'route' => 'student-dashboard.index', 'active' => 'student-dashboard*', 'permission' => ['students.view']],

                ['header' => 'Student Records', 'color' => 'text-warning'],
                ['key' => 'students-all', 'label' => 'All Students', 'icon' => 'fas fa-users', 'color' => 'text-warning',
                 'route' => 'students.index', 'active' => ['students', 'students/show*'], 'permission' => ['students.view', 'students.manage']],
                ['key' => 'students-create', 'label' => 'Student Admission', 'icon' => 'fas fa-user-plus', 'color' => 'text-warning',
                 'route' => 'students.create', 'active' => 'students/create', 'permission' => ['students.manage']],

                ['header' => 'Enrollment & Classes', 'color' => 'text-warning'],
                ['key' => 'student-import', 'label' => 'Bulk Enrollment', 'icon' => 'fas fa-users-cog', 'color' => 'text-warning',
                 'route' => 'students.import', 'active' => 'students/import', 'permission' => ['students.import']],
                ['key' => 'student-promotion', 'label' => 'Student Promotion', 'icon' => 'fas fa-level-up-alt', 'color' => 'text-warning',
                 'route' => 'student-promotion.index', 'active' => 'student-promotion*', 'permission' => ['students.manage']],
                ['key' => 'student-transfer', 'label' => 'Student Transfer', 'icon' => 'fas fa-exchange-alt', 'color' => 'text-warning',
                 'route' => 'student-transfer.index', 'active' => 'student-transfer*', 'permission' => ['students.manage']],
                // Unassigned Students removed — not in use
                ['key' => 'emergency-contacts', 'label' => 'Emergency Contact', 'icon' => 'fas fa-hospital-user', 'color' => 'text-warning',
                 'route' => 'emergencyContacts.index', 'active' => 'emergency-contacts*', 'permission' => ['students.view', 'students.manage']],

                ['header' => 'Family & Guardians', 'color' => 'text-warning'],
                ['key' => 'parents', 'label' => 'Parents/Guardians', 'icon' => 'fas fa-user-friends', 'color' => 'text-warning',
                 'route' => 'parents.index', 'active' => 'parents*', 'permission' => ['students.view', 'students.manage']],
                ['key' => 'student-parent-rels', 'label' => 'Parent Relationships', 'icon' => 'fas fa-link', 'color' => 'text-warning',
                 'route' => 'student-parent-relationships.index', 'active' => 'student-parent-relationships*', 'permission' => ['students.view', 'students.manage']],

                ['header' => 'Student Services', 'color' => 'text-warning'],
                ['key' => 'student-attendance', 'label' => 'Student Attendance', 'icon' => 'fas fa-calendar-check', 'color' => 'text-warning',
                 'route' => 'student-attendance.index', 'active' => 'student-attendance*', 'permission' => ['academics.view', 'academics.attendance.manage']],
                ['key' => 'disciplinary', 'label' => 'Disciplinary Records', 'icon' => 'fas fa-gavel', 'color' => 'text-warning',
                 'route' => 'disciplinary-records.index', 'active' => 'disciplinary-records*', 'permission' => ['discipline.view', 'discipline.manage']],
                ['key' => 'medical', 'label' => 'Medical Records', 'icon' => 'fas fa-notes-medical', 'color' => 'text-warning',
                 'route' => 'medical-incidents.index', 'active' => 'medical-incidents*', 'permission' => ['students.view', 'students.manage']],

                ['header' => 'Reports', 'color' => 'text-warning'],
                ['key' => 'student-reports', 'label' => 'Student Reports', 'icon' => 'fas fa-file-invoice', 'color' => 'text-warning',
                 'route' => 'student-reports.index', 'active' => 'student-reports*', 'permission' => ['students.view']],
            ],
        ],

        // Examinations
        [
            'key'      => 'exams',
            'label'    => 'Examinations',
            'icon'     => 'fas fa-file-invoice',
            'color'    => 'text-danger',
            'active'   => ['exam*', 'grading-scales*', 'exam-rooms*',
                           'grade-book*', 'mark-sheets*', 'marks-approval*', 'learning-areas*',
                           'strands*', 'competency-assessment*',
                           'report-card-templates*', 'sub-strands*', 'cbc-assessments*', 'bulk-report-cards*'],
            'permission' => ['exams.marks.enter-own', 'exams.schedule.view', 'exams.results.view-own',
                             'exams.publish', 'exams.grading.manage', 'exams.results.view-all',
                             'exams.analysis.view', 'exams.report-cards.export', 'exams.approve', 'exams.import',
                             'academics.settings.manage'],
            'children' => [
                ['key' => 'exam-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line', 'color' => 'text-danger',
                 'route' => 'exam-dashboard.index', 'active' => 'exam-dashboard', 'permission' => ['exams.schedule.view', 'exams.results.view-own', 'exams.results.view-all'], 'roles' => ['Owner', 'Super Admin', 'Admin']],

                ['header' => 'Configuration', 'color' => 'text-danger'],
                ['key' => 'exam-types', 'label' => 'Categories', 'icon' => 'fas fa-tags', 'color' => 'text-danger',
                 'route' => 'exam-types.index', 'active' => 'exam-types*', 'permission' => ['academics.settings.manage']],
                ['key' => 'grading-scales', 'label' => 'Grading Systems', 'icon' => 'fas fa-star-half-alt', 'color' => 'text-danger',
                 'route' => 'gradingScales.index', 'active' => 'grading-scales*', 'permission' => ['exams.grading.manage', 'academics.settings.manage']],
                // Assessment Types removed — not in use

                ['header' => 'Management', 'color' => 'text-danger'],
                ['key' => 'exams', 'label' => 'Sessions', 'icon' => 'fas fa-calendar-alt', 'color' => 'text-danger',
                 'route' => 'exams.index', 'active' => 'exams*', 'permission' => ['exams.schedule.view', 'academics.settings.manage'], 'roles' => ['Owner', 'Super Admin', 'Admin']],
                ['key' => 'exam-schedules', 'label' => 'Timetables', 'icon' => 'fas fa-clock', 'color' => 'text-danger',
                 'route' => 'exam-schedules.index', 'active' => 'exam-schedules*', 'permission' => ['exams.schedule.view', 'academics.settings.manage'], 'roles' => ['Owner', 'Super Admin', 'Admin']],
                ['key' => 'exam-rooms', 'label' => 'Rooms', 'icon' => 'fas fa-door-open', 'color' => 'text-danger',
                 'route' => 'exam-rooms.index', 'active' => 'exam-rooms*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Marks', 'color' => 'text-danger'],
                ['key' => 'exam-results', 'label' => 'Enter Marks', 'icon' => 'fas fa-edit', 'color' => 'text-danger',
                 'route' => 'exam-results.index', 'active' => 'exam-results*', 'permission' => ['exams.marks.enter-own']],
                ['key' => 'exam-results-bulk', 'label' => 'Bulk Import Marks', 'icon' => 'fas fa-file-import', 'color' => 'text-danger',
                 'route' => 'exam-results.bulk', 'active' => 'exam-results/bulk*', 'permission' => ['exams.import']],
                ['key' => 'grade-book', 'label' => 'Grade Book', 'icon' => 'fas fa-book', 'color' => 'text-danger',
                 'route' => 'grade-book.index', 'active' => 'grade-book*', 'permission' => ['exams.results.view-own']],
                ['key' => 'marks-approval', 'label' => 'Approval', 'icon' => 'fas fa-check-circle', 'color' => 'text-danger',
                 'route' => 'marks-approval.index', 'active' => 'marks-approval*', 'permission' => ['exams.approve']],

                ['header' => 'Reports', 'color' => 'text-danger'],
                ['key' => 'exam-reports', 'label' => 'Report Cards', 'icon' => 'fas fa-file-pdf', 'color' => 'text-danger',
                 'route' => 'exam-reports.generate', 'active' => 'exam-reports/generate*', 'permission' => ['exams.report-cards.export', 'exams.results.view-own']],
                ['key' => 'exam-analysis', 'label' => 'Analysis', 'icon' => 'fas fa-chart-bar', 'color' => 'text-danger',
                 'route' => 'exam-analysis.performance', 'active' => 'exam-analysis/performance*', 'permission' => ['exams.analysis.view', 'exams.results.view-all']],

                ['header' => 'CBE Curriculum', 'color' => 'text-danger'],
                ['key' => 'learning-areas', 'label' => 'Learning Areas', 'icon' => 'fas fa-brain', 'color' => 'text-danger',
                 'route' => 'learning-areas.index', 'active' => 'learning-areas*', 'permission' => ['academics.settings.manage', 'academics.view']],
                ['key' => 'strands', 'label' => 'Strands', 'icon' => 'fas fa-layer-group', 'color' => 'text-danger',
                 'route' => 'strands.index', 'active' => 'strands*', 'permission' => ['academics.settings.manage', 'academics.view']],
                ['key' => 'sub-strands', 'label' => 'Sub-Strands', 'icon' => 'fas fa-stream', 'color' => 'text-danger',
                 'route' => 'sub-strands.index', 'active' => 'sub-strands*', 'permission' => ['academics.settings.manage', 'academics.view']],
                ['key' => 'cbc-assessments', 'label' => 'Competency Assessment', 'icon' => 'fas fa-clipboard-check', 'color' => 'text-danger',
                 'route' => 'cbc-assessments.index', 'active' => ['cbc-assessments*', 'competency-assessment*'], 'permission' => ['exams.marks.enter-own', 'academics.view']],
            ],
        ],

        // ─── OPERATIONS ───────────────────────────────────────────
        [
            'header' => 'OPERATIONS',
        ],

        // Inventory Management
        [
            'key'      => 'inventory',
            'label'    => 'Inventory Management',
            'icon'     => 'fas fa-boxes',
            'color'    => 'text-success',
            'active'   => ['inventory*', 'inventory-categories*', 'inventory-items*', 'suppliers*'],
            'permission' => ['inventory.view', 'inventory.manage', 'inventory.approve'],
            'children' => [
                ['key' => 'inventory-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line', 'color' => 'text-success',
                 'route' => 'inventory.dashboard', 'active' => 'inventory', 'permission' => ['inventory.view']],
                ['key' => 'inventory-items', 'label' => 'Items Catalog', 'icon' => 'fas fa-list', 'color' => 'text-success',
                 'route' => 'inventory-items.index', 'active' => 'inventory-items*', 'permission' => ['inventory.view', 'inventory.manage']],
                ['key' => 'inventory-categories', 'label' => 'Categories', 'icon' => 'fas fa-tags', 'color' => 'text-success',
                 'route' => 'inventory-categories.index', 'active' => 'inventory-categories*', 'permission' => ['inventory.view', 'inventory.manage']],
                ['key' => 'suppliers', 'label' => 'Suppliers', 'icon' => 'fas fa-truck', 'color' => 'text-success',
                 'route' => 'suppliers.index', 'active' => 'suppliers*', 'permission' => ['inventory.view', 'inventory.manage']],

                ['header' => 'Operations'],
                ['key' => 'requisitions', 'label' => 'My Requisitions', 'icon' => 'fas fa-file-signature', 'color' => 'text-info',
                 'route' => 'inventory.requisitions.index', 'active' => 'inventory/requisitions*', 'permission' => ['inventory.view', 'inventory.manage']],
                ['key' => 'purchase-orders', 'label' => 'Purchase Orders', 'icon' => 'fas fa-shopping-cart', 'color' => 'text-warning',
                 'route' => 'inventory.purchase-orders.index', 'active' => 'inventory/purchase-orders*', 'permission' => ['inventory.view', 'inventory.manage']],
                ['key' => 'stock-history', 'label' => 'Stock History', 'icon' => 'fas fa-history', 'color' => 'text-secondary',
                 'route' => 'inventory.stock-movement-history', 'active' => 'inventory/stock-movement-history', 'permission' => ['inventory.view']],
            ],
        ],

        // Library Management
        [
            'key'      => 'library',
            'label'    => 'Library Management',
            'icon'     => 'fas fa-book',
            'color'    => 'text-primary',
            'active'   => ['library*', 'book-categories*', 'books*', 'library-members*', 'book-issues*'],
            'permission' => ['library.view', 'library.manage'],
            'children' => [
                ['key' => 'library-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'text-primary',
                 'route' => 'library.dashboard', 'active' => 'library/dashboard', 'permission' => ['library.view']],
                ['key' => 'books', 'label' => 'All Books', 'icon' => 'fas fa-swatchbook', 'color' => 'text-primary',
                 'route' => 'books.index', 'active' => 'books*', 'permission' => ['library.view', 'library.manage']],
                ['key' => 'book-categories', 'label' => 'Categories', 'icon' => 'fas fa-tags', 'color' => 'text-primary',
                 'route' => 'bookCategories.index', 'active' => 'book-categories*', 'permission' => ['library.view', 'library.manage']],
                ['key' => 'book-issues', 'label' => 'Circulation', 'icon' => 'fas fa-sync-alt', 'color' => 'text-primary',
                 'route' => 'book-issues.index', 'active' => 'book-issues*', 'permission' => ['library.view', 'library.manage']],
                ['key' => 'library-members', 'label' => 'Library Members', 'icon' => 'fas fa-users-cog', 'color' => 'text-primary',
                 'route' => 'library-members.index', 'active' => 'library-members*', 'permission' => ['library.view', 'library.manage']],
            ],
        ],

        // Fee Management
        [
            'key'      => 'fees',
            'label'    => 'Fee Management',
            'icon'     => 'fas fa-coins',
            'color'    => 'text-success',
            'active'   => ['fees*', 'fee-*', 'student-fee-*'],
            'permission' => ['fees.view', 'fees.manage', 'fees.collect', 'fees.approve'],
            'children' => [
                ['key' => 'fees-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-pie', 'color' => 'text-success',
                 'route' => 'fees.dashboard', 'active' => 'fees/dashboard', 'permission' => ['fees.view']],

                ['header' => 'Setup & Structure', 'color' => 'text-success'],
                ['key' => 'fee-categories', 'label' => 'Fee Categories', 'icon' => 'fas fa-tags', 'color' => 'text-success',
                 'route' => 'feeCategories.index', 'active' => 'fee-categories*', 'permission' => ['fees.view', 'fees.manage']],
                ['key' => 'fee-structures', 'label' => 'Fee Structures', 'icon' => 'fas fa-list-alt', 'color' => 'text-success',
                 'route' => 'fee-structures.index', 'active' => 'fee-structures*', 'permission' => ['fees.view', 'fees.manage']],
                ['key' => 'discount-schemes', 'label' => 'Discount Schemes', 'icon' => 'fas fa-percent', 'color' => 'text-success',
                 'route' => 'fees.discounts.index', 'active' => 'fees/discounts*', 'permission' => ['fees.view', 'fees.manage']],
                ['key' => 'terms', 'label' => 'Terms', 'icon' => 'fas fa-calendar-alt', 'color' => 'text-success',
                 'route' => 'fees.terms.index', 'active' => 'fees/terms*', 'permission' => ['academics.settings.manage']],

                ['header' => 'Operations', 'color' => 'text-success'],
                ['key' => 'fee-assignments', 'label' => 'Fee Assignments', 'icon' => 'fas fa-file-invoice-dollar', 'color' => 'text-success',
                 'route' => 'fees.assignments.index', 'active' => 'fees/assignments*', 'permission' => ['fees.view', 'fees.manage']],
                ['key' => 'fee-collection', 'label' => 'Collect Fees', 'icon' => 'fas fa-cash-register', 'color' => 'text-success',
                 'route' => 'fees.collect', 'active' => ['fees/collect*', 'fee-management*'], 'permission' => ['fees.view', 'fees.collect']],
                ['key' => 'fee-adjustments', 'label' => 'Adjustments', 'icon' => 'fas fa-sliders-h', 'color' => 'text-success',
                 'route' => 'fees.adjustments.index', 'active' => 'fees/adjustments*', 'permission' => ['fees.view', 'fees.approve']],
                ['key' => 'fee-arrears', 'label' => 'Arrears', 'icon' => 'fas fa-exclamation-triangle', 'color' => 'text-danger',
                 'route' => 'fees.arrears.index', 'active' => 'fees/arrears*', 'permission' => ['fees.view']],
                ['key' => 'fee-refunds', 'label' => 'Refunds', 'icon' => 'fas fa-hand-holding-usd', 'color' => 'text-success',
                 'route' => 'fees.refunds.index', 'active' => 'fees/refunds*', 'permission' => ['fees.view', 'fees.collect', 'fees.approve']],

                ['header' => 'Reports', 'color' => 'text-success'],
                ['key' => 'fee-revenue', 'label' => 'Expected Revenue', 'icon' => 'fas fa-chart-line', 'color' => 'text-success',
                 'route' => 'fees.reports.expected-revenue', 'active' => 'fees/reports/expected-revenue', 'permission' => ['fees.view']],
                ['key' => 'fee-assignment-status', 'label' => 'Assignment Status', 'icon' => 'fas fa-tasks', 'color' => 'text-success',
                 'route' => 'fees.reports.assignment-status', 'active' => 'fees/reports/assignment-status*', 'permission' => ['fees.view']],
                ['key' => 'fee-collections-report', 'label' => 'Collections', 'icon' => 'fas fa-money-check-alt', 'color' => 'text-success',
                 'route' => 'fees.reports.collections', 'active' => 'fees/reports/collections*', 'permission' => ['fees.view']],
                ['key' => 'fee-payment-method-report', 'label' => 'Payment Methods', 'icon' => 'fas fa-credit-card', 'color' => 'text-success',
                 'route' => 'fees.reports.payment-method', 'active' => 'fees/reports/payment-method*', 'permission' => ['fees.view']],
                ['key' => 'fee-receipt-register', 'label' => 'Receipt Register', 'icon' => 'fas fa-receipt', 'color' => 'text-success',
                 'route' => 'fees.reports.receipt-register', 'active' => 'fees/reports/receipt-register*', 'permission' => ['fees.view']],
                ['key' => 'fee-discount-summary', 'label' => 'Discount Summary', 'icon' => 'fas fa-percent', 'color' => 'text-success',
                 'route' => 'fees.reports.discount-summary', 'active' => 'fees/reports/discount-summary*', 'permission' => ['fees.view']],
            ],
        ],

        // ─── GOVERNANCE ───────────────────────────────────────────
        [
            'header' => 'GOVERNANCE',
        ],

        // Human Resources
        [
            'key'      => 'hr',
            'label'    => 'Human Resources',
            'icon'     => 'fas fa-user-tie',
            'color'    => 'text-secondary',
            'active'   => ['hr*', 'departments*', 'job-positions*', 'leave-*', 'staff-*', 'payroll-*'],
            'permission' => ['hr.view', 'hr.manage', 'hr.approve'],
            'children' => [
                ['key' => 'hr-dashboard', 'label' => 'HR Dashboard', 'icon' => 'fas fa-chart-line', 'color' => 'text-secondary',
                 'route' => 'hr.dashboard', 'active' => 'hr/dashboard', 'permission' => ['hr.view']],

                ['header' => 'Staff Management', 'color' => 'text-secondary'],
                ['key' => 'staff', 'label' => 'All Staff', 'icon' => 'fas fa-users', 'color' => 'text-secondary',
                 'route' => 'staff.index', 'active' => ['staff', 'staff/show*'], 'permission' => ['hr.view', 'hr.manage']],
                ['key' => 'staff-onboarding', 'label' => 'Onboarding', 'icon' => 'fas fa-user-plus', 'color' => 'text-secondary',
                 'route' => 'hr.onboarding', 'active' => 'hr/onboarding*', 'permission' => ['hr.view', 'hr.manage']],

                ['header' => 'Organization', 'color' => 'text-secondary'],
                ['key' => 'departments', 'label' => 'Departments', 'icon' => 'far fa-building', 'color' => 'text-secondary',
                 'route' => 'departments.index', 'active' => 'departments*', 'permission' => ['hr.view', 'hr.manage']],
                ['key' => 'job-positions', 'label' => 'Job Positions', 'icon' => 'far fa-briefcase', 'color' => 'text-secondary',
                 'route' => 'job-positions.index', 'active' => 'job-positions*', 'permission' => ['hr.view', 'hr.manage']],

                ['header' => 'Time Off & Attendance', 'color' => 'text-secondary'],
                ['key' => 'leave-applications', 'label' => 'Leave Applications', 'icon' => 'fas fa-calendar-alt', 'color' => 'text-secondary',
                 'route' => 'leave-applications.index', 'active' => 'leave-applications*', 'permission' => ['hr.view', 'hr.manage', 'hr.approve']],
                ['key' => 'leave-types', 'label' => 'Leave Types', 'icon' => 'far fa-calendar-times', 'color' => 'text-secondary',
                 'route' => 'leaveTypes.index', 'active' => 'leave-types*', 'permission' => ['hr.view', 'hr.manage']],
                ['key' => 'staff-attendance', 'label' => 'Attendance', 'icon' => 'fas fa-calendar-check', 'color' => 'text-secondary',
                 'route' => 'staff-attendance.index', 'active' => 'staff-attendance*', 'permission' => ['hr.view', 'hr.manage']],

                ['header' => 'Ops & Finance', 'color' => 'text-secondary'],
                ['key' => 'staff-documents', 'label' => 'Documents', 'icon' => 'far fa-folder', 'color' => 'text-secondary',
                 'route' => 'staffDocuments.index', 'active' => 'staff-documents*', 'permission' => ['hr.view', 'hr.manage']],
                ['key' => 'payroll', 'label' => 'Payroll', 'icon' => 'fas fa-money-check-alt', 'color' => 'text-secondary',
                 'route' => 'payroll-processing.index', 'active' => 'payroll-processing*', 'permission' => ['hr.view', 'hr.manage']],
                ['key' => 'staff-exit', 'label' => 'Exit Management', 'icon' => 'fas fa-sign-out-alt', 'color' => 'text-secondary',
                 'route' => 'hr.exit', 'active' => 'hr/exit*', 'permission' => ['hr.view', 'hr.manage']],

                ['header' => 'Reports', 'color' => 'text-secondary'],
                ['key' => 'hr-headcount', 'label' => 'Headcount', 'icon' => 'fas fa-users', 'color' => 'text-secondary',
                 'route' => 'hr.reports.headcount', 'active' => 'hr/reports/headcount*', 'permission' => ['hr.view']],
                ['key' => 'hr-leave-analytics', 'label' => 'Leave Analytics', 'icon' => 'fas fa-chart-bar', 'color' => 'text-secondary',
                 'route' => 'hr.reports.leave', 'active' => 'hr/reports/leave*', 'permission' => ['hr.view']],
                ['key' => 'hr-payroll-analytics', 'label' => 'Payroll Analytics', 'icon' => 'fas fa-chart-pie', 'color' => 'text-secondary',
                 'route' => 'hr.reports.payroll', 'active' => 'hr/reports/payroll*', 'permission' => ['hr.view']],
            ],
        ],

        // Financial Management
        [
            'key'      => 'finance',
            'label'    => 'Financial Management',
            'icon'     => 'fas fa-chart-line',
            'color'    => 'text-dark',
            'active'   => ['finance*', 'expenses*', 'income*', 'bank*', 'budgets*',
                           'financial-reports*', 'financial-years*', 'budget-vs-actual*'],
            'permission' => ['finance.view', 'finance.manage', 'finance.approve', 'finance.import', 'finance.export'],
            'children' => [
                ['key' => 'finance-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-tachometer-alt', 'color' => 'text-dark',
                 'route' => 'finance.dashboard', 'active' => 'finance/dashboard', 'permission' => ['finance.view']],

                ['header' => 'Income', 'color' => 'text-success'],
                ['key' => 'income-categories', 'label' => 'Income Categories', 'icon' => 'fas fa-plus-circle', 'color' => 'text-success',
                 'route' => 'incomeCategories.index', 'active' => 'income-categories*', 'permission' => ['finance.view', 'finance.manage']],
                ['key' => 'income-create', 'label' => 'Record Income', 'icon' => 'fas fa-arrow-down', 'color' => 'text-success',
                 'route' => 'income.create', 'active' => 'income/create', 'permission' => ['finance.manage']],
                ['key' => 'income-transactions', 'label' => 'Income Transactions', 'icon' => 'fas fa-list', 'color' => 'text-success',
                 'route' => 'income.index', 'active' => 'income', 'permission' => ['finance.view']],

                ['header' => 'Expenses', 'color' => 'text-danger'],
                ['key' => 'expense-categories', 'label' => 'Expense Categories', 'icon' => 'fas fa-minus-circle', 'color' => 'text-danger',
                 'route' => 'expenseCategories.index', 'active' => 'expense-categories*', 'permission' => ['finance.view', 'finance.manage']],
                ['key' => 'expense-create', 'label' => 'Record Expense', 'icon' => 'fas fa-arrow-up', 'color' => 'text-danger',
                 'route' => 'expenses.create', 'active' => 'expenses/create', 'permission' => ['finance.manage']],
                ['key' => 'expense-transactions', 'label' => 'Expense Transactions', 'icon' => 'fas fa-receipt', 'color' => 'text-danger',
                 'route' => 'expenses.index', 'active' => 'expenses', 'permission' => ['finance.view']],
                ['key' => 'expense-pending', 'label' => 'Pending Approvals', 'icon' => 'fas fa-clock', 'color' => 'text-warning',
                 'route' => 'expenses.pending', 'active' => 'expenses/pending', 'permission' => ['finance.view', 'finance.approve']],

                ['header' => 'Banking', 'color' => 'text-primary'],
                ['key' => 'bank-accounts', 'label' => 'Bank Accounts', 'icon' => 'fas fa-university', 'color' => 'text-primary',
                 'route' => 'bankAccounts.index', 'active' => 'bank-accounts*', 'permission' => ['finance.view', 'finance.manage']],
                ['key' => 'bank-transactions', 'label' => 'Bank Transactions', 'icon' => 'fas fa-exchange-alt', 'color' => 'text-primary',
                 'route' => 'bank-transactions.index', 'active' => 'bank-transactions*', 'permission' => ['finance.view', 'finance.manage']],
                ['key' => 'bank-reconciliation', 'label' => 'Bank Reconciliation', 'icon' => 'fas fa-balance-scale', 'color' => 'text-primary',
                 'route' => 'bank-reconciliations.index', 'active' => 'bank-reconciliations*', 'permission' => ['finance.view']],

                ['header' => 'Budgeting', 'color' => 'text-info'],
                ['key' => 'budgets', 'label' => 'Budget Planning', 'icon' => 'fas fa-clipboard-list', 'color' => 'text-info',
                 'route' => 'budgets.index', 'active' => 'budgets*', 'permission' => ['finance.view', 'finance.manage']],
                ['key' => 'budget-vs-actual', 'label' => 'Budget vs Actual', 'icon' => 'fas fa-chart-bar', 'color' => 'text-info',
                 'route' => 'budgets.vs-actual', 'active' => 'budgets/vs-actual', 'permission' => ['finance.view']],

                ['header' => 'Reports & Analysis', 'color' => 'text-dark'],
                ['key' => 'financial-reports', 'label' => 'Financial Reports', 'icon' => 'fas fa-file-alt', 'color' => 'text-dark',
                 'route' => 'financial-reports.index', 'active' => 'financial-reports*', 'permission' => ['finance.view', 'finance.export']],
                ['key' => 'cashflow', 'label' => 'Cashflow Analysis', 'icon' => 'fas fa-chart-line', 'color' => 'text-dark',
                 'route' => 'financial-reports.cashflow', 'active' => 'financial-reports/cashflow', 'permission' => ['finance.view', 'finance.export']],
                ['key' => 'profit-loss', 'label' => 'Profit & Loss', 'icon' => 'fas fa-calculator', 'color' => 'text-dark',
                 'route' => 'financial-reports.p-and-l', 'active' => 'financial-reports/p-and-l', 'permission' => ['finance.view']],

                ['header' => 'Auditing & Setup', 'color' => 'text-secondary'],
                ['key' => 'financial-years', 'label' => 'Financial Year', 'icon' => 'fas fa-calendar-alt', 'color' => 'text-secondary',
                 'route' => 'financial-years.index', 'active' => 'financial-years*', 'permission' => ['finance.view', 'finance.manage']],
            ],
        ],

        // Hostel Management
        [
            'key'      => 'hostel',
            'label'    => 'Hostel Management',
            'icon'     => 'fas fa-hotel',
            'color'    => 'text-warning',
            'active'   => ['hostel*'],
            'permission' => ['hostel.view', 'hostel.manage'],
            'children' => [
                ['key' => 'hostel-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-pie', 'color' => 'text-warning',
                 'route' => 'hostel.dashboard', 'active' => 'hostel/dashboard', 'permission' => ['hostel.view']],
                ['key' => 'hostels', 'label' => 'Hostels', 'icon' => 'fas fa-building', 'color' => 'text-warning',
                 'route' => 'hostels.index', 'active' => 'hostels*', 'permission' => ['hostel.view', 'hostel.manage']],
                ['key' => 'hostel-rooms', 'label' => 'Hostel Rooms', 'icon' => 'fas fa-door-open', 'color' => 'text-warning',
                 'route' => 'hostel-rooms.index', 'active' => 'hostel-rooms*', 'permission' => ['hostel.view', 'hostel.manage']],
                ['key' => 'hostel-allocations', 'label' => 'Hostel Allocations', 'icon' => 'fas fa-key', 'color' => 'text-warning',
                 'route' => 'hostel-allocations.index', 'active' => 'hostel-allocations*', 'permission' => ['hostel.view', 'hostel.manage']],
                ['key' => 'hostel-vacancy', 'label' => 'Vacancy Report', 'icon' => 'fas fa-percentage', 'color' => 'text-warning',
                 'route' => 'hostel.vacancy-report', 'active' => 'hostel/vacancy-report', 'permission' => ['hostel.view']],
                ['key' => 'hostel-students', 'label' => 'Student List', 'icon' => 'fas fa-list', 'color' => 'text-warning',
                 'route' => 'hostel.student-list', 'active' => 'hostel/student-list', 'permission' => ['hostel.view']],
            ],
        ],

        // Transportation
        [
            'key'      => 'transport',
            'label'    => 'Transportation',
            'icon'     => 'fas fa-bus-alt',
            'color'    => 'text-danger',
            'active'   => ['routes*', 'route-stops*', 'transportation*', 'vehicles*',
                           'student-transport-assignments*', 'transport-assignments*',
                           'transport-registrations*', 'api/routes*'],
            'permission' => ['transport.view', 'transport.manage'],
            'children' => [
                ['key' => 'transport-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-pie', 'color' => 'text-danger',
                 'route' => 'transportation.dashboard', 'active' => 'transportation/dashboard', 'permission' => ['transport.view']],

                ['header' => 'Fleet & Routes', 'color' => 'text-danger'],
                ['key' => 'routes', 'label' => 'Routes', 'icon' => 'fas fa-route', 'color' => 'text-danger',
                 'route' => 'routes.index', 'active' => 'routes*', 'permission' => ['transport.view', 'transport.manage']],
                ['key' => 'route-stops', 'label' => 'Route Stops', 'icon' => 'fas fa-map-marker-alt', 'color' => 'text-danger',
                 'route' => 'routeStops.index', 'active' => 'route-stops*', 'permission' => ['transport.view', 'transport.manage']],
                ['key' => 'vehicles', 'label' => 'Vehicles', 'icon' => 'fas fa-shuttle-van', 'color' => 'text-danger',
                 'route' => 'vehicles.index', 'active' => 'vehicles*', 'permission' => ['transport.view', 'transport.manage']],

                ['header' => 'Allocations', 'color' => 'text-danger'],
                ['key' => 'student-transport', 'label' => 'Student Assignments', 'icon' => 'fas fa-user-graduate', 'color' => 'text-danger',
                 'route' => 'student-transport-assignments.index', 'active' => 'student-transport-assignments*', 'permission' => ['transport.view', 'transport.manage']],

                ['header' => 'Reports', 'color' => 'text-danger'],
                ['key' => 'transport-route-report', 'label' => 'Route Wise List', 'icon' => 'fas fa-list-ol', 'color' => 'text-danger',
                 'route' => 'transportation.reports.route-wise', 'active' => 'transportation/reports/route-wise', 'permission' => ['transport.view']],
                ['key' => 'transport-occupancy', 'label' => 'Occupancy Report', 'icon' => 'fas fa-chair', 'color' => 'text-danger',
                 'route' => 'transportation.reports.occupancy', 'active' => 'transportation/reports/occupancy', 'permission' => ['transport.view']],
            ],
        ],

        // Communication
        [
            'key'      => 'communication',
            'label'    => 'Communication',
            'icon'     => 'fas fa-comments',
            'color'    => 'text-secondary',
            'active'   => ['sms-templates*', 'email-templates*', 'communication*', 'messages*',
                           'notifications*'],
            'permission' => ['communication.view', 'communication.manage'],
            'children' => [
                ['key' => 'comm-dashboard', 'label' => 'Dashboard', 'icon' => 'fas fa-chart-line', 'color' => 'text-secondary',
                 'route' => 'communication.dashboard', 'active' => 'communication/dashboard', 'permission' => ['communication.view']],
                ['key' => 'comm-compose', 'label' => 'Compose Message', 'icon' => 'fas fa-pen-nib', 'color' => 'text-secondary',
                 'route' => 'communication.compose', 'active' => 'communication/compose', 'permission' => ['communication.manage']],
                ['key' => 'comm-history', 'label' => 'Message History', 'icon' => 'fas fa-history', 'color' => 'text-secondary',
                 'route' => 'communication.history.index', 'active' => 'communication/history*', 'permission' => ['communication.view']],
                ['key' => 'sms-templates', 'label' => 'SMS Templates', 'icon' => 'far fa-sms', 'color' => 'text-secondary',
                 'route' => 'smsTemplates.index', 'active' => 'sms-templates*', 'permission' => ['communication.view', 'communication.manage']],
                ['key' => 'email-templates', 'label' => 'Email Templates', 'icon' => 'far fa-envelope', 'color' => 'text-secondary',
                 'route' => 'emailTemplates.index', 'active' => 'email-templates*', 'permission' => ['communication.view', 'communication.manage']],
                ['key' => 'comm-triggers', 'label' => 'Auto Triggers', 'icon' => 'fas fa-bolt', 'color' => 'text-secondary',
                 'route' => 'communication.triggers.index', 'active' => 'communication/triggers*', 'permission' => ['communication.view']],
                ['key' => 'comm-pending', 'label' => 'Pending Confirmations', 'icon' => 'fas fa-inbox', 'color' => 'text-secondary',
                 'route' => 'communication.pending.index', 'active' => 'communication/pending*', 'permission' => ['communication.send']],
                ['key' => 'comm-providers', 'label' => 'Provider Settings', 'icon' => 'fas fa-cog', 'color' => 'text-secondary',
                 'route' => 'communication.providers.index', 'active' => 'communication/providers*', 'permission' => ['communication.manage'],
                 'owner_only' => true],
            ],
        ],

        // System Administration (platform Owner only — never school admins)
        [
            'key'      => 'administration',
            'label'    => 'Administration',
            'icon'     => 'fas fa-shield-alt',
            'color'    => 'text-dark',
            'active'   => ['audit-trail*', 'modules*', 'system-logs*'],
            'permission' => [],
            'owner_only' => true,
            'children' => [
                ['key' => 'audit-trail', 'label' => 'Audit Trail', 'icon' => 'fas fa-history', 'color' => 'text-secondary',
                 'route' => 'audit-trail.index', 'active' => 'audit-trail*', 'permission' => [], 'owner_only' => true],
                ['key' => 'modules', 'label' => 'Modules', 'icon' => 'fas fa-puzzle-piece', 'color' => 'text-secondary',
                 'route' => 'modules.index', 'active' => 'modules*', 'permission' => [], 'owner_only' => true],
                ['key' => 'system-logs', 'label' => 'System Logs', 'icon' => 'fas fa-bug', 'color' => 'text-secondary',
                 'route' => 'system-logs.index', 'active' => 'system-logs*', 'permission' => [], 'owner_only' => true],
            ],
        ],

    ],

];
