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
<li class="nav-item has-treeview {{ Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-graduation-cap text-info"></i>
        <p>
            Academic Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('academic-years.index') }}" class="nav-link {{ Request::is('academic-years*') ? 'active' : '' }}">
                <i class="far fa-calendar nav-icon text-info"></i>
                <p>Academic Years</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('school-classes.index') }}" class="nav-link {{ Request::is('school-classes*') ? 'active' : '' }}">
                <i class="far fa-chalkboard nav-icon text-info"></i>
                <p>School Classes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('sections.index') }}" class="nav-link {{ Request::is('sections*') ? 'active' : '' }}">
                <i class="far fa-layer-group nav-icon text-info"></i>
                <p>Sections</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('class-sections.index') }}" class="nav-link {{ Request::is('class-sections*') ? 'active' : '' }}">
                <i class="far fa-sitemap nav-icon text-info"></i>
                <p>Class Sections</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('subjects.index') }}" class="nav-link {{ Request::is('subjects*') ? 'active' : '' }}">
                <i class="far fa-book-open nav-icon text-info"></i>
                <p>Subjects</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('class-subjects.index') }}" class="nav-link {{ Request::is('class-subjects*') ? 'active' : '' }}">
                <i class="far fa-link nav-icon text-info"></i>
                <p>Class Subjects</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('teacher-subjects.index') }}" class="nav-link {{ Request::is('teacher-subjects*') ? 'active' : '' }}">
                <i class="far fa-user-tie nav-icon text-info"></i>
                <p>Teacher Subjects</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('periods.index') }}" class="nav-link {{ Request::is('periods*') ? 'active' : '' }}">
                <i class="far fa-clock nav-icon text-info"></i>
                <p>Periods</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('classrooms.index') }}" class="nav-link {{ Request::is('classrooms*') ? 'active' : '' }}">
                <i class="far fa-door-open nav-icon text-info"></i>
                <p>Classrooms</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('timetables.index') }}" class="nav-link {{ Request::is('timetables*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-home"></i>
                <p>Timetables</p>
            </a>
        </li>
    </ul>
</li>

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
<li class="nav-item has-treeview {{ Request::is('exam-types*') || Request::is('grading-scales*') || Request::is('exams*') || Request::is('exam-schedules*') || Request::is('exam-results*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('exam-types*') || Request::is('grading-scales*') || Request::is('exams*') || Request::is('exam-schedules*') || Request::is('exam-results*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-clipboard-list text-danger"></i>
        <p>
            Examination
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('exam-types.index') }}" class="nav-link {{ Request::is('exam-types*') ? 'active' : '' }}">
                <i class="far fa-list nav-icon text-danger"></i>
                <p>Exam Types</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('grading-scales.index') }}" class="nav-link {{ Request::is('grading-scales*') ? 'active' : '' }}">
                <i class="far fa-chart-bar nav-icon text-danger"></i>
                <p>Grading Scales</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exams.index') }}" class="nav-link {{ Request::is('exams*') ? 'active' : '' }}">
                <i class="far fa-file-alt nav-icon text-danger"></i>
                <p>Exams</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-schedules.index') }}" class="nav-link {{ Request::is('exam-schedules*') ? 'active' : '' }}">
                <i class="far fa-calendar-alt nav-icon text-danger"></i>
                <p>Exam Schedules</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('exam-results.index') }}" class="nav-link {{ Request::is('exam-results*') ? 'active' : '' }}">
                <i class="far fa-trophy nav-icon text-danger"></i>
                <p>Exam Results</p>
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
<li class="nav-item has-treeview {{ Request::is('expense-categories*') || Request::is('income-categories*') || Request::is('bank-accounts*') || Request::is('expenses*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('expense-categories*') || Request::is('income-categories*') || Request::is('bank-accounts*') || Request::is('expenses*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-line text-dark"></i>
        <p>
            Financial Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('expense-categories.index') }}" class="nav-link {{ Request::is('expense-categories*') ? 'active' : '' }}">
                <i class="far fa-minus-circle nav-icon text-dark"></i>
                <p>Expense Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.index') }}" class="nav-link {{ Request::is('expenses*') ? 'active' : '' }}">
                <i class="far fa-credit-card nav-icon text-dark"></i>
                <p>Expenses</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('income-categories.index') }}" class="nav-link {{ Request::is('income-categories*') ? 'active' : '' }}">
                <i class="far fa-plus-circle nav-icon text-dark"></i>
                <p>Income Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bank-accounts.index') }}" class="nav-link {{ Request::is('bank-accounts*') ? 'active' : '' }}">
                <i class="far fa-university nav-icon text-dark"></i>
                <p>Bank Accounts</p>
            </a>
        </li>
    </ul>
</li>

<!-- Fee Management -->
<li class="nav-item has-treeview {{ Request::is('fee-categories*') || Request::is('fee-structures*') || Request::is('student-fee-discounts*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('fee-categories*') || Request::is('fee-structures*') || Request::is('student-fee-discounts*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-wave text-success"></i>
        <p>
            Fee Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('fee-categories.index') }}" class="nav-link {{ Request::is('fee-categories*') ? 'active' : '' }}">
                <i class="far fa-tags nav-icon text-success"></i>
                <p>Fee Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('fee-structures.index') }}" class="nav-link {{ Request::is('fee-structures*') ? 'active' : '' }}">
                <i class="far fa-table nav-icon text-success"></i>
                <p>Fee Structures</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('student-fee-discounts.index') }}" class="nav-link {{ Request::is('student-fee-discounts*') ? 'active' : '' }}">
                <i class="far fa-percent nav-icon text-success"></i>
                <p>Student Fee Discounts</p>
            </a>
        </li>
    </ul>
</li>

<!-- Library Management -->
<li class="nav-item has-treeview {{ Request::is('book-categories*') || Request::is('books*') || Request::is('library-members*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('book-categories*') || Request::is('books*') || Request::is('library-members*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-book text-primary"></i>
        <p>
            Library Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('book-categories.index') }}" class="nav-link {{ Request::is('book-categories*') ? 'active' : '' }}">
                <i class="far fa-bookmark nav-icon text-primary"></i>
                <p>Book Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('books.index') }}" class="nav-link {{ Request::is('books*') ? 'active' : '' }}">
                <i class="far fa-book nav-icon text-primary"></i>
                <p>Books</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('library-members.index') }}" class="nav-link {{ Request::is('library-members*') ? 'active' : '' }}">
                <i class="far fa-id-card nav-icon text-primary"></i>
                <p>Library Members</p>
            </a>
        </li>
    </ul>
</li>

<!-- Inventory Management -->
<li class="nav-item has-treeview {{ Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('inventory-categories*') || Request::is('inventory-items*') || Request::is('suppliers*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-boxes text-info"></i>
        <p>
            Inventory Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('inventory-categories.index') }}" class="nav-link {{ Request::is('inventory-categories*') ? 'active' : '' }}">
                <i class="far fa-list-alt nav-icon text-info"></i>
                <p>Inventory Categories</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('inventory-items.index') }}" class="nav-link {{ Request::is('inventory-items*') ? 'active' : '' }}">
                <i class="far fa-cube nav-icon text-info"></i>
                <p>Inventory Items</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('suppliers.index') }}" class="nav-link {{ Request::is('suppliers*') ? 'active' : '' }}">
                <i class="far fa-truck nav-icon text-info"></i>
                <p>Suppliers</p>
            </a>
        </li>
    </ul>
</li>

<!-- Hostel Management -->
<li class="nav-item has-treeview {{ Request::is('hostels*') || Request::is('hostel-rooms*') || Request::is('hostel-allocations*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('hostels*') || Request::is('hostel-rooms*') || Request::is('hostel-allocations*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home text-warning"></i>
        <p>
            Hostel Management
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('hostels.index') }}" class="nav-link {{ Request::is('hostels*') ? 'active' : '' }}">
                <i class="far fa-building nav-icon text-warning"></i>
                <p>Hostels</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel-rooms.index') }}" class="nav-link {{ Request::is('hostel-rooms*') ? 'active' : '' }}">
                <i class="far fa-bed nav-icon text-warning"></i>
                <p>Hostel Rooms</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('hostel-allocations.index') }}" class="nav-link {{ Request::is('hostel-allocations*') ? 'active' : '' }}">
                <i class="far fa-user-check nav-icon text-warning"></i>
                <p>Hostel Allocations</p>
            </a>
        </li>
    </ul>
</li>

<!-- Transportation -->
<li class="nav-item has-treeview {{ Request::is('routes*') || Request::is('route-stops*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('routes*') || Request::is('route-stops*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bus text-danger"></i>
        <p>
            Transportation
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('routes.index') }}" class="nav-link {{ Request::is('routes*') ? 'active' : '' }}">
                <i class="far fa-route nav-icon text-danger"></i>
                <p>Routes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('route-stops.index') }}" class="nav-link {{ Request::is('route-stops*') ? 'active' : '' }}">
                <i class="far fa-map-marker-alt nav-icon text-danger"></i>
                <p>Route Stops</p>
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