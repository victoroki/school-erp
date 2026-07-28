<?php

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\FeePayment;
use App\Models\StudentFeeAssignment;
use App\Models\LeaveApplication;
use App\Models\InventoryItem;
use App\Models\Requisition;
use App\Models\BookIssue;
use App\Models\HostelAllocation;
use App\Models\Vehicle;
use App\Models\Exam;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Dashboard Widget Configuration
|--------------------------------------------------------------------------
|
| Each widget defines:
|   key       — unique identifier
|   label     — display name
|   icon      — FontAwesome class
|   color     — CSS color class (ic-*)
|   permission — required permission(s) — string or array (OR logic)
|   route     — link destination
|   summary   — callable returning ['count' => ..., 'label' => ..., 'status' => 'good|warning|danger']
|   alert     — optional, callable returning null or ['type' => 'danger|warning|info', 'title' => ..., 'desc' => ..., 'action' => ..., 'actionRoute' => ...]
|
| Widget is visible if user has ANY of the listed permissions.
| Super Admin sees everything.
|
*/

return [

    'modules' => [

        'academic' => [
            'key'       => 'academic',
            'label'     => 'Academic',
            'icon'      => 'fa-graduation-cap',
            'color'     => 'ic-blue',
            'permission' => ['academics.view', 'academics.manage'],
            'route'     => 'academic-dashboard.index',
            'summary'   => fn() => [
                'count' => SchoolClass::count(),
                'label' => 'Classes Active',
                'status' => SchoolClass::count() > 0 ? 'good' : 'warning',
            ],
        ],

        'students' => [
            'key'       => 'students',
            'label'     => 'Students',
            'icon'      => 'fa-user-graduate',
            'color'     => 'ic-blue',
            'permission' => ['students.view', 'students.manage'],
            'route'     => 'students.index',
            'summary'   => fn() => [
                'count' => Student::where('is_active', true)->count(),
                'label' => 'Active Students',
                'status' => Student::where('is_active', true)->count() > 0 ? 'good' : 'danger',
            ],
        ],

        'exams' => [
            'key'       => 'exams',
            'label'     => 'Exams',
            'icon'      => 'fa-file-alt',
            'color'     => 'ic-red',
            'permission' => ['exams.view', 'exams.manage'],
            'route'     => 'exam-dashboard.index',
            'summary'   => fn() => [
                'count' => Exam::where('end_date', '>=', now()->toDateString())->count(),
                'label' => 'Active Exams',
                'status' => Exam::where('end_date', '>=', now()->toDateString())->count() > 0 ? 'good' : 'info',
            ],
        ],

        'inventory' => [
            'key'       => 'inventory',
            'label'     => 'Inventory',
            'icon'      => 'fa-boxes',
            'color'     => 'ic-green',
            'permission' => ['inventory.view', 'inventory.manage'],
            'route'     => 'inventory.dashboard',
            'summary'   => fn() => [
                'count' => Requisition::where('status', 'Pending')->count(),
                'label' => 'Pending Reqs',
                'status' => InventoryItem::whereRaw('quantity <= minimum_quantity')->count() > 0 ? 'warning' : 'good',
            ],
        ],

        'library' => [
            'key'       => 'library',
            'label'     => 'Library',
            'icon'      => 'fa-book',
            'color'     => 'ic-blue',
            'permission' => ['library.view', 'library.manage'],
            'route'     => 'library.dashboard',
            'summary'   => fn() => [
                'count' => BookIssue::whereNull('return_date')->where('due_date', '<', now())->count(),
                'label' => 'Overdue Books',
                'status' => 'good',
            ],
        ],

        'hr' => [
            'key'       => 'hr',
            'label'     => 'HR',
            'icon'      => 'fa-users-cog',
            'color'     => 'ic-purple',
            'permission' => ['hr.view', 'hr.manage'],
            'route'     => 'hr.dashboard',
            'summary'   => fn() => [
                'count' => User::whereHas('roles')->count(),
                'label' => 'Total Staff',
                'status' => LeaveApplication::where('application_status', 'pending')->count() > 0 ? 'warning' : 'good',
            ],
        ],

        'fees' => [
            'key'       => 'fees',
            'label'     => 'Fees',
            'icon'      => 'fa-money-check-alt',
            'color'     => 'ic-green',
            'permission' => ['fees.view', 'fees.manage', 'fees.collect'],
            'route'     => 'fee-management.index',
            'summary'   => fn() => [
                'count' => number_format(FeePayment::whereMonth('created_at', now()->month)->sum('amount') / 1000, 1) . 'k',
                'label' => 'Monthly Rev',
                'status' => StudentFeeAssignment::where('status', 'active')
                    ->whereRaw('COALESCE(paid_amount, 0) < final_amount')->count() > 10 ? 'danger' : 'good',
            ],
        ],

        'hostel' => [
            'key'       => 'hostel',
            'label'     => 'Hostel',
            'icon'      => 'fa-hotel',
            'color'     => 'ic-yellow',
            'permission' => ['hostel.view', 'hostel.manage'],
            'route'     => 'hostel.dashboard',
            'summary'   => fn() => [
                'count' => HostelAllocation::where('status', 'active')->count(),
                'label' => 'Occupants',
                'status' => 'good',
            ],
        ],

        'transport' => [
            'key'       => 'transport',
            'label'     => 'Transport',
            'icon'      => 'fa-bus',
            'color'     => 'ic-red',
            'permission' => ['transport.view', 'transport.manage'],
            'route'     => 'transportation.dashboard',
            'summary'   => fn() => [
                'count' => Vehicle::where('status', 'active')->count(),
                'label' => 'Active Vehicles',
                'status' => 'good',
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Banners
    |--------------------------------------------------------------------------
    |
    | Each alert fires only when its permission is satisfied AND the data
    | condition is true.
    |
    */
    'alerts' => [

        [
            'key'        => 'pending-fees',
            'type'       => 'danger',
            'permission' => ['fees.view', 'fees.collect'],
            'icon'       => 'fa-file-invoice-dollar',
            'title'      => fn($counts) => number_format($counts['pendingFeeCount']) . ' Outstanding Fee Accounts',
            'desc'       => fn($counts) => 'KES ' . number_format($counts['pendingFeeAmount'], 2) . ' pending collection',
            'action'     => 'Collect',
            'actionRoute' => 'fee-management.index',
            'visible'    => fn($counts) => $counts['pendingFeeCount'] > 0,
        ],

        [
            'key'        => 'low-stock',
            'type'       => 'warning',
            'permission' => ['inventory.view', 'inventory.manage'],
            'icon'       => 'fa-boxes',
            'title'      => fn($counts) => $counts['lowStockCount'] . ' Low Stock Items',
            'desc'       => fn($counts) => 'Inventory levels below minimum threshold',
            'action'     => 'Check',
            'actionRoute' => 'inventory-items.index',
            'visible'    => fn($counts) => $counts['lowStockCount'] > 0,
        ],

        [
            'key'        => 'pending-requisitions',
            'type'       => 'info',
            'permission' => ['inventory.view', 'inventory.manage'],
            'icon'       => 'fa-clipboard-list',
            'title'      => fn($counts) => $counts['pendingRequisitionsCount'] . ' Pending Requisitions',
            'desc'       => fn($counts) => 'Awaiting administrative approval',
            'action'     => 'Review',
            'actionRoute' => 'inventory.requisitions.index',
            'visible'    => fn($counts) => $counts['pendingRequisitionsCount'] > 0,
        ],

        [
            'key'        => 'pending-leave',
            'type'       => 'warning',
            'permission' => ['hr.view', 'hr.approve'],
            'icon'       => 'fa-user-clock',
            'title'      => fn($counts) => $counts['pendingLeaveCount'] . ' Pending Leave Applications',
            'desc'       => fn($counts) => 'Staff leave requests awaiting approval',
            'action'     => 'Review',
            'actionRoute' => 'leave-applications.index',
            'visible'    => fn($counts) => $counts['pendingLeaveCount'] > 0,
        ],

    ],

];
