<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Role;
use App\Models\UserRole;

class ListUsers extends Command
{
    protected $signature = 'list:users';
    protected $description = 'List all users with their roles';

    public function handle(): void
    {
        $users = User::with('userRoles.role')->get();
        
        $this->info('📋 All Users and Their Roles:');
        $this->info('========================================');
        
        foreach ($users as $user) {
            $roles = $user->userRoles->pluck('role.role_name')->all();
            $roleList = !empty($roles) ? implode(', ', $roles) : 'No roles assigned';
            
            $this->info('Name: ' . $user->name);
            $this->info('Email: ' . $user->email);
            $this->info('Roles: ' . $roleList);
            $this->info('----------------------------------------');
        }
    }
}