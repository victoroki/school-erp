<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

class RbacSeeder extends Seeder
{
    /**
     * Role -> permission name mappings.
     *
     * Super Admin: all permissions (53)
     * Admin:       all except users.*, roles.*, permissions.*
     * Teacher:     5 scoped/basic permissions (academics.view + attendance + marks enter + schedule view + own results)
     * Accountant:  finance.* + fees.* (8 permissions)
     * Parent:      0 (portal, ownership-scoped via Policy)
     * Student:     0 (portal, ownership-scoped via Policy)
     */
    private array $rolePermissions = [
        'Owner' => [
            'users.view', 'users.manage',
            'roles.view', 'roles.manage',
            'permissions.view', 'permissions.manage',
            'students.view', 'students.manage', 'students.import', 'students.export',
            'academics.view', 'academics.settings.manage', 'academics.attendance.manage',
            'exams.marks.enter-own', 'exams.schedule.view', 'exams.results.view-own',
            'exams.publish', 'exams.grading.manage', 'exams.results.view-all',
            'exams.analysis.view', 'exams.report-cards.export', 'exams.approve', 'exams.import',
            'fees.view', 'fees.manage', 'fees.approve', 'fees.collect', 'fees.print',
            'finance.view', 'finance.manage', 'finance.approve',
            'hr.view', 'hr.manage', 'hr.approve',
            'inventory.view', 'inventory.manage', 'inventory.approve',
            'library.view', 'library.manage',
            'hostel.view', 'hostel.manage',
            'transport.view', 'transport.manage',
            'communication.view', 'communication.manage', 'communication.dashboard', 'communication.compose', 'communication.send', 'communication.history.index', 'communication.history.show',
            'discipline.view', 'discipline.manage',
            'parents.view', 'parents.manage',
        ],
        'Super Admin' => [
            'users.view', 'users.manage',
            'roles.view', 'roles.manage',
            'permissions.view', 'permissions.manage',
            'students.view', 'students.manage', 'students.import', 'students.export',
            'academics.view', 'academics.settings.manage', 'academics.attendance.manage',
            'exams.marks.enter-own', 'exams.schedule.view', 'exams.results.view-own',
            'exams.publish', 'exams.grading.manage', 'exams.results.view-all',
            'exams.analysis.view', 'exams.report-cards.export', 'exams.approve', 'exams.import',
            'fees.view', 'fees.manage', 'fees.approve', 'fees.collect', 'fees.print',
            'finance.view', 'finance.manage', 'finance.approve',
            'hr.view', 'hr.manage', 'hr.approve',
            'inventory.view', 'inventory.manage', 'inventory.approve',
            'library.view', 'library.manage',
            'hostel.view', 'hostel.manage',
            'transport.view', 'transport.manage',
            'communication.view', 'communication.manage', 'communication.dashboard', 'communication.compose', 'communication.send', 'communication.history.index', 'communication.history.show',
            'discipline.view', 'discipline.manage',
            'parents.view', 'parents.manage',
        ],
        'Admin' => [
            'students.view', 'students.manage', 'students.import', 'students.export',
            'academics.view', 'academics.settings.manage', 'academics.attendance.manage',
            'exams.marks.enter-own', 'exams.schedule.view', 'exams.results.view-own',
            'exams.publish', 'exams.grading.manage', 'exams.results.view-all',
            'exams.analysis.view', 'exams.report-cards.export', 'exams.approve', 'exams.import',
            'fees.view', 'fees.manage', 'fees.approve', 'fees.collect', 'fees.print',
            'finance.view', 'finance.manage', 'finance.approve',
            'hr.view', 'hr.manage', 'hr.approve',
            'inventory.view', 'inventory.manage', 'inventory.approve',
            'library.view', 'library.manage',
            'hostel.view', 'hostel.manage',
            'transport.view', 'transport.manage',
            'communication.view', 'communication.manage', 'communication.dashboard', 'communication.compose', 'communication.send', 'communication.history.index', 'communication.history.show',
            'discipline.view', 'discipline.manage',
            'parents.view', 'parents.manage',
        ],
        'Teacher' => [
            'academics.view',
            'academics.attendance.manage',
            'exams.marks.enter-own',
            'exams.schedule.view',
            'exams.results.view-own',
        ],
        'Accountant' => [
            'finance.view',
            'finance.manage',
            'finance.approve',
            'fees.view',
            'fees.manage',
            'fees.approve',
            'fees.collect',
            'fees.print',
        ],
        'Parent' => [],
        'Student' => [],
    ];

    public function run(): void
    {
        // 1. Create roles (idempotent)
        $roles = [
            ['role_name' => 'Owner',       'description' => 'Platform owner (SaaS provider): sole access to modules, audit trail and system logs', 'is_protected' => true, 'is_hidden' => true],
            ['role_name' => 'Super Admin', 'description' => 'School Super Administrator with all permissions (except the platform Administration module)', 'is_protected' => true],
            ['role_name' => 'Admin',       'description' => 'Administrator with full access except user/role/permission management'],
            ['role_name' => 'Teacher',     'description' => 'Teacher with scoped academic and exam permissions'],
            ['role_name' => 'Accountant',  'description' => 'Accountant with finance and fees permissions'],
            ['role_name' => 'Parent',      'description' => 'Parent portal user (ownership-scoped via Policy)'],
            ['role_name' => 'Student',     'description' => 'Student portal user (ownership-scoped via Policy)'],
        ];

        foreach ($roles as $role) {
            $existing = Role::where('role_name', $role['role_name'])->first();

            if ($existing) {
                $existing->update([
                    'is_protected' => $role['is_protected'] ?? false,
                    'is_hidden' => $role['is_hidden'] ?? false,
                ]);
            } else {
                Role::create([
                    'role_name' => $role['role_name'],
                    'description' => $role['description'],
                    'is_protected' => $role['is_protected'] ?? false,
                    'is_hidden' => $role['is_hidden'] ?? false,
                ]);
            }
        }

        // 2. Reconcile permissions for each role (removes old, adds new)
        foreach ($this->rolePermissions as $roleName => $permissionNames) {
            $role = Role::where('role_name', $roleName)->first();
            if (!$role) {
                continue;
            }

            $newIds = empty($permissionNames)
                ? []
                : Permission::whereIn('permission_name', $permissionNames)
                    ->pluck('permission_id')
                    ->all();

            $currentIds = RolePermission::where('role_id', $role->role_id)
                ->pluck('permission_id')
                ->all();

            $toRemove = array_diff($currentIds, $newIds);
            $toAdd    = array_diff($newIds, $currentIds);

            if (!empty($toRemove)) {
                RolePermission::where('role_id', $role->role_id)
                    ->whereIn('permission_id', $toRemove)
                    ->delete();
            }

            foreach ($toAdd as $permissionId) {
                RolePermission::create([
                    'role_id'       => $role->role_id,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
}
