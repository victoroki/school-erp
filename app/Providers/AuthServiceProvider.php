<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return true; // Bypass all authorization checks for demo
        });

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                foreach (\App\Models\Permission::pluck('permission_name') as $permission) {
                    \Illuminate\Support\Facades\Gate::define($permission, function ($user) use ($permission) {
                        return $user->hasPermission($permission);
                    });
                }
            }
        } catch (\Exception $e) {
            //
        }
    }
}
