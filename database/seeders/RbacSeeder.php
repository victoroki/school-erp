<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;
use App\Models\User;
use App\Models\UserRole;

class RbacSeeder extends Seeder
{
    /**
     * Role -> permission name mappings per the Phase 1 design matrix.
     *
     * Super Admin: all 43 permissions
     * Admin:       all except users.*/roles.*/permissions.* (37 permissions)
     * Teacher:     academics.view, academics.manage, exams.view, exams.manage (4 permissions)
     * Accountant:  finance.* + fees.* (8 permissions)
     * Parent:      0 (portal, ownership-scoped via Policy)
     * Student:     0 (portal, ownership-scoped via Policy)
     */
    private array $rolePermissions = [
        'Super Admin' => [
            'users.view', 'users.manage',
            'roles.view', 'roles.manage',
            'permissions.view', 'permissions.manage',
            'students.view', 'students.manage', 'students.import', 'students.export',
            'academics.view', 'academics.manage',
            'exams.view', 'exams.manage', 'exams.approve', 'exams.import', 'exams.export',
            'fees.view', 'fees.manage', 'fees.approve', 'fees.collect', 'fees.print',
            'finance.view', 'finance.manage', 'finance.approve',
            'hr.view', 'hr.manage', 'hr.approve',
            'inventory.view', 'inventory.manage', 'inventory.approve',
            'library.view', 'library.manage',
            'hostel.view', 'hostel.manage',
            'transport.view', 'transport.manage',
            'communication.view', 'communication.manage',
            'discipline.view', 'discipline.manage',
            'parents.view', 'parents.manage',
        ],
        'Admin' => [
            'students.view', 'students.manage', 'students.import', 'students.export',
            'academics.view', 'academics.manage',
            'exams.view', 'exams.manage', 'exams.approve', 'exams.import', 'exams.export',
            'fees.view', 'fees.manage', 'fees.approve', 'fees.collect', 'fees.print',
            'finance.view', 'finance.manage', 'finance.approve',
            'hr.view', 'hr.manage', 'hr.approve',
            'inventory.view', 'inventory.manage', 'inventory.approve',
            'library.view', 'library.manage',
            'hostel.view', 'hostel.manage',
            'transport.view', 'transport.manage',
            'communication.view', 'communication.manage',
            'discipline.view', 'discipline.manage',
            'parents.view', 'parents.manage',
        ],
        'Teacher' => [
            'academics.view',
            'academics.manage',
            'exams.view',
            'exams.manage',
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
            ['role_name' => 'Super Admin', 'description' => 'System Super Administrator with all permissions'],
            ['role_name' => 'Admin',       'description' => 'Administrator with full access except user/role/permission management'],
            ['role_name' => 'Teacher',     'description' => 'Teacher with scoped academic and exam permissions'],
            ['role_name' => 'Accountant',  'description' => 'Accountant with finance and fees permissions'],
            ['role_name' => 'Parent',      'description' => 'Parent portal user (ownership-scoped via Policy)'],
            ['role_name' => 'Student',     'description' => 'Student portal user (ownership-scoped via Policy)'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['role_name' => $role['role_name']],
                ['description' => $role['description']]
            );
        }

        // 2. Assign permissions to each role
        foreach ($this->rolePermissions as $roleName => $permissionNames) {
            $role = Role::where('role_name', $roleName)->first();
            if (!$role) {
                continue;
            }

            if (empty($permissionNames)) {
                continue;
            }

            $permissionIds = Permission::whereIn('permission_name', $permissionNames)
                ->pluck('permission_id')
                ->all();

            foreach ($permissionIds as $permissionId) {
                RolePermission::firstOrCreate([
                    'role_id'       => $role->role_id,
                    'permission_id' => $permissionId,
                ]);
            }
        }

        // 3. Assign Super Admin role to the first user (existing behavior)
        $superAdminRole = Role::where('role_name', 'Super Admin')->first();
        if ($superAdminRole) {
            $firstUser = User::first();
            if ($firstUser) {
                UserRole::firstOrCreate([
                    'user_id'  => $firstUser->id,
                    'role_id'  => $superAdminRole->role_id,
                ]);
            }
        }
    }
}
