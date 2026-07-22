<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class MenuValidate extends Command
{
    protected $signature = 'menu:validate';
    protected $description = 'Validate that all route names in config/menu.php are registered routes';

    public function handle(): int
    {
        // Get all registered route names
        $registeredRoutes = [];
        foreach (Route::getRoutes() as $route) {
            if ($route->getName()) {
                $registeredRoutes[$route->getName()] = true;
            }
        }

        // Get menu config
        $menuConfig = config('menu.sections', []);
        $menuRoutes = $this->extractRouteNames($menuConfig);

        $broken = [];
        foreach ($menuRoutes as $routeName => $context) {
            if (!isset($registeredRoutes[$routeName])) {
                $broken[$routeName] = $context;
            }
        }

        if (empty($broken)) {
            $this->info('✓ All ' . count($menuRoutes) . ' route references in config/menu.php are valid.');
            return self::SUCCESS;
        }

        $this->error('✗ Found ' . count($broken) . ' broken route reference(s) in config/menu.php:');
        $this->newLine();

        foreach ($broken as $route => $context) {
            $this->line("  <fg=red>MISSING:</fg=red> {$route}  <comment>({$context})</comment>");
        }

        $this->newLine();
        $this->warn('Run this command after any route changes to catch broken menu links before deploy.');
        return self::FAILURE;
    }

    /**
     * Recursively extract all route names from the menu config structure.
     */
    protected function extractRouteNames(array $sections): array
    {
        $routes = [];

        foreach ($sections as $item) {
            // Skip headers
            if (isset($item['header'])) {
                continue;
            }

            // Leaf item with a route
            if (isset($item['route'])) {
                $key = $item['key'] ?? $item['label'] ?? 'unknown';
                $routes[$item['route']] = $item['label'] ?? $item['key'] ?? 'menu item';
            }

            // Recurse into children
            if (!empty($item['children'])) {
                $routes = array_merge($routes, $this->extractRouteNames($item['children']));
            }
        }

        return $routes;
    }
}
