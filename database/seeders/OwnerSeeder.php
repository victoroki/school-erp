<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\User;

class OwnerSeeder extends Seeder
{
    /**
     * Seed the SaaS provider's Owner account.
     *
     * The Owner role is exclusive to the platform developer: it is the only
     * key to the Administration module (paid module activation, audit trail,
     * system error logs) and is never handed out by the /setup flow. This
     * seeder runs on every deployment, so each new school already has the
     * owner account in place before the school administrator ever signs up.
     *
     * Credentials come from the environment (see config/saas.php):
     *   SAAS_OWNER_EMAIL    — required to create the account
     *   SAAS_OWNER_PASSWORD — required only when creating; existing accounts
     *                         keep their current password
     */
    public function run(): void
    {
        $ownerRole = Role::updateOrCreate(
            ['role_name' => 'Owner'],
            [
                'description' => 'Platform owner (SaaS provider): sole access to modules, audit trail and system logs',
                'is_protected' => true,
                'is_hidden' => true,
            ]
        );

        $email = trim((string) config('saas.owner.email'));

        if ($email === '') {
            $this->command?->warn(
                'OwnerSeeder skipped: SAAS_OWNER_EMAIL is not set — no platform owner account was created.'
            );

            return;
        }

        $owner = User::firstOrNew(['email' => $email]);
        $isNew = ! $owner->exists;

        if ($isNew) {
            $password = (string) config('saas.owner.password');

            if ($password === '') {
                $this->command?->warn(
                    "OwnerSeeder skipped: SAAS_OWNER_PASSWORD is not set — cannot create the owner account [{$email}] without an initial password."
                );

                return;
            }

            $owner->name = config('saas.owner.name', 'Platform Owner');
            $owner->password = Hash::make($password);
        }

        $owner->user_type = 'admin';
        $owner->is_active = true;
        $owner->is_protected = true;
        $owner->is_hidden = true;
        $owner->email_verified_at = $owner->email_verified_at ?? now();
        $owner->save();

        $owner->roles()->syncWithoutDetaching([$ownerRole->role_id]);
    }
}
