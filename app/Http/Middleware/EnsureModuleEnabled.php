<?php

namespace App\Http\Middleware;

use App\Services\MenuService;
use App\Services\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Blocks requests to routes that belong to a disabled module.
 *
 * The module a route belongs to is inferred from the active URL patterns in
 * config/menu.php (the single source of truth the sidebar also uses). A
 * request is blocked with a 404 only when it matches at least one module and
 * none of the matching modules is active — so overlapping patterns across
 * modules can never take down a route that another enabled module owns.
 *
 * Runs on the authenticated route group BEFORE the per-controller `can:`
 * permission middleware, so it is a purely additive layer on top of RBAC.
 */
class EnsureModuleEnabled
{
    private const PATTERN_CACHE_KEY = 'modules.route_patterns';

    public function __construct(private ModuleManager $modules) {}

    public function handle(Request $request, Closure $next)
    {
        $activeKeys = $this->modules->activeKeys();

        // No modules registered yet (pre-seed / pre-migration) → behave as before.
        if (empty($activeKeys)) {
            return $next($request);
        }

        $path = $request->path();
        $matched = false;

        foreach ($this->routePatterns() as $moduleKey => $patterns) {
            foreach ($patterns as $pattern) {
                if (! MenuService::isActive($pattern)) {
                    continue;
                }

                $matched = true;

                if (in_array($moduleKey, $activeKeys, true)) {
                    return $next($request);
                }
            }
        }

        // The route belongs to at least one module and every matching module
        // is disabled — respond as if the route does not exist.
        if ($matched) {
            abort(404);
        }

        // Unmapped routes (auth, portal, setup, dashboard data) are not gated.
        return $next($request);
    }

    /**
     * Build a map of module key => active URL patterns from the sidebar config.
     * Children inherit the module of their parent unless the child declares its
     * own `module` key, in which case its patterns belong to that module (and
     * are excluded from the parent's set) so disabling the child's module
     * takes its routes down.
     */
    private function routePatterns(): array
    {
        return Cache::remember(self::PATTERN_CACHE_KEY, now()->addDay(), function () {
            $map = [];

            foreach (config('menu.sections', []) as $item) {
                if (isset($item['header']) || empty($item['key'])) {
                    continue;
                }

                $parentPatterns = (array) ($item['active'] ?? []);

                foreach ($item['children'] ?? [] as $child) {
                    if (isset($child['header'])) {
                        continue;
                    }

                    $childPatterns = (array) ($child['active'] ?? []);

                    if (! empty($child['module'])) {
                        $map[$child['module']] = array_merge($map[$child['module']] ?? [], $childPatterns);

                        continue;
                    }

                    $parentPatterns = array_merge($parentPatterns, $childPatterns);
                }

                $map[$item['key']] = array_values(array_unique(array_filter($parentPatterns)));
            }

            foreach ($map as $moduleKey => $patterns) {
                $map[$moduleKey] = array_values(array_unique(array_filter($patterns)));
            }

            return $map;
        });
    }
}
