<?php

namespace App\Policies;

use App\Models\User;

class InventoryPolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Accountant']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('inventory.manage');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('inventory.approve');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('inventory.import');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('inventory.export');
    }
}
