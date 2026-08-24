<?php

namespace App\Services;

use App\Models\Module;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ModuleManager
{
    /**
     * Cache key holding all modules keyed by their module key.
     * Forgotten on every toggle so enabled/disabled state is fresh.
     */
    private const ALL_CACHE_KEY = 'modules.all';

    /**
     * All registered modules, keyed by module key, ordered by the
     * configured display order. Cached for the lifetime of the request.
     */
    public function all(): Collection
    {
        return Cache::remember(self::ALL_CACHE_KEY, now()->addDay(), function () {
            if (!\Illuminate\Support\Facades\Schema::hasTable('modules')) {
                return collect();
            }

            return Module::query()
                ->orderBy('order')
                ->get()
                ->keyBy('key');
        });
    }

    /**
     * The keys of every currently active module.
     */
    public function activeKeys(): array
    {
        return $this->all()
            ->where('is_active', true)
            ->keys()
            ->all();
    }

    /**
     * Whether a module is enabled.
     *
     * Unknown keys (menu items with no matching modules row) are treated as
     * active so the sidebar never hides an item the installer has not opted
     * into the module registry.
     */
    public function isActive(string $key): bool
    {
        $module = $this->all()->get($key);

        if (!$module) {
            return true;
        }

        return (bool) $module->is_active;
    }

    /**
     * Flip a module's enabled state.
     *
     * @throws \DomainException when no such module exists
     */
    public function toggle(string $key, bool $state): Module
    {
        $module = Module::where('key', $key)->firstOrFail();

        $module->update(['is_active' => $state]);
        $this->forgetCache();

        return $module->fresh();
    }

    /**
     * Drop the cached module registry after a state change.
     */
    public function forgetCache(): void
    {
        Cache::forget(self::ALL_CACHE_KEY);
    }
}
