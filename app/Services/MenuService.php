<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class MenuService
{
    protected array $config;
    protected ?User $user = null;

    public function __construct()
    {
        $this->config = config('menu.sections', []);
    }

    /**
     * Get the filtered menu sections for the current user.
     * Returns only items the user has permission to see.
     * Parent items auto-hide when ALL children are hidden.
     */
    public function getVisibleMenu(): array
    {
        $this->user = Auth::user();
        if (!$this->user) {
            return [];
        }

        $visible = [];
        foreach ($this->config as $item) {
            // Section headers — always include
            if (isset($item['header'])) {
                $visible[] = $item;
                continue;
            }

            // Leaf item (no children)
            if (empty($item['children'])) {
                if ($this->canSee($item)) {
                    $visible[] = $item;
                }
                continue;
            }

            // Parent with children — filter children first
            $visibleChildren = [];
            foreach ($item['children'] as $child) {
                // Sub-section headers — include if parent is visible
                if (isset($child['header'])) {
                    $visibleChildren[] = $child;
                    continue;
                }

                if ($this->canSee($child)) {
                    $visibleChildren[] = $child;
                }
            }

            // Only show parent if it has visible children or is itself visible
            if (!empty($visibleChildren) || $this->canSee($item)) {
                $item['children'] = $visibleChildren;
                $visible[] = $item;
            }
        }

        return $visible;
    }

    /**
     * Check if the current user can see a menu item.
     * Returns true if no permission is required (always visible)
     * or if the user holds ANY of the listed permissions.
     */
    protected function canSee(array $item): bool
    {
        $required = $item['permission'] ?? [];

        // No permission required — always visible
        if (empty($required)) {
            return true;
        }

        $required = (array) $required;

        return $this->user->hasAnyRole(['Super Admin'])
            || $this->user->getAllPermissions()->intersect($required)->isNotEmpty();
    }

    /**
     * Check if a given route pattern matches the current request.
     * Used by the blade to determine active state.
     */
    public static function isActive(string $pattern): bool
    {
        $path = request()->path();
        // Convert route pattern to regex: * matches anything
        $regex = '#^' . str_replace('*', '.*', $pattern) . '$#';
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
