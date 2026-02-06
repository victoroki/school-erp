<!-- REFINED STUDENT MANAGEMENT SIDEBAR MENU -->
<!-- Replace lines 165-206 in resources/views/layouts/menu-tooltip-fix.blade.php with this content -->

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
            <a href="#" class="nav-link" data-tooltip="Student ID Cards">
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
            <a href="#" class="nav-link" data-tooltip="Bulk Enrollment">
                <i class="fas fa-users-cog nav-icon text-warning"></i>
                <p>Bulk Enrollment</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-tooltip="Student Promotion">
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
            <a href="#" class="nav-link" data-tooltip="Student Attendance">
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
            <a href="#" class="nav-link" data-tooltip="Student Reports">
                <i class="fas fa-file-pdf nav-icon text-warning"></i>
                <p>Student Reports</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-tooltip="Class Lists">
                <i class="fas fa-list-alt nav-icon text-warning"></i>
                <p>Class Lists</p>
            </a>
        </li>
    </ul>
</li>
