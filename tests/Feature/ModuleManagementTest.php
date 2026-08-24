<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Models\RolePermission;
use App\Models\Staff;
use App\Models\Module;
use App\Services\MenuService;
use App\Services\ModuleManager;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Database\Seeders\ModuleSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Module enable/disable gating — sidebar visibility + route blocking.
 *
 * Runs against the isolated school_erp_test database. Hermetic: seeds RBAC
 * and module tables when missing, creates fixture users per role, and resets
 * module state at the start of every test.
 */
class ModuleManagementTest extends TestCase
{
    private const FIXTURE_EMAILS = [
        'Super Admin' => 'module-test-superadmin@test.local',
        'Teacher'     => 'module-test-teacher@test.local',
        'Student'     => 'module-test-student@test.local',
        'Owner'       => 'module-test-owner@test.local',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        if (Schema::hasTable('roles')) {
            $emails = array_values(self::FIXTURE_EMAILS);
            UserRole::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
            Staff::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
            User::whereIn('email', $emails)->delete();
            RolePermission::query()->delete();
            Role::query()->delete();
            Permission::query()->delete();
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        if (Schema::hasTable('modules')) {
            Module::query()->delete();
            $this->seed(ModuleSeeder::class);
        }
    }

    protected function tearDown(): void
    {
        $emails = array_values(self::FIXTURE_EMAILS);
        UserRole::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
        Staff::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
        User::whereIn('email', $emails)->delete();
        parent::tearDown();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => self::FIXTURE_EMAILS[$roleName]],
            ['name' => "Module Test {$roleName}", 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->load('roles.permissions');
    }

    private function visibleMenuFor(User $user): array
    {
        Auth::setUser($user->loadMissing('roles.permissions'));
        return app(MenuService::class)->getVisibleMenu();
    }

    private function topLabels(array $menu): array
    {
        return array_values(array_map(
            fn($i) => $i['label'],
            array_filter($menu, fn($i) => !isset($i['header']))
        ));
    }

    private function allChildLabels(array $menu): array
    {
        $labels = [];
        foreach ($menu as $item) {
            if (!isset($item['children'])) {
                continue;
            }
            foreach ($item['children'] as $child) {
                if (!isset($child['header'])) {
                    $labels[] = $child['label'];
                }
            }
        }
        return $labels;
    }

    // ─── SCREEN ACCESS ────────────────────────────────────────────

    public function test_modules_screen_is_owner_only(): void
    {
        $this->actingAs($this->userWithRole('Student'))
            ->get('/modules')
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/modules')
            ->assertForbidden();

        // The school's Super Admin is locked out of module management too —
        // activating paid modules is the SaaS provider's exclusive right.
        $this->actingAs($this->userWithRole('Super Admin'))
            ->get('/modules')
            ->assertForbidden();

        $response = $this->actingAs($this->userWithRole('Owner'))
            ->get('/modules');

        $response->assertOk();
        $response->assertSee('Modules');
        $response->assertSee('Library');
    }

    // ─── DASHBOARD TILES ─────────────────────────────────────────

    public function test_disabled_module_tile_hidden_from_dashboard_and_restored_when_enabled(): void
    {
        $manager = app(ModuleManager::class);
        $admin = $this->userWithRole('Super Admin');

        // Tile links only render when the module is enabled (asserting on the
        // tile's route URL — safe against label collisions in other panels).
        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(route('library.dashboard'))
            ->assertSee(route('fee-management.index'))
            ->assertSee(route('exam-dashboard.index'));

        $manager->toggle('library', false);
        $manager->toggle('fees', false);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertDontSee(route('library.dashboard'))
            ->assertDontSee(route('fee-management.index'))
            ->assertSee(route('exam-dashboard.index'));

        $manager->toggle('library', true);

        $this->actingAs($admin)
            ->get('/dashboard')
            ->assertOk()
            ->assertSee(route('library.dashboard'))
            ->assertDontSee(route('fee-management.index'));
    }

    // ─── SIDEBAR FILTERING ────────────────────────────────────────

    public function test_disabled_module_hidden_from_sidebar_and_restored_when_enabled(): void
    {
        $manager = app(ModuleManager::class);
        $admin = $this->userWithRole('Super Admin');

        // Enabled by default.
        $this->assertContains('Library Management', $this->topLabels($this->visibleMenuFor($admin)));
        $this->assertContains('All Books', $this->allChildLabels($this->visibleMenuFor($admin)));

        $manager->toggle('library', false);

        $menu = $this->visibleMenuFor($admin);
        $this->assertNotContains('Library Management', $this->topLabels($menu));
        $this->assertNotContains('All Books', $this->allChildLabels($menu));

        $manager->toggle('library', true);

        $this->assertContains('Library Management', $this->topLabels($this->visibleMenuFor($admin)));
        $this->assertContains('All Books', $this->allChildLabels($this->visibleMenuFor($admin)));
    }

    // ─── ROUTE BLOCKING ───────────────────────────────────────────

    public function test_disabled_module_routes_return_404_and_restore_on_enable(): void
    {
        $manager = app(ModuleManager::class);
        $admin = $this->actingAs($this->userWithRole('Super Admin'));

        $admin->get('/books')->assertOk();
        $admin->get('/library/dashboard')->assertOk();

        $manager->toggle('library', false);

        $admin->get('/books')->assertNotFound();
        $admin->get('/library/dashboard')->assertNotFound();

        $manager->toggle('library', true);

        $admin->get('/books')->assertOk();
        $admin->get('/library/dashboard')->assertOk();
    }

    public function test_unmapped_and_other_module_routes_are_not_affected(): void
    {
        $manager = app(ModuleManager::class);
        $admin = $this->actingAs($this->userWithRole('Super Admin'));

        $manager->toggle('library', false);

        // Portal routes carry no module pattern → still reachable.
        $admin->get('/portal/profile')->assertOk();

        // Another enabled module's routes still work.
        $admin->get('/books')->assertNotFound();
        $admin->get('/students')->assertOk();
    }

    // ─── CORE MODULES ─────────────────────────────────────────────

    public function test_core_modules_can_be_toggled_by_privileged_users(): void
    {
        $manager = app(ModuleManager::class);
        $admin = $this->actingAs($this->userWithRole('Super Admin'));

        $manager->toggle('dashboard', false);

        $this->assertFalse($manager->isActive('dashboard'));
        $admin->get('/home')->assertNotFound();

        $manager->toggle('dashboard', true);

        $this->assertTrue($manager->isActive('dashboard'));
        $admin->get('/home')->assertRedirect(route('dashboard'));
    }

    public function test_core_module_can_be_toggled_via_endpoint(): void
    {
        $owner = $this->actingAs($this->userWithRole('Owner'));
        $manager = app(ModuleManager::class);

        $this->post('/modules/dashboard/toggle', ['is_active' => 0])
            ->assertRedirect();

        $this->assertFalse($manager->isActive('dashboard'));
        $owner->get('/home')->assertNotFound();

        $this->post('/modules/dashboard/toggle', ['is_active' => 1])
            ->assertRedirect();

        $this->assertTrue($manager->isActive('dashboard'));
        $owner->get('/home')->assertRedirect(route('dashboard'));
    }

    // ─── HTTP TOGGLE AUTHORIZATION ────────────────────────────────

    public function test_non_super_admin_cannot_toggle_modules(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->post('/modules/library/toggle', ['is_active' => 0])
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Student'))
            ->post('/modules/library/toggle', ['is_active' => 0])
            ->assertForbidden();

        // State untouched.
        $this->assertTrue(app(ModuleManager::class)->isActive('library'));
    }

    public function test_owner_toggles_module_via_endpoint(): void
    {
        $ownerUser = $this->userWithRole('Owner');
        $manager = app(ModuleManager::class);

        $this->actingAs($ownerUser)
            ->post('/modules/fees/toggle', ['is_active' => 0])
            ->assertRedirect();

        $this->assertFalse($manager->isActive('fees'));
        $this->actingAs($ownerUser)->get('/fees/dashboard')->assertNotFound();
        $this->assertNotContains('Fee Management', $this->topLabels($this->visibleMenuFor($this->userWithRole('Super Admin'))));

        // visibleMenuFor() swaps the authenticated user, so re-establish the
        // owner session before the next request.
        $this->actingAs($ownerUser)
            ->post('/modules/fees/toggle', ['is_active' => 1])
            ->assertRedirect();

        $this->assertTrue($manager->isActive('fees'));
        $this->actingAs($ownerUser)->get('/fees/dashboard')->assertOk();
        $this->assertContains('Fee Management', $this->topLabels($this->visibleMenuFor($this->userWithRole('Super Admin'))));
    }

    public function test_super_admin_cannot_toggle_modules_via_endpoint(): void
    {
        // Regression guard: the school's Super Admin must never be able to
        // flip paid module switches — that is the platform Owner's right.
        $this->actingAs($this->userWithRole('Super Admin'))
            ->post('/modules/library/toggle', ['is_active' => 0])
            ->assertForbidden();

        $this->assertTrue(app(ModuleManager::class)->isActive('library'));
    }
}
