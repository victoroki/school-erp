<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Eager-loads roles.permissions on the authenticated user once per request.
 * This eliminates the N+1 query problem in User::hasPermission() —
 * roles and their permissions are loaded into memory in 2 queries total,
 * regardless of how many permission checks occur during the request.
 */
class LoadPermissions
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && !$user->relationLoaded('roles')) {
            $user->load('roles.permissions');
        }

        return $next($request);
    }
}
