<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Super Roles
    |--------------------------------------------------------------------------
    |
    | The ERP has a single, explicit answer to "who outranks everybody?".
    | A user holding any role listed here is a super user: `User::hasPermission()`
    | returns true for every permission slug, and the `Gate::before` hook in
    | App\Providers\AuthServiceProvider short-circuits every ability — so a
    | policy that lists only ['Super Admin', 'Admin', 'Teacher'] still admits
    | the platform owner without that policy having to know about it.
    |
    | This replaces the previous state, where access was spread across a
    | half-dozen policies that quietly omitted the Owner role. The symptom was
    | that the Owner was refused by /medical-incidents, homework and student
    | notices while Super Admin and Admin sailed through.
    |
    | Adding a role here is a deliberate, security-relevant decision. Do not add
    | an operational role (Teacher, Accountant, Parent, Student) to this list.
    |
    */

    'super_roles' => [
        'Owner',
    ],

    /*
    |--------------------------------------------------------------------------
    | Implicit Grants
    |--------------------------------------------------------------------------
    |
    | Permissions a role receives even when it is absent from its explicit list
    | in Database\Seeders\RbacSeeder. Kept empty by default: a role's grants
    | should be readable in one place, the seeder.
    |
    */

    'implicit_grants' => [
        //
    ],

];
