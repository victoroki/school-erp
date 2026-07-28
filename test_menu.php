<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Services\MenuService;

$u = User::find(1);
$u->load('roles.permissions');

echo "Roles: " . $u->roles->pluck('role_name')->implode(', ') . PHP_EOL;
echo "hasAnyRole(Super Admin): " . ($u->hasAnyRole(['Super Admin']) ? 'YES' : 'NO') . PHP_EOL;
echo "Permissions count: " . $u->getAllPermissions()->count() . PHP_EOL;
echo "Has hr.view: " . ($u->getAllPermissions()->contains('hr.view') ? 'YES' : 'NO') . PHP_EOL;

// Test canSee for GOVERNANCE items
$testPerms = [
    'HR' => ['hr.view', 'hr.manage', 'hr.approve'],
    'Finance' => ['finance.view', 'finance.manage'],
    'Hostel' => ['hostel.view', 'hostel.manage'],
    'Transport' => ['transport.view', 'transport.manage'],
    'Inventory' => ['inventory.view', 'inventory.manage'],
    'Library' => ['library.view', 'library.manage'],
    'Fees' => ['fees.view', 'fees.manage'],
    'Communication' => ['communication.view', 'communication.manage'],
];

foreach ($testPerms as $name => $perms) {
    $result = MenuService::canSee($u, $perms) ? 'YES' : 'NO';
    echo "canSee($name): $result" . PHP_EOL;
}

// Now test the full menu rendering
$menuService = new MenuService();
$menu = $menuService->getVisibleMenu();
echo PHP_EOL . "=== VISIBLE MENU ITEMS ===" . PHP_EOL;
foreach ($menu as $item) {
    if (isset($item['header'])) {
        echo PHP_EOL . "--- {$item['header']} ---" . PHP_EOL;
        continue;
    }
    $childCount = isset($item['children']) ? count(array_filter($item['children'], fn($c) => !isset($c['header']))) : 0;
    echo "  {$item['label']} (children: $childCount)" . PHP_EOL;
}
