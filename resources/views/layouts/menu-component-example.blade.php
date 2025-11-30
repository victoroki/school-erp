<!-- Dashboard -->
<x-sidebar-item 
    icon="fas fa-tachometer-alt text-primary" 
    text="Dashboard" 
    route="{{ route('home') }}" 
    :isActive="Request::is('home')" />

<!-- User Management -->
<x-sidebar-item 
    icon="fas fa-users-cog text-success" 
    text="User Management" 
    :hasSubmenu="true"
    :isOpen="Request::is('roles*') || Request::is('permissions*') || Request::is('user-roles*') || Request::is('role-permissions*')"
    :isActive="Request::is('roles*') || Request::is('permissions*') || Request::is('user-roles*') || Request::is('role-permissions*')"
    :submenuItems="[
        ['icon' => 'far fa-circle text-success', 'text' => 'Roles', 'route' => route('roles.index'), 'isActive' => Request::is('roles*')],
        ['icon' => 'far fa-circle text-success', 'text' => 'Permissions', 'route' => route('permissions.index'), 'isActive' => Request::is('permissions*')],
        ['icon' => 'far fa-circle text-success', 'text' => 'User Roles', 'route' => route('user-roles.index'), 'isActive' => Request::is('user-roles*')],
        ['icon' => 'far fa-circle text-success', 'text' => 'Role Permissions', 'route' => route('role-permissions.index'), 'isActive' => Request::is('role-permissions*')]
    ]" />

<!-- Academic Management -->
<x-sidebar-item 
    icon="fas fa-graduation-cap text-info" 
    text="Academic Management" 
    :hasSubmenu="true"
    :isOpen="Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*')"
    :isActive="Request::is('academic-years*') || Request::is('school-classes*') || Request::is('sections*') || Request::is('class-sections*') || Request::is('subjects*') || Request::is('class-subjects*') || Request::is('teacher-subjects*') || Request::is('periods*') || Request::is('classrooms*')"
    :submenuItems="[
        ['icon' => 'far fa-calendar text-info', 'text' => 'Academic Years', 'route' => route('academic-years.index'), 'isActive' => Request::is('academic-years*')],
        ['icon' => 'far fa-chalkboard text-info', 'text' => 'School Classes', 'route' => route('school-classes.index'), 'isActive' => Request::is('school-classes*')],
        ['icon' => 'far fa-layer-group text-info', 'text' => 'Sections', 'route' => route('sections.index'), 'isActive' => Request::is('sections*')],
        ['icon' => 'far fa-sitemap text-info', 'text' => 'Class Sections', 'route' => route('class-sections.index'), 'isActive' => Request::is('class-sections*')],
        ['icon' => 'far fa-book-open text-info', 'text' => 'Subjects', 'route' => route('subjects.index'), 'isActive' => Request::is('subjects*')],
        ['icon' => 'far fa-link text-info', 'text' => 'Class Subjects', 'route' => route('class-subjects.index'), 'isActive' => Request::is('class-subjects*')],
        ['icon' => 'far fa-user-tie text-info', 'text' => 'Teacher Subjects', 'route' => route('teacher-subjects.index'), 'isActive' => Request::is('teacher-subjects*')],
        ['icon' => 'far fa-clock text-info', 'text' => 'Periods', 'route' => route('periods.index'), 'isActive' => Request::is('periods*')],
        ['icon' => 'far fa-door-open text-info', 'text' => 'Classrooms', 'route' => route('classrooms.index'), 'isActive' => Request::is('classrooms*')],
        ['icon' => 'fas fa-home', 'text' => 'Timetables', 'route' => route('timetables.index'), 'isActive' => Request::is('timetables*')]
    ]" />