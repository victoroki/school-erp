<!-- need to remove -->
<li class="nav-item">
    <a href="{{ route('home') }}" class="nav-link {{ Request::is('home') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Home</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('roles.index') }}" class="nav-link {{ Request::is('roles*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Roles</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('permissions.index') }}" class="nav-link {{ Request::is('permissions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Permissions</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('departments.index') }}" class="nav-link {{ Request::is('departments*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Departments</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('academicYears.index') }}" class="nav-link {{ Request::is('academicYears*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Academic Years</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('schoolClasses.index') }}" class="nav-link {{ Request::is('schoolClasses*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>School Classes</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('sections.index') }}" class="nav-link {{ Request::is('sections*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Sections</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('classrooms.index') }}" class="nav-link {{ Request::is('classrooms*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Classrooms</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('subjects.index') }}" class="nav-link {{ Request::is('subjects*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Subjects</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('periods.index') }}" class="nav-link {{ Request::is('periods*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Periods</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('examTypes.index') }}" class="nav-link {{ Request::is('examTypes*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Exam Types</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('gradingScales.index') }}" class="nav-link {{ Request::is('gradingScales*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Grading Scales</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('feeCategories.index') }}" class="nav-link {{ Request::is('feeCategories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Fee Categories</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('bookCategories.index') }}" class="nav-link {{ Request::is('bookCategories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Book Categories</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('inventoryCategories.index') }}" class="nav-link {{ Request::is('inventoryCategories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Inventory Categories</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('suppliers.index') }}" class="nav-link {{ Request::is('suppliers*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Suppliers</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('expenseCategories.index') }}" class="nav-link {{ Request::is('expenseCategories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Expense Categories</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('incomeCategories.index') }}" class="nav-link {{ Request::is('incomeCategories*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Income Categories</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('bankAccounts.index') }}" class="nav-link {{ Request::is('bankAccounts*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Bank Accounts</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('jobPositions.index') }}" class="nav-link {{ Request::is('jobPositions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Job Positions</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('leaveTypes.index') }}" class="nav-link {{ Request::is('leaveTypes*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Leave Types</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('smsTemplates.index') }}" class="nav-link {{ Request::is('smsTemplates*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Sms Templates</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('emailTemplates.index') }}" class="nav-link {{ Request::is('emailTemplates*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Email Templates</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('userRoles.index') }}" class="nav-link {{ Request::is('userRoles*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>User Roles</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('rolePermissions.index') }}" class="nav-link {{ Request::is('rolePermissions*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Role Permissions</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('studentParentRelationships.index') }}" class="nav-link {{ Request::is('studentParentRelationships*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Student Parent Relationships</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('studentDocuments.index') }}" class="nav-link {{ Request::is('studentDocuments*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Student Documents</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('staffDocuments.index') }}" class="nav-link {{ Request::is('staffDocuments*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Staff Documents</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('classSections.index') }}" class="nav-link {{ Request::is('classSections*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Class Sections</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('classSubjects.index') }}" class="nav-link {{ Request::is('classSubjects*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Class Subjects</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('teacherSubjects.index') }}" class="nav-link {{ Request::is('teacherSubjects*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Teacher Subjects</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('exams.index') }}" class="nav-link {{ Request::is('exams*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Exams</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('examSchedules.index') }}" class="nav-link {{ Request::is('examSchedules*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Exam Schedules</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('examResults.index') }}" class="nav-link {{ Request::is('examResults*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Exam Results</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('feeStructures.index') }}" class="nav-link {{ Request::is('feeStructures*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Fee Structures</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('studentFeeDiscounts.index') }}" class="nav-link {{ Request::is('studentFeeDiscounts*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Student Fee Discounts</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('books.index') }}" class="nav-link {{ Request::is('books*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Books</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('inventoryItems.index') }}" class="nav-link {{ Request::is('inventoryItems*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Inventory Items</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('routes.index') }}" class="nav-link {{ Request::is('routes*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Routes</p>
    </a>
</li>

<li class="nav-item">
    <a href="{{ route('routeStops.index') }}" class="nav-link {{ Request::is('routeStops*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-home"></i>
        <p>Route Stops</p>
    </a>
</li>
