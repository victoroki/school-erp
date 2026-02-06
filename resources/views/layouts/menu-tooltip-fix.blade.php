<li class="nav-header">CORE DASHBOARD</li>
<!-- Dashboard -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}" data-tooltip="Dashboard">
        <i class="nav-icon fas fa-tachometer-alt text-primary"></i>
        <p>Dashboard</p>
    </a>
</li>

<!-- User Management -->
<li class="nav-item has-treeview {{ Request::is('users*') || Request::is('roles*') || Request::is('permissions*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('users*') || Request::is('roles*') || Request::is('permissions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users-cog text-success"></i>
        <p>
            User Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        @can('users.index')
        <li class="nav-item">
            <a href="{{ route('users.index') }}" class="nav-link {{ Request::is('users*') ? 'active' : '' }}" data-tooltip="Users">
                <i class="far fa-user nav-icon text-success"></i>
                <p>Users</p>
            </a>
        </li>
        @endcan
        @can('roles.index')
        <li class="nav-item">
            <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}" data-tooltip="Roles">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>Roles</p>
            </a>
        </li>
        @endcan
        @can('permissions.index')
        <li class="nav-item">
            <a href="{{ route('permissions.index') }}" class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}" data-tooltip="Permissions">
                <i class="far fa-circle nav-icon text-success"></i>
                <p>Permissions</p>
            </a>
        </li>
        @endcan
    </ul>
</li>

<li class="nav-header">EDUCATIONAL UNITS</li>
<!-- Academic Management -->
<li class="nav-item has-treeview {{ Request::is('academic-dashboard*') || Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*') || Request::is('timetables*') || Request::is('departments*') || Request::is('academic-calendar*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('academic-dashboard*') || Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*') || Request::is('timetables*') || Request::is('departments*') || Request::is('academic-calendar*') ? 'active' : '' }}" data-tooltip="Academic Management">
        <i class="nav-icon fas fa-graduation-cap text-info"></i>
        <p>
            Academic Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('academic-dashboard.index') }}" class="nav-link {{ Request::is('academic-dashboard*') ? 'active' : '' }}" data-tooltip="Academic Dashboard">
                <i class="fas fa-chart-line nav-icon text-info"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-info">Structure</li>
        <li class="nav-item">
            <a href="{{ route('academic-years.index') }}" class="nav-link {{ Request::is('academic-years*') ? 'active' : '' }}" data-tooltip="Academic Years">
                <i class="far fa-calendar nav-icon text-info"></i>
                <p>Academic Years</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('school-classes.index') }}" class="nav-link {{ Request::is('school-classes*') ? 'active' : '' }}" data-tooltip="Classes">
                <i class="fas fa-chalkboard nav-icon text-info"></i>
                <p>Classes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sections.index') }}" class="nav-link {{ Request::is('sections*') ? 'active' : '' }}" data-tooltip="Sections">
                <i class="fas fa-layer-group nav-icon text-info"></i>
                <p>Sections</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('class-sections.index') }}" class="nav-link {{ Request::is('class-sections*') ? 'active' : '' }}" data-tooltip="Class Sections">
                <i class="fas fa-sitemap nav-icon text-info"></i>
                <p>Class Sections</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('classrooms.index') }}" class="nav-link {{ Request::is('classrooms*') ? 'active' : '' }}" data-tooltip="Classrooms">
                <i class="fas fa-door-open nav-icon text-info"></i>
                <p>Classrooms</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-info">Curriculum</li>
        <li class="nav-item">
            <a href="{{ route('subjects.index') }}" class="nav-link {{ Request::is('subjects*') ? 'active' : '' }}" data-tooltip="Subjects">
                <i class="fas fa-book-open nav-icon text-info"></i>
                <p>Subjects</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('class-subjects.index') }}" class="nav-link {{ Request::is('class-subjects*') ? 'active' : '' }}" data-tooltip="Class Subjects">
                <i class="fas fa-link nav-icon text-info"></i>
                <p>Class Subjects</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('departments.index') }}" class="nav-link {{ Request::is('departments*') ? 'active' : '' }}" data-tooltip="Departments">
                <i class="fas fa-building nav-icon text-info"></i>
                <p>Departments</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-info">Scheduling</li>
        <li class="nav-item">
            <a href="{{ route('periods.index') }}" class="nav-link {{ Request::is('periods*') ? 'active' : '' }}" data-tooltip="Periods">
                <i class="fas fa-clock nav-icon text-info"></i>
                <p>Periods</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('timetables.index') }}" class="nav-link {{ Request::is('timetables*') && !Request::is('timetables/teacher*') && !Request::is('timetables/class*') ? 'active' : '' }}" data-tooltip="Master Timetables">
                <i class="fas fa-calendar-alt nav-icon text-info"></i>
                <p>Master Timetables</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('timetables.teacher') }}" class="nav-link {{ Request::is('timetables/teacher*') ? 'active' : '' }}" data-tooltip="Teacher Timetables">
                <i class="fas fa-user-clock nav-icon text-info"></i>
                <p>Teacher Timetables</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('academic-calendar.index') }}" class="nav-link {{ Request::is('academic-calendar*') ? 'active' : '' }}" data-tooltip="Academic Calendar">
                <i class="fas fa-calendar-check nav-icon text-info"></i>
                <p>Academic Calendar</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-info">Teachers</li>
        <li class="nav-item">
            <a href="{{ route('teacher-subjects.index') }}" class="nav-link {{ Request::is('teacher-subjects*') ? 'active' : '' }}" data-tooltip="Teacher Subjects">
                <i class="fas fa-user-tie nav-icon text-info"></i>
                <p>Teacher Subjects</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('class-teachers.index') }}" class="nav-link {{ Request::is('class-teachers*') ? 'active' : '' }}" data-tooltip="Class Teachers">
                <i class="fas fa-chalkboard-teacher nav-icon text-info"></i>
                <p>Class Teachers</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('teacher-workload.index') }}" class="nav-link {{ Request::is('teacher-workload*') ? 'active' : '' }}" data-tooltip="Teacher Workload">
                <i class="fas fa-tasks nav-icon text-info"></i>
                <p>Teacher Workload</p>
            </a>
        </li>
    </ul>
</li>

<!-- Student Management -->
<li class="nav-item has-treeview {{ Request::is('student-dashboard*') || Request::is('students*') || Request::is('student-class-enrollments*') || Request::is('student-parent-relationships*') || Request::is('student-documents*') || Request::is('parents*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('student-dashboard*') || Request::is('students*') || Request::is('student-class-enrollments*') || Request::is('student-parent-relationships*') || Request::is('student-documents*') || Request::is('parents*') ? 'active' : '' }}" data-tooltip="Student Management">
        <i class="nav-icon fas fa-user-graduate text-warning"></i>
        <p>
            Student Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('student-dashboard.index') }}" class="nav-link {{ Request::is('student-dashboard*') ? 'active' : '' }}" data-tooltip="Student Dashboard">
                <i class="fas fa-chart-line nav-icon text-warning"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-warning">Student Records</li>
        <li class="nav-item">
            <a href="{{ route('students.index') }}" class="nav-link {{ Request::is('students') || Request::is('students/show*') ? 'active' : '' }}" data-tooltip="All Students">
                <i class="fas fa-users nav-icon text-warning"></i>
                <p>All Students</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('students.create') }}" class="nav-link {{ Request::is('students/create') ? 'active' : '' }}" data-tooltip="Admit New Student">
                <i class="fas fa-user-plus nav-icon text-warning"></i>
                <p>Student Admission</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-documents.index') }}" class="nav-link {{ Request::is('student-documents*') ? 'active' : '' }}" data-tooltip="Student Documents">
                <i class="fas fa-file-alt nav-icon text-warning"></i>
                <p>Student Documents</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('students.index') }}?view=id_cards" class="nav-link" data-tooltip="Student ID Cards">
                <i class="fas fa-id-card nav-icon text-warning"></i>
                <p>Student ID Cards</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-warning">Enrollment & Classes</li>
        <li class="nav-item">
            <a href="{{ route('student-class-enrollments.index') }}" class="nav-link {{ Request::is('student-class-enrollments*') ? 'active' : '' }}" data-tooltip="Class Enrollments">
                <i class="fas fa-clipboard-list nav-icon text-warning"></i>
                <p>Class Enrollments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('students.import') }}" class="nav-link {{ Request::is('students/import') ? 'active' : '' }}" data-tooltip="Bulk Enrollment">
                <i class="fas fa-users-cog nav-icon text-warning"></i>
                <p>Bulk Enrollment</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-promotion.index') }}" class="nav-link {{ Request::is('student-promotion*') ? 'active' : '' }}" data-tooltip="Student Promotion">
                <i class="fas fa-level-up-alt nav-icon text-warning"></i>
                <p>Student Promotion</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-tooltip="Student Transfer">
                <i class="fas fa-exchange-alt nav-icon text-warning"></i>
                <p>Student Transfer</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-warning">Family & Guardians</li>
        <li class="nav-item">
            <a href="{{ route('parents.index') }}" class="nav-link {{ Request::is('parents*') ? 'active' : '' }}" data-tooltip="Parents/Guardians">
                <i class="fas fa-user-friends nav-icon text-warning"></i>
                <p>Parents/Guardians</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-parent-relationships.index') }}" class="nav-link {{ Request::is('student-parent-relationships*') ? 'active' : '' }}" data-tooltip="Parent Relationships">
                <i class="fas fa-link nav-icon text-warning"></i>
                <p>Parent Relationships</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-tooltip="Emergency Contacts">
                <i class="fas fa-phone-square nav-icon text-warning"></i>
                <p>Emergency Contacts</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-warning">Student Services</li>
        <li class="nav-item">
            <a href="{{ route('student-attendance.index') }}" class="nav-link {{ Request::is('student-attendance*') ? 'active' : '' }}" data-tooltip="Student Attendance">
                <i class="fas fa-calendar-check nav-icon text-warning"></i>
                <p>Student Attendance</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-tooltip="Disciplinary Records">
                <i class="fas fa-gavel nav-icon text-warning"></i>
                <p>Disciplinary Records</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-tooltip="Medical Records">
                <i class="fas fa-notes-medical nav-icon text-warning"></i>
                <p>Medical Records</p>
            </a>
        </li>
        <li class="nav-header small text-uppercase text-warning">Reports</li>
        <li class="nav-item">
            <a href="{{ route('student-reports.index') }}" class="nav-link {{ Request::is('student-reports*') ? 'active' : '' }}" data-tooltip="Student Reports">
                <i class="fas fa-file-invoice nav-icon text-warning"></i>
                <p>Student Reports</p>
            </a>
        </li>
    </ul>
</li>

<!-- Examination Management -->
<li class="nav-item has-treeview {{ Request::is('exam*') || Request::is('grading-scales*') || Request::is('assessment-types*') || Request::is('exam-rooms*') || Request::is('grade-book*') || Request::is('mark-sheets*') || Request::is('marks-approval*') || Request::is('report-card-templates*') || Request::is('learning-areas*') || Request::is('strands*') || Request::is('competency-assessment*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('exam*') || Request::is('grading-scales*') || Request::is('assessment-types*') || Request::is('exam-rooms*') || Request::is('grade-book*') || Request::is('mark-sheets*') || Request::is('marks-approval*') || Request::is('report-card-templates*') || Request::is('learning-areas*') || Request::is('strands*') || Request::is('competency-assessment*') ? 'active' : '' }}" data-tooltip="Examinations">
        <i class="nav-icon fas fa-file-invoice text-danger"></i>
        <p>
            Examinations
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('exam-dashboard.index') }}" class="nav-link {{ Request::is('exam-dashboard') ? 'active' : '' }}" data-tooltip="Examinations Dashboard">
                <i class="fas fa-chart-line nav-icon text-danger"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Configuration</li>
        <li class="nav-item">
            <a href="{{ route('exam-types.index') }}" class="nav-link {{ Request::is('exam-types*') ? 'active' : '' }}" data-tooltip="Exam Categories">
                <i class="fas fa-tags nav-icon text-danger"></i>
                <p>Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grading-scales.index') }}" class="nav-link {{ Request::is('grading-scales*') ? 'active' : '' }}" data-tooltip="Grading Systems">
                <i class="fas fa-star-half-alt nav-icon text-danger"></i>
                <p>Grading Systems</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('assessment-types.index') }}" class="nav-link {{ Request::is('assessment-types*') ? 'active' : '' }}" data-tooltip="Assessment Types">
                <i class="fas fa-clipboard-check nav-icon text-danger"></i>
                <p>Assessments</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Management</li>
        <li class="nav-item">
            <a href="{{ route('exams.index') }}" class="nav-link {{ Request::is('exams*') && !Request::is('exam-dashboard*') ? 'active' : '' }}" data-tooltip="Exam Sessions">
                <i class="fas fa-calendar-alt nav-icon text-danger"></i>
                <p>Sessions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-schedules.index') }}" class="nav-link {{ Request::is('exam-schedules*') ? 'active' : '' }}" data-tooltip="Exam Timetables">
                <i class="fas fa-clock nav-icon text-danger"></i>
                <p>Timetables</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-rooms.index') }}" class="nav-link {{ Request::is('exam-rooms*') ? 'active' : '' }}" data-tooltip="Exam Room Allocation">
                <i class="fas fa-door-open nav-icon text-danger"></i>
                <p>Rooms</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Marks</li>
        <li class="nav-item">
            <a href="{{ route('exam-results.index') }}" class="nav-link {{ Request::is('exam-results*') ? 'active' : '' }}" data-tooltip="Enter Exam Marks">
                <i class="fas fa-edit nav-icon text-danger"></i>
                <p>Enter Marks</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grade-book.index') }}" class="nav-link {{ Request::is('grade-book*') ? 'active' : '' }}" data-tooltip="Full Grade Book">
                <i class="fas fa-book nav-icon text-danger"></i>
                <p>Grade Book</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('marks-approval.index') }}" class="nav-link {{ Request::is('marks-approval*') ? 'active' : '' }}" data-tooltip="Approve Results">
                <i class="fas fa-check-circle nav-icon text-danger"></i>
                <p>Approval</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Reports</li>
        <li class="nav-item">
            <a href="{{ route('exam-reports.generate') }}" class="nav-link {{ Request::is('exam-reports/generate*') ? 'active' : '' }}" data-tooltip="Generate Report Cards">
                <i class="fas fa-file-pdf nav-icon text-danger"></i>
                <p>Report Cards</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-analysis.performance') }}" class="nav-link {{ Request::is('exam-analysis/performance*') ? 'active' : '' }}" data-tooltip="Performance Analysis">
                <i class="fas fa-chart-bar nav-icon text-danger"></i>
                <p>Analysis</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">CBC</li>
        <li class="nav-item">
            <a href="{{ route('learning-areas.index') }}" class="nav-link {{ Request::is('learning-areas*') ? 'active' : '' }}" data-tooltip="CBC Learning Areas">
                <i class="fas fa-brain nav-icon text-danger"></i>
                <p>Learning Areas</p>
            </a>
        </li>
    </ul>
</li>

<!-- Inventory Management -->
<li class="nav-item has-treeview {{ Request::is('inventory*') || Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('inventory*') || Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'active' : '' }}" data-tooltip="Inventory Management">
        <i class="nav-icon fas fa-boxes text-success"></i>
        <p>
            Inventory Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('inventory.dashboard') }}" class="nav-link {{ Request::is('inventory') ? 'active' : '' }}" data-tooltip="Inventory Dashboard">
                <i class="fas fa-chart-line nav-icon text-success"></i>
                <p>Dashboard</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory-items.index') }}" class="nav-link {{ Request::is('inventory-items*') ? 'active' : '' }}" data-tooltip="All Items / Catalog">
                <i class="fas fa-list nav-icon text-success"></i>
                <p>Items Catalog</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory-categories.index') }}" class="nav-link {{ Request::is('inventory-categories*') ? 'active' : '' }}" data-tooltip="Categories">
                <i class="fas fa-tags nav-icon text-success"></i>
                <p>Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ Request::is('suppliers*') ? 'active' : '' }}" data-tooltip="Suppliers">
                <i class="fas fa-truck nav-icon text-success"></i>
                <p>Suppliers</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase">Operations</li>
        <li class="nav-item">
            <a href="{{ route('inventory.requisitions.index') }}" class="nav-link {{ Request::is('inventory/requisitions*') ? 'active' : '' }}" data-tooltip="Request Items">
                <i class="fas fa-file-signature nav-icon text-info"></i>
                <p>Requisitions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.purchase-orders.index') }}" class="nav-link {{ Request::is('inventory/purchase-orders*') ? 'active' : '' }}" data-tooltip="Purchase Orders">
                <i class="fas fa-shopping-cart nav-icon text-warning"></i>
                <p>Purchase Orders</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory.stock-movement-history') }}" class="nav-link {{ Request::is('inventory/stock-movement-history') ? 'active' : '' }}" data-tooltip="Movement History">
                <i class="fas fa-history nav-icon text-secondary"></i>
                <p>Stock History</p>
            </a>
        </li>
    </ul>
</li>
<li class="nav-item has-treeview {{ Request::is('library*') || Request::is('book-categories*') || Request::is('books*') || Request::is('library-members*') || Request::is('book-issues*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('library*') || Request::is('book-categories*') || Request::is('books*') || Request::is('library-members*') || Request::is('book-issues*') ? 'active' : '' }}" data-tooltip="Library Management">
        <i class="nav-icon fas fa-book text-primary"></i>
        <p>
            Library Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('library.dashboard') }}" class="nav-link {{ Request::is('library/dashboard') ? 'active' : '' }}" data-tooltip="Library Dashboard">
                <i class="fas fa-tachometer-alt nav-icon text-primary"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <!-- Books Management -->
        <li class="nav-item has-treeview {{ Request::is('books*') || Request::is('book-categories*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::is('books*') || Request::is('book-categories*') ? 'active' : '' }}" data-tooltip="Books Catalog">
                <i class="fas fa-swatchbook nav-icon text-primary"></i>
                <p>
                    Books Catalog
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview pl-3">
                <li class="nav-item">
                    <a href="{{ route('books.index') }}" class="nav-link {{ Request::is('books') ? 'active' : '' }}" data-tooltip="All Books">
                        <i class="far fa-circle nav-icon"></i>
                        <p>All Books</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('book-categories.index') }}" class="nav-link {{ Request::is('book-categories*') ? 'active' : '' }}" data-tooltip="Book Categories">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Categories</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Circulation -->
        <li class="nav-item has-treeview {{ Request::is('book-issues*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::is('book-issues*') ? 'active' : '' }}" data-tooltip="Circulation / Issues">
                <i class="fas fa-sync-alt nav-icon text-primary"></i>
                <p>
                    Circulation
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview pl-3">
                <li class="nav-item">
                    <a href="{{ route('book-issues.create') }}" class="nav-link {{ Request::is('book-issues/create') ? 'active' : '' }}" data-tooltip="Issue New Book">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Issue Book</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('book-issues.index') }}" class="nav-link {{ Request::is('book-issues') ? 'active' : '' }}" data-tooltip="Active Issues">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Active Issues</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tooltip="Reservations">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Reservations</p>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Library Members -->
        <li class="nav-item">
            <a href="{{ route('library-members.index') }}" class="nav-link {{ Request::is('library-members*') ? 'active' : '' }}" data-tooltip="Library Members">
                <i class="fas fa-users-cog nav-icon text-primary"></i>
                <p>Library Members</p>
            </a>
        </li>

        <!-- Reports -->
        <li class="nav-item has-treeview {{ Request::is('library/reports*') ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ Request::is('library/reports*') ? 'active' : '' }}" data-tooltip="Library Reports">
                <i class="fas fa-chart-pie nav-icon text-primary"></i>
                <p>
                    Reports
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview pl-3">
                <li class="nav-item">
                    <a href="#" class="nav-link" data-tooltip="Circulation Report">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Circulation Report</p>
                    </a>
                </li>
            </ul>
        </li>
    </ul>
</li>

<li class="nav-header">GOVERNANCE</li>
<!-- Human Resources -->
<li class="nav-item has-treeview {{ Request::is('departments*') || Request::is('job-positions*') || Request::is('leave-types*') || Request::is('staff-documents*') || Request::is('payrolls*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('departments*') || Request::is('job-positions*') || Request::is('leave-types*') || Request::is('staff-documents*') || Request::is('payrolls*') ? 'active' : '' }}" data-tooltip="Human Resources">
        <i class="nav-icon fas fa-user-tie text-secondary"></i>
        <p>
            Human Resources
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('departments.index') }}" class="nav-link {{ Request::is('departments*') ? 'active' : '' }}" data-tooltip="Departments">
                <i class="far fa-building nav-icon text-secondary"></i>
                <p>Departments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('job-positions.index') }}" class="nav-link {{ Request::is('job-positions*') ? 'active' : '' }}" data-tooltip="Job Positions">
                <i class="far fa-briefcase nav-icon text-secondary"></i>
                <p>Job Positions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('leave-types.index') }}" class="nav-link {{ Request::is('leave-types*') ? 'active' : '' }}" data-tooltip="Leave Types">
                <i class="far fa-calendar-times nav-icon text-secondary"></i>
                <p>Leave Types</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('staff.index') }}" class="nav-link {{ Request::is('staff*') ? 'active' : '' }}" data-tooltip="Staff">
                <i class="nav-icon fas fa-work"></i>
                <p>Staff</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('staff-documents.index') }}" class="nav-link {{ Request::is('staff-documents*') ? 'active' : '' }}" data-tooltip="Staff Documents">
                <i class="far fa-folder nav-icon text-secondary"></i>
                <p>Staff Documents</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('payrolls.index') }}" class="nav-link {{ Request::is('payrolls*') ? 'active' : '' }}" data-tooltip="Payrolls">
                <i class="far fa-money-check-alt nav-icon text-secondary"></i>
                <p>Payrolls</p>
            </a>
        </li>
    </ul>
</li>

<!-- Financial Management -->
<li class="nav-item has-treeview {{ Request::is('finance*') || Request::is('expenses*') || Request::is('income*') || Request::is('bank*') || Request::is('student-fee-discounts*') || Request::is('budgets*') || Request::is('financial-reports*') || Request::is('financial-years*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('finance*') || Request::is('expenses*') || Request::is('income*') || Request::is('bank*') || Request::is('student-fee-discounts*') || Request::is('budgets*') || Request::is('financial-reports*') || Request::is('financial-years*') ? 'active' : '' }}" data-tooltip="Financial Management">
        <i class="nav-icon fas fa-chart-line text-dark"></i>
        <p>
            Financial Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('finance.dashboard') }}" class="nav-link {{ Request::is('finance/dashboard') ? 'active' : '' }}" data-tooltip="Finance Dashboard">
                <i class="fas fa-tachometer-alt nav-icon text-dark"></i>
                <p>Dashboard</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-success">Income</li>
        <li class="nav-item">
            <a href="{{ route('incomeCategories.index') }}" class="nav-link {{ Request::is('income-categories*') ? 'active' : '' }}" data-tooltip="Income Categories">
                <i class="fas fa-plus-circle nav-icon text-success"></i>
                <p>Income Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('income.create') }}" class="nav-link {{ Request::is('income/create') ? 'active' : '' }}" data-tooltip="Record Income">
                <i class="fas fa-arrow-down nav-icon text-success"></i>
                <p>Record Income</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('income.index') }}" class="nav-link {{ Request::is('income') ? 'active' : '' }}" data-tooltip="Income Transactions">
                <i class="fas fa-list nav-icon text-success"></i>
                <p>Income Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('fee-management.index') }}" class="nav-link {{ Request::is('fee-management*') ? 'active' : '' }}" data-tooltip="Fee Collection">
                <i class="fas fa-cash-register nav-icon text-success"></i>
                <p>Fee Collection</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-danger">Expenses</li>
        <li class="nav-item">
            <a href="{{ route('expenseCategories.index') }}" class="nav-link {{ Request::is('expense-categories*') ? 'active' : '' }}" data-tooltip="Expense Categories">
                <i class="fas fa-minus-circle nav-icon text-danger"></i>
                <p>Expense Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.create') }}" class="nav-link {{ Request::is('expenses/create') ? 'active' : '' }}" data-tooltip="Record Expense">
                <i class="fas fa-arrow-up nav-icon text-danger"></i>
                <p>Record Expense</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.index') }}" class="nav-link {{ Request::is('expenses') ? 'active' : '' }}" data-tooltip="Expense Transactions">
                <i class="fas fa-receipt nav-icon text-danger"></i>
                <p>Expense Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.pending') }}" class="nav-link {{ Request::is('expenses/pending') ? 'active' : '' }}" data-tooltip="Pending Approvals">
                <i class="fas fa-clock nav-icon text-warning"></i>
                <p>Pending Approvals</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-primary">Banking</li>
        <li class="nav-item">
            <a href="{{ route('bank-accounts.index') }}" class="nav-link {{ Request::is('bank-accounts*') ? 'active' : '' }}" data-tooltip="Bank Accounts">
                <i class="fas fa-university nav-icon text-primary"></i>
                <p>Bank Accounts</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bank-transactions.index') }}" class="nav-link {{ Request::is('bank-transactions*') ? 'active' : '' }}" data-tooltip="Bank Transactions">
                <i class="fas fa-exchange-alt nav-icon text-primary"></i>
                <p>Bank Transactions</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bank-reconciliations.index') }}" class="nav-link {{ Request::is('bank-reconciliations*') ? 'active' : '' }}" data-tooltip="Bank Reconciliation">
                <i class="fas fa-balance-scale nav-icon text-primary"></i>
                <p>Bank Reconciliation</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-info">Budgeting</li>
        <li class="nav-item">
            <a href="{{ route('budgets.index') }}" class="nav-link {{ Request::is('budgets*') ? 'active' : '' }}" data-tooltip="Budget Planning">
                <i class="fas fa-clipboard-list nav-icon text-info"></i>
                <p>Budget Planning</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('budgets.vs-actual') }}" class="nav-link {{ Request::is('budgets/vs-actual') ? 'active' : '' }}" data-tooltip="Budget vs Actual">
                <i class="fas fa-chart-bar nav-icon text-info"></i>
                <p>Budget vs Actual</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-dark">Reports & Analysis</li>
        <li class="nav-item">
            <a href="{{ route('financial-reports.index') }}" class="nav-link {{ Request::is('financial-reports*') ? 'active' : '' }}" data-tooltip="Financial Reports">
                <i class="fas fa-file-alt nav-icon text-dark"></i>
                <p>Financial Reports</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-reports.cashflow') }}" class="nav-link {{ Request::is('financial-reports/cashflow') ? 'active' : '' }}" data-tooltip="Cashflow Analysis">
                <i class="fas fa-chart-line nav-icon text-dark"></i>
                <p>Cashflow Analysis</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-reports.p-and-l') }}" class="nav-link {{ Request::is('financial-reports/p-and-l') ? 'active' : '' }}" data-tooltip="Profit & Loss">
                <i class="fas fa-calculator nav-icon text-dark"></i>
                <p>Profit & Loss</p>
            </a>
        </li>

        <li class="nav-header small text-uppercase text-secondary">Auditing & Setup</li>
        <li class="nav-item">
            <a href="{{ route('audit-trail.index') }}" class="nav-link {{ Request::is('audit-trail*') ? 'active' : '' }}" data-tooltip="Audit Trail">
                <i class="fas fa-history nav-icon text-secondary"></i>
                <p>Audit Trail</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-years.index') }}" class="nav-link {{ Request::is('financial-years*') ? 'active' : '' }}" data-tooltip="Financial Year">
                <i class="fas fa-calendar-alt nav-icon text-secondary"></i>
                <p>Financial Year</p>
            </a>
        </li>
    </ul>
</li>

<!-- Fee Management -->
<li class="nav-item has-treeview {{ Request::is('fee-categories*') || Request::is('fee-structures*') || Request::is('student-fee-discounts*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('fee-categories*') || Request::is('fee-structures*') || Request::is('student-fee-discounts*') ? 'active' : '' }}" data-tooltip="Fee Management">
        <i class="nav-icon fas fa-money-bill-wave text-success"></i>
        <p>
            Fee Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('fee-categories.index') }}" class="nav-link {{ Request::is('fee-categories*') ? 'active' : '' }}" data-tooltip="Fee Categories">
                <i class="far fa-tags nav-icon text-success"></i>
                <p>Fee Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('fee-structures.index') }}" class="nav-link {{ Request::is('fee-structures*') ? 'active' : '' }}" data-tooltip="Fee Structures">
                <i class="far fa-table nav-icon text-success"></i>
                <p>Fee Structures</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-fee-discounts.index') }}" class="nav-link {{ Request::is('student-fee-discounts*') ? 'active' : '' }}" data-tooltip="Student Fee Discounts">
                <i class="far fa-percent nav-icon text-success"></i>
                <p>Student Fee Discounts</p>
            </a>
        </li>
    </ul>
</li>


<!-- Inventory Management -->
<li class="nav-item has-treeview {{ Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'active' : '' }}" data-tooltip="Inventory Management">
        <i class="nav-icon fas fa-boxes text-info"></i>
        <p>
            Inventory Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('inventory-categories.index') }}" class="nav-link {{ Request::is('inventory-categories*') ? 'active' : '' }}" data-tooltip="Inventory Categories">
                <i class="far fa-list-alt nav-icon text-info"></i>
                <p>Inventory Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory-items.index') }}" class="nav-link {{ Request::is('inventory-items*') ? 'active' : '' }}" data-tooltip="Inventory Items">
                <i class="far fa-cube nav-icon text-info"></i>
                <p>Inventory Items</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ Request::is('suppliers*') ? 'active' : '' }}" data-tooltip="Suppliers">
                <i class="far fa-truck nav-icon text-info"></i>
                <p>Suppliers</p>
            </a>
        </li>
    </ul>
</li>

<!-- Hostel Management -->
<li class="nav-item has-treeview {{ Request::is('hostels*') || Request::is('hostel-rooms*') || Request::is('hostel-allocations*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('hostels*') || Request::is('hostel-rooms*') || Request::is('hostel-allocations*') ? 'active' : '' }}" data-tooltip="Hostel Management">
        <i class="nav-icon fas fa-home text-warning"></i>
        <p>
            Hostel Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('hostels.index') }}" class="nav-link {{ Request::is('hostels*') ? 'active' : '' }}" data-tooltip="Hostels">
                <i class="far fa-building nav-icon text-warning"></i>
                <p>Hostels</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel-rooms.index') }}" class="nav-link {{ Request::is('hostel-rooms*') ? 'active' : '' }}" data-tooltip="Hostel Rooms">
                <i class="far fa-bed nav-icon text-warning"></i>
                <p>Hostel Rooms</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel-allocations.index') }}" class="nav-link {{ Request::is('hostel-allocations*') ? 'active' : '' }}" data-tooltip="Hostel Allocations">
                <i class="far fa-user-check nav-icon text-warning"></i>
                <p>Hostel Allocations</p>
            </a>
        </li>
    </ul>
</li>

<!-- Transportation -->
<li class="nav-item has-treeview {{ Request::is('routes*') || Request::is('route-stops*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('routes*') || Request::is('route-stops*') ? 'active' : '' }}" data-tooltip="Transportation">
        <i class="nav-icon fas fa-bus text-danger"></i>
        <p>
            Transportation
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('routes.index') }}" class="nav-link {{ Request::is('routes*') ? 'active' : '' }}" data-tooltip="Routes">
                <i class="far fa-route nav-icon text-danger"></i>
                <p>Routes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('route-stops.index') }}" class="nav-link {{ Request::is('route-stops*') ? 'active' : '' }}" data-tooltip="Route Stops">
                <i class="far fa-map-marker-alt nav-icon text-danger"></i>
                <p>Route Stops</p>
            </a>
        </li>
    </ul>
</li>

<!-- Communication -->
<li class="nav-item has-treeview {{ Request::is('sms-templates*') || Request::is('email-templates*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('sms-templates*') || Request::is('email-templates*') ? 'active' : '' }}" data-tooltip="Communication">
        <i class="nav-icon fas fa-comments text-secondary"></i>
        <p>
            Communication
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('sms-templates.index') }}" class="nav-link {{ Request::is('sms-templates*') ? 'active' : '' }}" data-tooltip="SMS Templates">
                <i class="far fa-sms nav-icon text-secondary"></i>
                <p>SMS Templates</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('email-templates.index') }}" class="nav-link {{ Request::is('email-templates*') ? 'active' : '' }}" data-tooltip="Email Templates">
                <i class="far fa-envelope nav-icon text-secondary"></i>
                <p>Email Templates</p>
            </a>
        </li>
    </ul>
</li>