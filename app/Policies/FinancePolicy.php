<?php

namespace App\Policies;

use App\Models\User;

/**
 * Finance authorisation.
 *
 * Every ability names a permission that actually exists. Two of them used to
 * name permissions that were never created — `finance.export` and
 * `finance.import` — so those abilities could only ever return false, and the
 * menu entries that advertised them could never be shown to anyone.
 *
 * `view()` also used to test a hard-coded role list instead of the permission,
 * which meant a custom role granted `finance.view` directly was refused by the
 * policy while every controller's `can:finance.view` middleware let it through.
 * The permission is the single rule.
 */
class FinancePolicy
{
    public function view(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }

    public function manage(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    public function approve(User $user): bool
    {
        return $user->hasPermission('finance.approve');
    }

    /**
     * Importing financial data is a management action — the same permission as
     * every other way of creating or changing it.
     */
    public function import(User $user): bool
    {
        return $user->hasPermission('finance.manage');
    }

    /**
     * Exporting financial data is reading it. There is no separate finance
     * export permission, and inventing one would create a permission nothing
     * else in the module checks.
     */
    public function export(User $user): bool
    {
        return $user->hasPermission('finance.view');
    }
}
