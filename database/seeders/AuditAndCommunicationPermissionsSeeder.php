<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\Role;

class AuditAndCommunicationPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'audit-trail.index' => 'View system audit trails',
            'communication.view' => 'View communication module pages',
            'communication.manage' => 'Manage communication providers, templates and triggers',
            'communication.dashboard' => 'View communication dashboard',
            'communication.compose' => 'Compose and send messages',
            'communication.send' => 'Execute sending of messages',
            'communication.history.index' => 'View message history',
            'communication.history.show' => 'View specific message details',
        ];

        $permissionIds = [];
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['permission_name' => $name],
                ['description' => $description]
            );
            $permissionIds[] = $permission->permission_id;
        }

        // Assign to Owner, Admin and Super Admin roles
        $roles = Role::whereIn('role_name', ['Owner', 'Admin', 'Super Admin'])->get();
        foreach ($roles as $role) {
            $role->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
