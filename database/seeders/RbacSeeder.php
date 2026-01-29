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
    public function run(): void
    {
        $admin = Role::firstOrCreate(['role_name' => 'Super Admin'], ['description' => 'Super Administrator with full access']);
        $roles = [
            ['role_name' => 'Admin', 'description' => 'Administrator'],
            ['role_name' => 'Teacher', 'description' => 'Teacher role'],
            ['role_name' => 'Accountant', 'description' => 'Accountant role'],
            ['role_name' => 'Parent', 'description' => 'Parent role'],
            ['role_name' => 'Student', 'description' => 'Student role'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['role_name' => $r['role_name']], ['description' => $r['description']]);
        }

        $superAdmin = Role::where('role_name', 'Super Admin')->first();

        $permissions = Permission::pluck('permission_id')->all();
        foreach ($permissions as $pid) {
            RolePermission::firstOrCreate(['role_id' => $superAdmin->role_id, 'permission_id' => $pid]);
        }

        $user = User::first();
        if ($user) {
            UserRole::firstOrCreate(['user_id' => $user->id, 'role_id' => $superAdmin->role_id]);
        }
    }
}