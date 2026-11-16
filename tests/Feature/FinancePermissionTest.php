<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\FinancePolicy;
use App\Services\MenuService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The finance permission surface.
 *
 * `finance.export` and `finance.import` were referenced in the finance policy and
 * in config/menu.php but were never created by the permission seeder, so those
 * abilities could only ever return false and the menu entries that advertised
 * them could never be shown. The cash-flow report carried the same reference as
 * a route gate, which made it a permanent 403 for every role including Owner.
 *
 * The decision here is to keep the permission set as it is — `finance.view`,
 * `finance.manage`, `finance.approve` — and point the dead references at it,
 * rather than invent a fourth and fifth permission that nothing else checks.
 */
class FinancePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function userWithPermissions(array $permissions): User
    {
        $role = Role::create([
            'role_name' => 'Finance Test Role ' . uniqid(),
            'description' => 'Test role',
            'is_protected' => false,
            'is_hidden' => true,
        ]);

        $role->permissions()->sync(
            Permission::whereIn('permission_name', $permissions)->pluck('permission_id')
        );

        $user = User::factory()->create([
            'name' => 'Finance Permission User ' . substr(uniqid(), -8),
            'email' => 'finance-perm.' . uniqid() . '@test.local',
        ]);
        $user->roles()->sync([$role->role_id]);

        return $user->load('roles.permissions');
    }

    public function test_the_finance_permission_set_is_exactly_the_seeded_one(): void
    {
        $this->assertSame(
            [],
            Permission::whereIn('permission_name', ['finance.export', 'finance.import'])->pluck('permission_name')->all(),
            'These permissions are deliberately not part of the RBAC model.'
        );

        foreach (['finance.view', 'finance.manage', 'finance.approve'] as $permission) {
            $this->assertNotNull(
                Permission::where('permission_name', $permission)->first(),
                "{$permission} must exist."
            );
        }
    }

    /**
     * Every ability answers to a permission that exists — and answers the same
     * way whether it is asked through the policy or through the route gate.
     */
    public function test_every_finance_ability_uses_a_real_permission(): void
    {
        $policy = new FinancePolicy();

        $viewer = $this->userWithPermissions(['finance.view']);
        $manager = $this->userWithPermissions(['finance.manage']);
        $nobody = $this->userWithPermissions([]);

        // Viewing, and exporting what you can view.
        $this->assertTrue($policy->view($viewer));
        $this->assertTrue($policy->export($viewer));

        // Importing is a management action.
        $this->assertFalse($policy->import($viewer));
        $this->assertTrue($policy->import($manager));
        $this->assertTrue($policy->manage($manager));

        // Someone with nothing gets nothing.
        foreach (['view', 'manage', 'approve', 'export', 'import'] as $ability) {
            $this->assertFalse($policy->{$ability}($nobody), "finance policy {$ability}() must deny an unprivileged user.");
        }
    }

    /**
     * A permission that is not seeded cannot be held, so a menu entry gated on
     * one is invisible to everybody — the "feature exists but is unreachable"
     * failure. This scan keeps the phantom names out of the codebase, not just
     * out of the database.
     */
    public function test_no_phantom_finance_permission_is_referenced_in_code(): void
    {
        $files = array_merge(
            [$this->basePath('config/menu.php')],
            $this->phpFilesUnder($this->basePath('app')),
        );

        $offenders = [];

        foreach ($files as $file) {
            $contents = file_get_contents($file);

            // The scan is about the code, not the history: several files explain
            // in doc comments that finance.export/finance.import were abolished.
            // Strip comments before searching so documentation never misbehaves.
            $contents = preg_replace('#/\*.*?\*/#s', '', $contents);
            $contents = preg_replace('#^\s*//.*$#m', '', $contents);

            foreach (['finance.export' => 'finance.view', 'finance.import' => 'finance.manage'] as $dead => $replacement) {
                if (str_contains($contents, $dead)) {
                    $offenders[] = str_replace($this->basePath() . DIRECTORY_SEPARATOR, '', $file)
                        . ' references ' . $dead . ' — use ' . $replacement . ' instead.';
                }
            }
        }

        $this->assertSame([], $offenders, "Dead finance permissions are still referenced:\n  " . implode("\n  ", $offenders));
    }

    /**
     * Menu visibility and route authorisation must agree: what the menu offers
     * must be reachable, and what it hides must be refused.
     */
    public function test_the_financial_reports_menu_entry_and_its_route_agree(): void
    {
        $entry = null;

        $walk = function (array $items) use (&$walk, &$entry) {
            foreach ($items as $item) {
                if (($item['route'] ?? null) === 'financial-reports.index') {
                    $entry = $item;
                }

                foreach (['children', 'items'] as $key) {
                    if (! empty($item[$key]) && is_array($item[$key])) {
                        $walk($item[$key]);
                    }
                }
            }
        };

        $walk(config('menu.sections', []));

        $this->assertNotNull($entry, 'The finance module should configure a financial-reports.index menu entry.');

        $this->assertSame(
            ['finance.view'],
            $entry['permission'],
            'The Financial Reports menu entry must advertise the permission FinancialReportController enforces.'
        );

        $viewer = $this->userWithPermissions(['finance.view']);

        $this->assertTrue(
            MenuService::canSee($viewer, $entry['permission']),
            'A user holding the advertised permission must actually see the entry.'
        );

        $this->actingAs($viewer)->get(route('financial-reports.index'))->assertOk();

        // ...and a user without it sees nothing and is refused.
        $nobody = $this->userWithPermissions([]);

        $this->assertFalse(MenuService::canSee($nobody, $entry['permission']));

        $this->actingAs($nobody)->get(route('financial-reports.index'))->assertForbidden();
    }

    /**
     * The cash-flow report was the screen this permission killed: gated on a
     * permission nobody could hold, so nobody could open it.
     */
    public function test_the_cashflow_report_is_reachable_by_anyone_who_can_view_finance(): void
    {
        $viewer = $this->userWithPermissions(['finance.view']);

        $this->actingAs($viewer)
            ->get(route('financial-reports.cashflow'))
            ->assertOk();
    }

    private function basePath(string $append = ''): string
    {
        return base_path($append);
    }

    /** @return array<int, string> */
    private function phpFilesUnder(string $directory): array
    {
        $files = [];

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
