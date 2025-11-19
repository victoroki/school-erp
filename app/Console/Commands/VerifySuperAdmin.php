<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;

class VerifySuperAdmin extends Command
{
    protected $signature = 'verify:superadmin';
    protected $description = 'Verify super admin user and roles';

    public function handle(): void
    {
        $user = User::where('email', 'superadmin@school.com')->first();
        
        if (!$user) {
            $this->error('Super admin user not found!');
            return;
        }

        $this->info('✅ Super Admin User Found:');
        $this->info('Name: ' . $user->name);
        $this->info('Email: ' . $user->email);
        
        $roles = $user->userRoles ? $user->userRoles->pluck('role.role_name')->all() : [];
        $this->info('Roles: ' . implode(', ', $roles));
        
        $this->info('✅ Super admin created successfully!');
    }
}