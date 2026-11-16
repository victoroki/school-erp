<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }

    /**
     * Handle an incoming request.
     * After authentication succeeds, eager-load roles.permissions to
     * eliminate N+1 queries in permission checks.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $response = parent::handle($request, $next, ...$guards);

        // After auth succeeds, load permissions if user exists and not yet loaded
        $user = $request->user();
        if ($user && !$user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        }

        return $response;
    }
}
