<?php

namespace App\Policies;

use App\Models\User;

class FinancePolicy
{
    public function view(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Accountant']);
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('finance.approve');
    }

    public function import(User $user): bool
    {
        return $user->hasPermission('finance.import');
    }

    public function export(User $user): bool
    {
        return $user->hasPermission('finance.export');
    }
}
