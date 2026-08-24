<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MenuService
{
    protected array $config;

    protected ?User $user = null;

    public function __construct(private ModuleManager $modules)
    {
        $this->config = config('menu.sections', []);
    }

    /**
     * Get the filtered menu sections for the current user.
     * Returns only items the user has permission to see.
     *
     * Heading visibility is DERIVED from children, never hardcoded:
     *   - A top-level section header (OPERATIONS, GOVERNANCE, ...) renders
     *     only when at least one visible item follows it before the next header.
     *   - A sub-header inside a parent's children renders only when at least
     *     one visible child follows it before the next sub-header.
     *   - A parent item renders only when it has at least one visible
     *     non-header child.
     *
     * Module gating is additive: a top-level item is hidden when its module
     * key is disabled (children inherit the parent's module), then the
     * existing permission check runs exactly as before.
     */
    public function getVisibleMenu(): array
    {
        $this->user = Auth::user();
        if (! $this->user) {
            return [];
        }

        $visible = [];
        $pendingHeader = null;

        foreach ($this->config as $item) {
            // Buffer section headers — only flushed when a visible item follows.
            if (isset($item['header'])) {
                $pendingHeader = $item;

                continue;
            }

            // Hide the whole section when its module is disabled.
            if (! $this->moduleIsVisible($item['key'] ?? '')) {
                continue;
            }

            if (empty($item['children'])) {
                if (self::canSee($this->user, $item['permission'] ?? [], $item['owner_only'] ?? false)) {
                    if ($pendingHeader !== null) {
                        $visible[] = $pendingHeader;
                        $pendingHeader = null;
                    }
                    $visible[] = $item;
                }

                continue;
            }

            $visibleChildren = $this->filterChildren($item['children']);

            $nonHeaderChildren = array_filter($visibleChildren, fn ($c) => ! isset($c['header']));
            if (! empty($nonHeaderChildren)) {
                $item['children'] = $visibleChildren;
                if ($pendingHeader !== null) {
                    $visible[] = $pendingHeader;
                    $pendingHeader = null;
                }
                $visible[] = $item;
            }
        }

        return $visible;
    }

    /**
     * A menu item renders only when its module is active. Keys that are not
     * registered modules (no row in the modules table) are always visible.
     */
    protected function moduleIsVisible(string $key): bool
    {
        if ($key === '') {
            return true;
        }

        return $this->modules->isActive($key);
    }

    /**
     * Filter a parent's children by permission, keeping a sub-header only
     * when at least one visible child follows it (no orphaned headings).
     */
    protected function filterChildren(array $children): array
    {
        $visible = [];
        $pendingHeader = null;

        foreach ($children as $child) {
            if (isset($child['header'])) {
                $pendingHeader = $child;

                continue;
            }

            // A child can opt into its own module: it is hidden when that
            // module is disabled, independently of the parent's module.
            if (! empty($child['module']) && ! $this->moduleIsVisible($child['module'])) {
                continue;
            }

            if (self::canSee($this->user, $child['permission'] ?? [], $child['owner_only'] ?? false)) {
                if ($pendingHeader !== null) {
                    $visible[] = $pendingHeader;
                    $pendingHeader = null;
                }
                $visible[] = $child;
            }
        }

        return $visible;
    }

    /**
     * Check if a user can see a menu item based on required permissions.
     * Shared static method — called by MenuService and DashboardController.
     *
     * Returns true if:
     *   - Item is owner-only and the user holds the platform Owner role
     *   - No permission is required (always visible)
     *   - User holds a protected role (sees everything)
     *   - User holds ANY of the listed permissions
     */
    public static function canSee(?User $user, array|string|null $permissions, bool $ownerOnly = false): bool
    {
        if (! $user) {
            return false;
        }

        if ($ownerOnly) {
            return $user->isOwner();
        }

        if (empty($permissions)) {
            return true;
        }

        $required = (array) $permissions;

        if ($user->canBypassProtection()) {
            return true;
        }

        return $user->getAllPermissions()->intersect($required)->isNotEmpty();
    }

    /**
     * Check if a given route pattern matches the current request.
     */
    public static function isActive(string $pattern): bool
    {
        $path = request()->path();
        $regex = '#^'.str_replace('*', '.*', $pattern).'$#';

        return (bool) preg_match($regex, $path);
    }

    /**
     * Check if ANY of the given patterns match the current request.
     */
    public static function isActiveAny(array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (self::isActive($pattern)) {
                return true;
            }
        }

        return false;
    }
}
