<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt text-primary"></i>
        <p>Dashboard</p>
    </a>
</li>

<!-- User Management -->
<li class="nav-item has-treeview {{ Request::is('roles*') || Request::is('permissions*') || Request::is('user-roles*') || Request::is('role-permissions*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('roles*') || Request::is('permissions*') || Request::is('user-roles*') || Request::is('role-permissions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users-cog text-success"></i>
        <p>
            User Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>Roles</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('permissions.index') }}" class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>Permissions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('user-roles.index') }}" class="nav-link {{ Request::is('user-roles*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>User Roles</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('role-permissions.index') }}" class="nav-link {{ Request::is('role-permissions*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>Role Permissions</p>
            </a>
        </li>
    </ul>
</li>

<!-- Academic Management -->
@canany([
    'academic-years.index',
    'school-classes.index',
    'sections.index',
    'class-sections.index',
    'subjects.index',
    'class-subjects.index',
    'teacher-subjects.index',
    'periods.index',
    'classrooms.index',
    'timetables.index'
])
<li class="nav-item has-treeview {{ Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-graduation-cap text-info"></i>
        <p>
            Academic Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @can('academic-years.index')
        <li class="nav-item">
            <a href="{{ route('academic-years.index') }}" class="nav-link {{ Request::is('academic-years*') ? 'active' : '' }}">
                <i class="far fa-calendar nav-icon text-info"></i>
                <p>Academic Years</p>
            </a>
        </li>
        @endcan

        @can('school-classes.index')
        <li class="nav-item">
            <a href="{{ route('school-classes.index') }}" class="nav-link {{ Request::is('school-classes*') ? 'active' : '' }}">
                <i class="far fa-chalkboard nav-icon text-info"></i>
                <p>School Classes</p>
            </a>
        </li>
        @endcan

        @can('sections.index')
        <li class="nav-item">
            <a href="{{ route('sections.index') }}" class="nav-link {{ Request::is('sections*') ? 'active' : '' }}">
                <i class="far fa-layer-group nav-icon text-info"></i>
                <p>Sections</p>
            </a>
        </li>
        @endcan

        @can('class-sections.index')
        <li class="nav-item">
            <a href="{{ route('class-sections.index') }}" class="nav-link {{ Request::is('class-sections*') ? 'active' : '' }}">
                <i class="far fa-sitemap nav-icon text-info"></i>
                <p>Class Sections</p>
            </a>
        </li>
        @endcan

        @can('subjects.index')
        <li class="nav-item">
            <a href="{{ route('subjects.index') }}" class="nav-link {{ Request::is('subjects*') ? 'active' : '' }}">
                <i class="far fa-book-open nav-icon text-info"></i>
                <p>Subjects</p>
            </a>
        </li>
        @endcan

        @can('class-subjects.index')
        <li class="nav-item">
            <a href="{{ route('class-subjects.index') }}" class="nav-link {{ Request::is('class-subjects*') ? 'active' : '' }}">
                <i class="far fa-link nav-icon text-info"></i>
                <p>Class Subjects</p>
            </a>
        </li>
        @endcan

        @can('teacher-subjects.index')
        <li class="nav-item">
            <a href="{{ route('teacher-subjects.index') }}" class="nav-link {{ Request::is('teacher-subjects*') ? 'active' : '' }}">
                <i class="far fa-user-tie nav-icon text-info"></i>
                <p>Teacher Subjects</p>
            </a>
        </li>
        @endcan

        @can('periods.index')
        <li class="nav-item">
            <a href="{{ route('periods.index') }}" class="nav-link {{ Request::is('periods*') ? 'active' : '' }}">
                <i class="far fa-clock nav-icon text-info"></i>
                <p>Periods</p>
            </a>
        </li>
        @endcan

        @can('classrooms.index')
        <li class="nav-item">
            <a href="{{ route('classrooms.index') }}" class="nav-link {{ Request::is('classrooms*') ? 'active' : '' }}">
                <i class="far fa-door-open nav-icon text-info"></i>
                <p>Classrooms</p>
            </a>
        </li>
        @endcan

        @can('timetables.index')
        <li class="nav-item">
            <a href="{{ route('timetables.index') }}" class="nav-link {{ Request::is('timetables*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-home"></i>
                <p>Timetables</p>
            </a>
        </li>
        @endcan
    </ul>
</li>
@endcanany

<!-- Student Management -->
<li class="nav-item has-treeview {{ Request::is('students*') || Request::is('student-class-enrollments*') || Request::is('student-parent-relationships*') || Request::is('student-documents*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('students*') || Request::is('student-class-enrollments*') || Request::is('student-parent-relationships*') || Request::is('student-documents*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-graduate text-warning"></i>
        <p>
            Student Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('students.index') }}" class="nav-link {{ Request::is('students*') ? 'active' : '' }}">
                <i class="far fa-user nav-icon text-warning"></i>
                <p>Students</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-class-enrollments.index') }}" class="nav-link {{ Request::is('student-class-enrollments*') ? 'active' : '' }}">
                <i class="far fa-user-plus nav-icon text-warning"></i>
                <p>Class Enrollments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-parent-relationships.index') }}" class="nav-link {{ Request::is('student-parent-relationships*') ? 'active' : '' }}">
                <i class="far fa-users nav-icon text-warning"></i>
                <p>Parent Relationships</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-documents.index') }}" class="nav-link {{ Request::is('student-documents*') ? 'active' : '' }}">
                <i class="far fa-file-alt nav-icon text-warning"></i>
                <p>Student Documents</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('parents.index') }}" class="nav-link {{ Request::is('parents*') ? 'active' : '' }}">
                <i class="far fa-user-friends nav-icon text-warning"></i>
                <p>Parents</p>
            </a>
        </li>
    </ul>
</li>

<!-- Examination Management -->
<li class="nav-item has-treeview {{ Request::is('exam*') || Request::is('grading-scales*') || Request::is('assessment-types*') || Request::is('exam-rooms*') || Request::is('grade-book*') || Request::is('mark-sheets*') || Request::is('marks-approval*') || Request::is('report-card-templates*') || Request::is('learning-areas*') || Request::is('strands*') || Request::is('competency-assessment*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('exam*') || Request::is('grading-scales*') || Request::is('assessment-types*') || Request::is('exam-rooms*') || Request::is('grade-book*') || Request::is('mark-sheets*') || Request::is('marks-approval*') || Request::is('report-card-templates*') || Request::is('learning-areas*') || Request::is('strands*') || Request::is('competency-assessment*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-invoice text-danger"></i>
        <p>
            Examinations
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('exam-dashboard.index') }}" class="nav-link {{ Request::is('exam-dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line nav-icon text-danger"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Configuration</li>
        <li class="nav-item">
            <a href="{{ route('exam-types.index') }}" class="nav-link {{ Request::is('exam-types*') ? 'active' : '' }}">
                <i class="fas fa-tags nav-icon text-danger"></i>
                <p>Exam Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grading-scales.index') }}" class="nav-link {{ Request::is('grading-scales*') ? 'active' : '' }}">
                <i class="fas fa-star-half-alt nav-icon text-danger"></i>
                <p>Grading Systems</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('assessment-types.index') }}" class="nav-link {{ Request::is('assessment-types*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-check nav-icon text-danger"></i>
                <p>Assessment Types</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Exam Management</li>
        <li class="nav-item">
            <a href="{{ route('exams.index') }}" class="nav-link {{ Request::is('exams*') && !Request::is('exams-dashboard*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt nav-icon text-danger"></i>
                <p>Exam Sessions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-schedules.index') }}" class="nav-link {{ Request::is('exam-schedules*') ? 'active' : '' }}">
                <i class="fas fa-clock nav-icon text-danger"></i>
                <p>Exam Timetables</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-rooms.index') }}" class="nav-link {{ Request::is('exam-rooms*') ? 'active' : '' }}">
                <i class="fas fa-door-open nav-icon text-danger"></i>
                <p>Exam Rooms</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Marks & Results</li>
        <li class="nav-item">
            <a href="{{ route('exam-results.index') }}" class="nav-link {{ Request::is('exam-results*') ? 'active' : '' }}">
                <i class="fas fa-edit nav-icon text-danger"></i>
                <p>Enter Marks</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grade-book.index') }}" class="nav-link {{ Request::is('grade-book*') ? 'active' : '' }}">
                <i class="fas fa-book nav-icon text-danger"></i>
                <p>Grade Book</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('mark-sheets.index') }}" class="nav-link {{ Request::is('mark-sheets*') ? 'active' : '' }}">
                <i class="fas fa-table nav-icon text-danger"></i>
                <p>Mark Sheets</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('marks-approval.index') }}" class="nav-link {{ Request::is('marks-approval*') ? 'active' : '' }}">
                <i class="fas fa-check-circle nav-icon text-danger"></i>
                <p>Marks Approval</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Report Cards</li>
        <li class="nav-item">
            <a href="{{ route('exam-reports.generate') }}" class="nav-link {{ Request::is('exam-reports/generate*') ? 'active' : '' }}">
                <i class="fas fa-file-pdf nav-icon text-danger"></i>
                <p>Generate Reports</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('report-card-templates.index') }}" class="nav-link {{ Request::is('report-card-templates*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice nav-icon text-danger"></i>
                <p>Templates</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-reports.bulk') }}" class="nav-link {{ Request::is('bulk-report-cards*') ? 'active' : '' }}">
                <i class="fas fa-print nav-icon text-danger"></i>
                <p>Bulk Report Cards</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Analysis & Reports</li>
        <li class="nav-item">
            <a href="{{ route('exam-analysis.performance') }}" class="nav-link {{ Request::is('exam-analysis/performance*') ? 'active' : '' }}">
                <i class="fas fa-chart-bar nav-icon text-danger"></i>
                <p>Performance Analysis</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-analysis.subject') }}" class="nav-link {{ Request::is('exam-analysis/subject*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie nav-icon text-danger"></i>
                <p>Subject Analysis</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-analysis.rankings') }}" class="nav-link {{ Request::is('exam-analysis/rankings*') ? 'active' : '' }}">
                <i class="fas fa-trophy nav-icon text-danger"></i>
                <p>Class Rankings</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">CBC Specific</li>
        <li class="nav-item">
            <a href="{{ route('learning-areas.index') }}" class="nav-link {{ Request::is('learning-areas*') ? 'active' : '' }}">
                <i class="fas fa-brain nav-icon text-danger"></i>
                <p>Learning Areas</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('strands.index') }}" class="nav-link {{ Request::is('strands*') ? 'active' : '' }}">
                <i class="fas fa-stream nav-icon text-danger"></i>
                <p>CBC Strands</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sub-strands.index') }}" class="nav-link {{ Request::is('sub-strands*') ? 'active' : '' }}">
                <i class="fas fa-project-diagram nav-icon text-danger"></i>
                <p>CBC Sub-Strands</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('competency-assessment.index') }}" class="nav-link {{ Request::is('competency-assessment*') ? 'active' : '' }}">
                <i class="fas fa-tasks nav-icon text-danger"></i>
                <p>Competency Assessment</p>
            </a>
        </li>
    </ul>
</li>

<!-- Human Resources -->
<li class="nav-item has-treeview {{ Request::is('departments*') || Request::is('job-positions*') || Request::is('leave-types*') || Request::is('staff-documents*') || Request::is('payrolls*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('departments*') || Request::is('job-positions*') || Request::is('leave-types*') || Request::is('staff-documents*') || Request::is('payrolls*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-tie text-secondary"></i>
        <p>
            Human Resources
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('departments.index') }}" class="nav-link {{ Request::is('departments*') ? 'active' : '' }}">
                <i class="far fa-building nav-icon text-secondary"></i>
                <p>Departments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('job-positions.index') }}" class="nav-link {{ Request::is('job-positions*') ? 'active' : '' }}">
                <i class="far fa-briefcase nav-icon text-secondary"></i>
                <p>Job Positions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('leave-types.index') }}" class="nav-link {{ Request::is('leave-types*') ? 'active' : '' }}">
                <i class="far fa-calendar-times nav-icon text-secondary"></i>
                <p>Leave Types</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('staff.index') }}" class="nav-link {{ Request::is('staff*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-work"></i>
                <p>Staff</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('staff-documents.index') }}" class="nav-link {{ Request::is('staff-documents*') ? 'active' : '' }}">
                <i class="far fa-folder nav-icon text-secondary"></i>
                <p>Staff Documents</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('payrolls.index') }}" class="nav-link {{ Request::is('payrolls*') ? 'active' : '' }}">
                <i class="far fa-money-check-alt nav-icon text-secondary"></i>
                <p>Payrolls</p>
            </a>
        </li>
    </ul>
</li>

<!-- Financial Management -->
<li class="nav-item has-treeview {{ Request::is('finance*') || Request::is('expenses*') || Request::is('income*') || Request::is('bank*') || Request::is('student-fee-discounts*') || Request::is('budgets*') || Request::is('financial-reports*') || Request::is('financial-years*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('finance*') || Request::is('expenses*') || Request::is('income*') || Request::is('bank*') || Request::is('student-fee-discounts*') || Request::is('budgets*') || Request::is('financial-reports*') || Request::is('financial-years*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line text-dark"></i>
        <p>
            Financial Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('finance.dashboard') }}" class="nav-link {{ Request::is('finance/dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt nav-icon text-dark"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-success">Income</li>
        <li class="nav-item">
            <a href="{{ route('incomeCategories.index') }}" class="nav-link {{ Request::is('income-categories*') ? 'active' : '' }}">
                <i class="fas fa-plus-circle nav-icon text-success"></i>
                <p>Income Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('income.create') }}" class="nav-link {{ Request::is('income/create') ? 'active' : '' }}">
                <i class="fas fa-arrow-down nav-icon text-success"></i>
                <p>Record Income</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('income.index') }}" class="nav-link {{ Request::is('income') ? 'active' : '' }}">
                <i class="fas fa-list nav-icon text-success"></i>
                <p>Income Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('fee-management.index') }}" class="nav-link {{ Request::is('fee-management*') ? 'active' : '' }}">
                <i class="fas fa-cash-register nav-icon text-success"></i>
                <p>Fee Collection</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Expenses</li>
        <li class="nav-item">
            <a href="{{ route('expenseCategories.index') }}" class="nav-link {{ Request::is('expense-categories*') ? 'active' : '' }}">
                <i class="fas fa-minus-circle nav-icon text-danger"></i>
                <p>Expense Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.create') }}" class="nav-link {{ Request::is('expenses/create') ? 'active' : '' }}">
                <i class="fas fa-arrow-up nav-icon text-danger"></i>
                <p>Record Expense</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.index') }}" class="nav-link {{ Request::is('expenses') ? 'active' : '' }}">
                <i class="fas fa-receipt nav-icon text-danger"></i>
                <p>Expense Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.pending') }}" class="nav-link {{ Request::is('expenses/pending') ? 'active' : '' }}">
                <i class="fas fa-clock nav-icon text-warning"></i>
                <p>Pending Approvals</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-primary">Banking</li>
        <li class="nav-item">
            <a href="{{ route('bank-accounts.index') }}" class="nav-link {{ Request::is('bank-accounts*') ? 'active' : '' }}">
                <i class="fas fa-university nav-icon text-primary"></i>
                <p>Bank Accounts</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bank-transactions.index') }}" class="nav-link {{ Request::is('bank-transactions*') ? 'active' : '' }}">
                <i class="fas fa-exchange-alt nav-icon text-primary"></i>
                <p>Bank Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bank-reconciliations.index') }}" class="nav-link {{ Request::is('bank-reconciliations*') ? 'active' : '' }}">
                <i class="fas fa-balance-scale nav-icon text-primary"></i>
                <p>Bank Reconciliation</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-info">Budgeting</li>
        <li class="nav-item">
            <a href="{{ route('budgets.index') }}" class="nav-link {{ Request::is('budgets*') ? 'active' : '' }}">
                <i class="fas fa-clipboard-list nav-icon text-info"></i>
                <p>Budget Planning</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('budgets.vs-actual') }}" class="nav-link {{ Request::is('budgets/vs-actual') ? 'active' : '' }}">
                <i class="fas fa-chart-bar nav-icon text-info"></i>
                <p>Budget vs Actual</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-dark">Reports & Analysis</li>
        <li class="nav-item">
            <a href="{{ route('financial-reports.index') }}" class="nav-link {{ Request::is('financial-reports*') ? 'active' : '' }}">
                <i class="fas fa-file-alt nav-icon text-dark"></i>
                <p>Financial Reports</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-reports.cashflow') }}" class="nav-link {{ Request::is('financial-reports/cashflow') ? 'active' : '' }}">
                <i class="fas fa-chart-line nav-icon text-dark"></i>
                <p>Cashflow Analysis</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-reports.p-and-l') }}" class="nav-link {{ Request::is('financial-reports/p-and-l') ? 'active' : '' }}">
                <i class="fas fa-calculator nav-icon text-dark"></i>
                <p>Profit & Loss</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-secondary">Auditing & Setup</li>
        <li class="nav-item">
            <a href="{{ route('audit-trail.index') }}" class="nav-link {{ Request::is('audit-trail*') ? 'active' : '' }}">
                <i class="fas fa-history nav-icon text-secondary"></i>
                <p>Audit Trail</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-years.index') }}" class="nav-link {{ Request::is('financial-years*') ? 'active' : '' }}">
                <i class="fas fa-calendar-alt nav-icon text-secondary"></i>
                <p>Financial Year</p>
            </a>
        </li>
    </ul>
</li>

<!-- Library Management -->
<li class="nav-item has-treeview {{ Request::is('library*') || Request::is('book-categories*') || Request::is('books*') || Request::is('library-members*') || Request::is('book-issues*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('library*') || Request::is('book-categories*') || Request::is('books*') || Request::is('library-members*') || Request::is('book-issues*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-book text-primary"></i>
        <p>
            Library Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('library.dashboard') }}" class="nav-link {{ Request::is('library/dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt nav-icon text-primary"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <!-- Books Management Sub-menu -->
        <li class="nav-item has-treeview {{ Request::is('books*') || Request::is('book-categories*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::is('books*') || Request::is('book-categories*') ? 'active' : '' }}">
                <i class="fas fa-swatchbook nav-icon text-primary"></i>
                <p>
                    Books Catalog
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview pl-3">
                <li class="nav-item">
                    <a href="{{ route('books.index') }}" class="nav-link {{ Request::is('books') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Books</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('book-categories.index') }}" class="nav-link {{ Request::is('book-categories*') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Categories</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Circulation Management Sub-menu -->
        <li class="nav-item has-treeview {{ Request::is('book-issues*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::is('book-issues*') ? 'active' : '' }}">
                <i class="fas fa-sync-alt nav-icon text-primary"></i>
                <p>
                    Circulation
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview pl-3">
                <li class="nav-item">
                    <a href="{{ route('book-issues.create') }}" class="nav-link {{ Request::is('book-issues/create') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Issue Book</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('book-issues.index') }}" class="nav-link {{ Request::is('book-issues') ? 'active' : '' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Active Issues</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Reservations</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Library Members -->
        <li class="nav-item">
            <a href="{{ route('library-members.index') }}" class="nav-link {{ Request::is('library-members*') ? 'active' : '' }}">
                <i class="fas fa-users-cog nav-icon text-primary"></i>
                <p>Library Members</p>
            </a>
        </li>

        <!-- Reports Sub-menu -->
        <li class="nav-item has-treeview {{ Request::is('library/reports*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::is('library/reports*') ? 'active' : '' }}">
                <i class="fas fa-chart-pie nav-icon text-primary"></i>
                <p>
                    Library Reports
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview pl-3">
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Circulation Report</p>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</li>

<!-- Inventory Management -->
<li class="nav-item has-treeview {{ Request::is('inventory*') || Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('inventory*') || Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-boxes text-success"></i>
        <p>
            Inventory Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('inventory.dashboard') }}" class="nav-link {{ Request::is('inventory') ? 'active' : '' }}">
                <i class="fas fa-chart-line nav-icon text-success"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory-items.index') }}" class="nav-link {{ Request::is('inventory-items*') ? 'active' : '' }}">
                <i class="fas fa-list nav-icon text-success"></i>
                <p>Items Catalog</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory-categories.index') }}" class="nav-link {{ Request::is('inventory-categories*') ? 'active' : '' }}">
                <i class="fas fa-tags nav-icon text-success"></i>
                <p>Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ Request::is('suppliers*') ? 'active' : '' }}">
                <i class="fas fa-truck nav-icon text-success"></i>
                <p>Suppliers</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase">Stock Operations</li>
        <li class="nav-item">
            <a href="{{ route('inventory.add-stock.form') }}" class="nav-link {{ Request::is('inventory/add-stock*') ? 'active' : '' }}">
                <i class="fas fa-plus-square nav-icon text-success"></i>
                <p>Add Stock</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.issue-stock.form') }}" class="nav-link {{ Request::is('inventory/issue-stock*') ? 'active' : '' }}">
                <i class="fas fa-minus-square nav-icon text-danger"></i>
                <p>Issue Stock</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.adjust-stock.form') }}" class="nav-link {{ Request::is('inventory/adjust-stock*') ? 'active' : '' }}">
                <i class="fas fa-sliders-h nav-icon text-warning"></i>
                <p>Adjust Stock</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase">Procurement</li>
        <li class="nav-item">
            <a href="{{ route('inventory.requisitions.index') }}" class="nav-link {{ Request::is('inventory/requisitions*') ? 'active' : '' }}">
                <i class="fas fa-file-signature nav-icon text-info"></i>
                <p>Requisitions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.purchase-orders.index') }}" class="nav-link {{ Request::is('inventory/purchase-orders*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart nav-icon text-warning"></i>
                <p>Purchase Orders</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.stock-movement-history') }}" class="nav-link {{ Request::is('inventory/stock-movement-history') ? 'active' : '' }}">
                <i class="fas fa-history nav-icon text-secondary"></i>
                <p>Stock History</p>
            </a>
        </li>
    </ul>
</li>

<!-- Hostel Management -->
<li class="nav-item has-treeview {{ Request::is('hostel*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('hostel*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-hotel text-warning"></i>
        <p>
            Hostel Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('hostel.dashboard') }}" class="nav-link {{ Request::is('hostel/dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt nav-icon text-warning"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostels.index') }}" class="nav-link {{ Request::is('hostels*') ? 'active' : '' }}">
                <i class="fas fa-building nav-icon text-warning"></i>
                <p>Hostels</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel-rooms.index') }}" class="nav-link {{ Request::is('hostel-rooms*') ? 'active' : '' }}">
                <i class="fas fa-bed nav-icon text-warning"></i>
                <p>Rooms</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel-allocations.index') }}" class="nav-link {{ Request::is('hostel-allocations*') ? 'active' : '' }}">
                <i class="fas fa-user-check nav-icon text-warning"></i>
                <p>Allocations</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel.reports') }}" class="nav-link {{ Request::is('hostel/reports*') ? 'active' : '' }}">
                <i class="fas fa-file-alt nav-icon text-warning"></i>
                <p>Reports</p>
            </a>
        </li>
    </ul>
</li>

<!-- Transportation -->
<li class="nav-item has-treeview {{ Request::is('transportation*') || Request::is('routes*') || Request::is('route-stops*') || Request::is('student-transport-assignments*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('transportation*') || Request::is('routes*') || Request::is('route-stops*') || Request::is('student-transport-assignments*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bus text-danger"></i>
        <p>
            Transportation
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('transportation.dashboard') }}" class="nav-link {{ Request::is('transportation/dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt nav-icon text-danger"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('routes.index') }}" class="nav-link {{ Request::is('routes*') ? 'active' : '' }}">
                <i class="fas fa-route nav-icon text-danger"></i>
                <p>Routes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('routeStops.index') }}" class="nav-link {{ Request::is('route-stops*') ? 'active' : '' }}">
                <i class="fas fa-map-marker-alt nav-icon text-danger"></i>
                <p>Route Stops</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-transport-assignments.index') }}" class="nav-link {{ Request::is('student-transport-assignments*') ? 'active' : '' }}">
                <i class="fas fa-user-check nav-icon text-danger"></i>
                <p>Assignment</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('transportation.reports.index') }}" class="nav-link {{ Request::is('transportation/reports*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice nav-icon text-danger"></i>
                <p>Reports</p>
            </a>
        </li>
    </ul>
</li>

<!-- Communication -->
<li class="nav-item has-treeview {{ Request::is('sms-templates*') || Request::is('email-templates*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('sms-templates*') || Request::is('email-templates*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-comments text-secondary"></i>
        <p>
            Communication
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('sms-templates.index') }}" class="nav-link {{ Request::is('sms-templates*') ? 'active' : '' }}">
                <i class="far fa-sms nav-icon text-secondary"></i>
                <p>SMS Templates</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('email-templates.index') }}" class="nav-link {{ Request::is('email-templates*') ? 'active' : '' }}">
                <i class="far fa-envelope nav-icon text-secondary"></i>
                <p>Email Templates</p>
            </a>
        </li>
    </ul>
</li>
