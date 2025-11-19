<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_dashboard',
            'view_roles','create_roles','edit_roles','delete_roles',
            'view_permissions','create_permissions','edit_permissions','delete_permissions',
            'view_user_roles','assign_user_roles','revoke_user_roles',
            'view_role_permissions','assign_role_permissions','revoke_role_permissions',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate([
                'permission_name' => $name,
            ], [
                'description' => '',
            ]);
        }
    }
}