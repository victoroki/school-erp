<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure Super Admin role exists (RbacSeeder creates it, but guard against
        // running this seeder standalone)
        $superAdminRole = Role::firstOrCreate([
            'role_name' => 'Super Admin',
        ], [
            'description' => 'System Super Administrator with all permissions',
        ]);

        // Create the default Super Admin user
        $superAdmin = User::firstOrCreate([
            'email' => 'superadmin@school.com',
        ], [
            'name' => 'Super Admin',
            'password' => Hash::make('SuperAdmin@2025'),
        ]);

        // Assign Super Admin role to this user
        UserRole::firstOrCreate([
            'user_id' => $superAdmin->id,
            'role_id' => $superAdminRole->role_id,
        ]);

        // Also assign Admin role if it exists (so this user has both)
        $adminRole = Role::where('role_name', 'Admin')->first();
        if ($adminRole) {
            UserRole::firstOrCreate([
                'user_id' => $superAdmin->id,
                'role_id' => $adminRole->role_id,
            ]);
        }
    }
}
