<?php

namespace App\Services;

use App\Models\BookIssue;
use App\Models\Exam;
use App\Models\FeePayment;
use App\Models\HostelAllocation;
use App\Models\InventoryItem;
use App\Models\LeaveApplication;
use App\Models\Requisition;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use App\Models\Vehicle;

/**
 * Source of truth for the dashboard module tiles and alert banners.
 *
 * The dynamic (callable) parts live here rather than in a config file so the
 * application stays compatible with `config:cache` — closures cannot be
 * serialized and would break `php artisan optimize`.
 */
class DashboardWidgetService
{
    /**
     * Module tiles. Each `summary` callable returns
     * ['count' => ..., 'label' => ..., 'status' => 'good|warning|danger|info'].
     *
     * The `module` key names the module registry entry (modules table) that
     * gates the tile — a tile is hidden when its module is disabled in
     * Administration → Modules, mirroring the sidebar's module gating.
     */
    public function modules(): array
    {
        return [
            'academic' => [
                'key' => 'academic',
                'module' => 'academics',
                'label' => 'Academic',
                'icon' => 'fa-graduation-cap',
                'color' => 'ic-blue',
                'permission' => ['academics.view', 'academics.manage'],
                'route' => 'academic-dashboard.index',
                'summary' => fn () => [
                    'count' => SchoolClass::count(),
                    'label' => 'Classes Active',
                    'status' => SchoolClass::count() > 0 ? 'good' : 'warning',
                ],
            ],

            'students' => [
                'key' => 'students',
                'module' => 'students',
                'label' => 'Students',
                'icon' => 'fa-user-graduate',
                'color' => 'ic-blue',
                'permission' => ['students.view', 'students.manage'],
                'route' => 'students.index',
                'summary' => fn () => [
                    'count' => Student::where('is_active', true)->count(),
                    'label' => 'Active Students',
                    'status' => Student::where('is_active', true)->count() > 0 ? 'good' : 'danger',
                ],
            ],

            'exams' => [
                'key' => 'exams',
                'module' => 'exams',
                'label' => 'Exams',
                'icon' => 'fa-file-alt',
                'color' => 'ic-red',
                'permission' => ['exams.view', 'exams.manage'],
                'route' => 'exam-dashboard.index',
                'summary' => fn () => [
                    'count' => Exam::where('end_date', '>=', now()->toDateString())->count(),
                    'label' => 'Active Exams',
                    'status' => Exam::where('end_date', '>=', now()->toDateString())->count() > 0 ? 'good' : 'info',
                ],
            ],

            'inventory' => [
                'key' => 'inventory',
                'module' => 'inventory',
                'label' => 'Inventory',
                'icon' => 'fa-boxes',
                'color' => 'ic-green',
                'permission' => ['inventory.view', 'inventory.manage'],
                'route' => 'inventory.dashboard',
                'summary' => fn () => [
                    'count' => Requisition::where('status', 'Pending')->count(),
                    'label' => 'Pending Reqs',
                    'status' => InventoryItem::whereRaw('quantity <= minimum_quantity')->count() > 0 ? 'warning' : 'good',
                ],
            ],

            'library' => [
                'key' => 'library',
                'module' => 'library',
                'label' => 'Library',
                'icon' => 'fa-book',
                'color' => 'ic-blue',
                'permission' => ['library.view', 'library.manage'],
                'route' => 'library.dashboard',
                'summary' => fn () => [
                    'count' => BookIssue::whereNull('return_date')->where('due_date', '<', now())->count(),
                    'label' => 'Overdue Books',
                    'status' => 'good',
                ],
            ],

            'hr' => [
                'key' => 'hr',
                'module' => 'hr',
                'label' => 'HR',
                'icon' => 'fa-users-cog',
                'color' => 'ic-purple',
                'permission' => ['hr.view', 'hr.manage'],
                'route' => 'hr.dashboard',
                'summary' => fn () => [
                    'count' => User::whereHas('roles')->count(),
                    'label' => 'Total Staff',
                    'status' => LeaveApplication::where('application_status', 'pending')->count() > 0 ? 'warning' : 'good',
                ],
            ],

            'fees' => [
                'key' => 'fees',
                'module' => 'fees',
                'label' => 'Fees',
                'icon' => 'fa-money-check-alt',
                'color' => 'ic-green',
                'permission' => ['fees.view', 'fees.manage', 'fees.collect'],
                'route' => 'fee-management.index',
                'summary' => fn () => [
                    'count' => number_format(FeePayment::whereMonth('created_at', now()->month)->sum('amount') / 1000, 1) . 'k',
                    'label' => 'Monthly Rev',
                    'status' => StudentFeeAssignment::where('status', 'active')
                        ->whereRaw('COALESCE(paid_amount, 0) < final_amount')->count() > 10 ? 'danger' : 'good',
                ],
            ],

            'hostel' => [
                'key' => 'hostel',
                'module' => 'hostel',
                'label' => 'Hostel',
                'icon' => 'fa-hotel',
                'color' => 'ic-yellow',
                'permission' => ['hostel.view', 'hostel.manage'],
                'route' => 'hostel.dashboard',
                'summary' => fn () => [
                    'count' => HostelAllocation::where('status', 'active')->count(),
                    'label' => 'Occupants',
                    'status' => 'good',
                ],
            ],

            'transport' => [
                'key' => 'transport',
                'module' => 'transport',
                'label' => 'Transport',
                'icon' => 'fa-bus',
                'color' => 'ic-red',
                'permission' => ['transport.view', 'transport.manage'],
                'route' => 'transportation.dashboard',
                'summary' => fn () => [
                    'count' => Vehicle::where('status', 'active')->count(),
                    'label' => 'Active Vehicles',
                    'status' => 'good',
                ],
            ],
        ];
    }

    /**
     * Alert banners. Each fires only when its permission is satisfied AND the
     * data condition is true.
     */
    public function alerts(): array
    {
        return [
            [
                'key' => 'pending-fees',
                'type' => 'danger',
                'module' => 'fees',
                'permission' => ['fees.view', 'fees.collect'],
                'icon' => 'fa-file-invoice-dollar',
                'title' => fn ($counts) => number_format($counts['pendingFeeCount']) . ' Outstanding Fee Accounts',
                'desc' => fn ($counts) => 'KES ' . number_format($counts['pendingFeeAmount'], 2) . ' pending collection',
                'action' => 'Collect',
                'actionRoute' => 'fee-management.index',
                'visible' => fn ($counts) => $counts['pendingFeeCount'] > 0,
            ],

            [
                'key' => 'low-stock',
                'type' => 'warning',
                'module' => 'inventory',
                'permission' => ['inventory.view', 'inventory.manage'],
                'icon' => 'fa-boxes',
                'title' => fn ($counts) => $counts['lowStockCount'] . ' Low Stock Items',
                'desc' => fn ($counts) => 'Inventory levels below minimum threshold',
                'action' => 'Check',
                'actionRoute' => 'inventory-items.index',
                'visible' => fn ($counts) => $counts['lowStockCount'] > 0,
            ],

            [
                'key' => 'pending-requisitions',
                'type' => 'info',
                'module' => 'inventory',
                'permission' => ['inventory.view', 'inventory.manage'],
                'icon' => 'fa-clipboard-list',
                'title' => fn ($counts) => $counts['pendingRequisitionsCount'] . ' Pending Requisitions',
                'desc' => fn ($counts) => 'Awaiting administrative approval',
                'action' => 'Review',
                'actionRoute' => 'inventory.requisitions.index',
                'visible' => fn ($counts) => $counts['pendingRequisitionsCount'] > 0,
            ],

            [
                'key' => 'pending-leave',
                'type' => 'warning',
                'module' => 'hr',
                'permission' => ['hr.view', 'hr.approve'],
                'icon' => 'fa-user-clock',
                'title' => fn ($counts) => $counts['pendingLeaveCount'] . ' Pending Leave Applications',
                'desc' => fn ($counts) => 'Staff leave requests awaiting approval',
                'action' => 'Review',
                'actionRoute' => 'leave-applications.index',
                'visible' => fn ($counts) => $counts['pendingLeaveCount'] > 0,
            ],
        ];
    }
}
