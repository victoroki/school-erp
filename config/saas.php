<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Platform Owner (SaaS Provider) Account
    |--------------------------------------------------------------------------
    |
    | The Owner role is reserved for you — the developer selling this SaaS.
    | It is the ONLY role that can open the Administration module: activate
    | paid modules, review the audit trail and read system error logs. School
    | administrators (Super Admin) can never see or reach that module.
    |
    | On every fresh deployment (`php artisan db:seed`) the account below is
    | created automatically with the Owner role, hidden from school user
    | listings and protected from edits by school staff. Set these in the
    | .env of each deployment:
    |
    |   SAAS_OWNER_EMAIL     — your login email (required to create the account)
    |   SAAS_OWNER_PASSWORD  — initial password (required only when creating;
    |                          existing accounts keep their current password)
    |   SAAS_OWNER_NAME      — display name (optional)
    |
    */

    'owner' => [
        'email'    => env('SAAS_OWNER_EMAIL', ''),
        'password' => env('SAAS_OWNER_PASSWORD', ''),
        'name'     => env('SAAS_OWNER_NAME', 'Platform Owner'),
    ],
];
