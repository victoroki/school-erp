<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use App\Models\Permission;
use App\Models\RolePermission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin Role
        $superAdminRole = Role::firstOrCreate([
            'role_name' => 'Super Admin',
        ], [
            'description' => 'System Super Administrator with all permissions',
        ]);

        // Create Super Admin User
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@school.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('SuperAdmin@2025'),
        ]);

        // Assign Super Admin Role to User
        UserRole::firstOrCreate([
            'user_id' => $superAdmin->id,
            'role_id' => $superAdminRole->role_id,
        ]);

        // Assign ALL permissions to Super Admin Role
        $allPermissions = Permission::pluck('permission_id')->all();
        foreach ($allPermissions as $permissionId) {
            RolePermission::firstOrCreate([
                'role_id' => $superAdminRole->role_id,
                'permission_id' => $permissionId,
            ]);
        }

        // Also assign existing admin role if it exists
        $adminRole = Role::where('role_name', 'Admin')->first();
        if ($adminRole) {
            UserRole::firstOrCreate([
                'user_id' => $superAdmin->id,
                'role_id' => $adminRole->role_id,
            ]);
        }
    }
}