<?php

namespace Tests\Feature;

use App\Http\Controllers\DiscountSchemeController;
use App\Http\Controllers\FeeAdjustmentController;
use App\Http\Controllers\FeeArrearsController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeDashboardController;
use App\Http\Controllers\FeeManagementController;
use App\Http\Controllers\FeeReportsController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\StudentFeeAssignmentController;
use App\Http\Controllers\TermController;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Staff;
use App\Models\User;
use App\Models\UserRole;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Authorization regression suite for the Fee Management module.
 *
 * A hidden menu item is not authorization. Before this suite existed, 15 fee
 * endpoints — including payment reversal and the whole-school ledger exports —
 * were reachable by any authenticated user who typed the URL, because their
 * controller methods appeared in NO middleware list. This suite locks the
 * behaviour in so it cannot silently regress.
 *
 * Two complementary checks:
 *
 *  1. STATIC — every public method of every fee controller must be covered by a
 *     permission guard. This catches a newly added method that nobody guarded,
 *     which is exactly how the original holes appeared.
 *  2. RUNTIME — Teacher, Parent and Student get 403 on every affected route,
 *     and the fee-administrating roles still get through.
 *
 * Role and permission names are read from the real seeders (PermissionSeeder,
 * RbacSeeder), not assumed: Owner / Super Admin / Admin / Accountant hold the
 * `fees.*` permissions; Teacher / Parent / Student hold none.
 */
class FeeAuthorizationTest extends TestCase
{
    /**
     * Fee module controllers whose methods must each carry a permission guard.
     *
     * PortalFeeController and MobileFeeController are intentionally excluded:
     * they do not use `can:` middleware at all — the portal scopes by parent /
     * student ownership inside the method and the mobile API is scoped by the
     * Sanctum token plus in-method permission checks. They need their own
     * tests, which already exist (MobilePortalDataIsolationTest,
     * MobileFeeIdempotencyTest).
     */
    private const FEE_CONTROLLERS = [
        FeeManagementController::class,
        FeeDashboardController::class,
        StudentFeeAssignmentController::class,
        FeeAdjustmentController::class,
        FeeArrearsController::class,
        FeeReportsController::class,
        RefundController::class,
        DiscountSchemeController::class,
        FeeCategoryController::class,
        FeeStructureController::class,
        TermController::class,
    ];

    private const FIXTURE_EMAILS = [
        'Teacher' => 'fee-auth-teacher@test.local',
        'Parent' => 'fee-auth-parent@test.local',
        'Student' => 'fee-auth-student@test.local',
        'Accountant' => 'fee-auth-accountant@test.local',
        'Admin' => 'fee-auth-admin@test.local',
        'Super Admin' => 'fee-auth-superadmin@test.local',
        'Owner' => 'fee-auth-owner@test.local',
    ];

    /** Roles that must never reach a fee financial endpoint. */
    private const DENIED_ROLES = ['Teacher', 'Parent', 'Student'];

    /** Roles that legitimately administer school fees. */
    private const PERMITTED_ROLES = ['Accountant', 'Admin', 'Super Admin', 'Owner'];

    protected function setUp(): void
    {
        parent::setUp();

        if (! Schema::hasTable('roles')) {
            $this->markTestSkipped('RBAC tables are not present in this database.');
        }

        $this->purgeFixtureUsers();
        RolePermission::query()->delete();
        Role::query()->delete();
        Permission::query()->delete();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    protected function tearDown(): void
    {
        $this->purgeFixtureUsers();

        parent::tearDown();
    }

    private function purgeFixtureUsers(): void
    {
        $ids = User::whereIn('email', array_values(self::FIXTURE_EMAILS))->pluck('id');

        UserRole::whereIn('user_id', $ids)->delete();
        Staff::whereIn('user_id', $ids)->delete();
        User::whereIn('id', $ids)->delete();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => self::FIXTURE_EMAILS[$roleName]],
            ['name' => "Fee Auth {$roleName}", 'password' => bcrypt('password')]
        );

        $user->roles()->syncWithoutDetaching($role);

        return $user->load('roles.permissions');
    }

    /**
     * The fee endpoints that were unprotected before the fix and are owned by
     * the fee permissions (fees.view / fees.manage / fees.approve / fees.collect).
     *
     * Every id is deliberately non-existent. That matters: an AUTHORIZED
     * request must not be able to mutate real data while the test runs, so the
     * controller findOrFail()s and returns 404. Reaching the controller at all
     * (any status other than 403) proves the permission gate passed; the 404
     * proves nothing was written.
     *
     * @return array<string, array{0: string, 1: string, 2?: array}>
     */
    private function previouslyUnguardedEndpoints(): array
    {
        return [
            // --- Payment reversal (money already receipted) ---
            'payment reversal form'        => ['get', '/fees/payments/999999999/reverse'],
            'payment reversal submit'      => ['post', '/fees/payments/999999999/reverse', ['reason' => 'authorization test']],

            // --- Fee adjustments ---
            'adjustment approve'           => ['post', '/fees/adjustments/999999999/approve', []],
            'adjustment reject'            => ['post', '/fees/adjustments/999999999/reject', ['rejection_reason' => 'authorization test']],
            'adjustment audit log'         => ['get', '/fees/adjustments/999999999/audit-log'],
            'student adjustment history'   => ['get', '/fees/adjustments/student/999999999'],
            'adjustment fee lookup ajax'   => ['get', '/fees/adjustments/ajax/student-fees'],
            'pending adjustments queue'    => ['get', '/fees/adjustments/pending'],

            // --- Fee assignments / student billing ---
            'student fee summary'          => ['get', '/fees/assignments/student/999999999'],
            'unassigned students'          => ['get', '/fees/assignments/unassigned'],
            'assignment ajax class fees'   => ['get', '/fees/assignments/ajax/class-fees'],
            'assignment ajax classes fees' => ['get', '/fees/assignments/ajax/classes-fees'],
            'assignment ajax auto preview' => ['get', '/fees/assignments/ajax/auto-preview'],
            'assignment ajax all fees'     => ['get', '/fees/assignments/ajax/all-fees'],

            // --- Refunds ---
            'refund student payments ajax' => ['get', '/fees/refunds/ajax/student-payments/999999999'],

            // --- Whole-school fee ledger exports ---
            'ledger export pdf'            => ['get', '/fee-management/export/pdf'],
            'ledger export excel'          => ['get', '/fee-management/export/excel'],

            // --- Fee categories ---
            'fee category code generation' => ['post', '/fee-categories/generate-code', []],
        ];
    }

    /**
     * Term activation was in the same unguarded set, but it is owned by the
     * academic-settings permission rather than by the fee permissions.
     *
     * `config/menu.php` gates the fee module's own "Terms" menu item on
     * `academics.settings.manage`, and `TermController` guards its whole
     * surface with the same permission — so menu visibility and the route
     * guard already agree. The permission is seeded to Owner, Super Admin and
     * Admin only (`RbacSeeder::rolePermissions`), which is why the Accountant —
     * who holds every `fees.*` permission — is denied here by design.
     *
     * Activating a term changes what the whole application treats as the
     * current term, so it deliberately needs more than fee access.
     *
     * @return array<string, array{0: string, 1: string, 2?: array}>
     */
    private function academicSettingsFeeEndpoints(): array
    {
        return [
            'term activation' => ['post', '/fees/terms/999999999/activate', []],
        ];
    }

    private function callEndpoint(string $method, string $uri, array $payload = [])
    {
        return $method === 'post'
            ? $this->post($uri, $payload)
            : $this->get($uri);
    }

    /**
     * The HTTP status of any response a fee endpoint can return.
     *
     * The ledger exports stream a file, and a StreamedResponse is a Symfony
     * response rather than a Laravel TestResponse, so it has getStatusCode()
     * and no status(). Calling status() unconditionally made the
     * "administrators can still reach it" test error on the two export
     * endpoints — the endpoint itself was working, which is precisely why it
     * returned a stream. Asserting through this helper keeps the intent
     * (403 vs not-403) for every response type.
     */
    private function statusOf($response): int
    {
        return method_exists($response, 'status')
            ? (int) $response->status()
            : (int) $response->getStatusCode();
    }

    // ─────────────────────────────── STATIC CHECK ───────────────────────────────

    /**
     * Every public method on every fee controller must be named in a
     * permission-guarded middleware list (or covered by a controller-wide
     * guard). This is the check that would have caught the original holes at
     * code-review time instead of in production.
     */
    public function test_every_public_fee_controller_method_is_covered_by_a_permission_guard(): void
    {
        $uncovered = [];

        foreach (self::FEE_CONTROLLERS as $controller) {
            $reflection = new ReflectionClass($controller);
            $source = file_get_contents($reflection->getFileName());

            $guardedEverywhere = false;
            $guardedMethods = [];

            // ->middleware('can:ability')->only([...])  AND  ->middleware('can:ability');
            preg_match_all(
                "/->middleware\(\s*'can:([a-z.]+)'\s*\)\s*(?:->only\(\s*\[(.*?)\]\s*\))?/s",
                $source,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                if (! isset($match[2]) || trim($match[2]) === '') {
                    // No ->only() means the guard applies to the whole controller.
                    $guardedEverywhere = true;

                    continue;
                }

                preg_match_all("/'([A-Za-z0-9_]+)'/", $match[2], $methods);
                $guardedMethods = array_merge($guardedMethods, $methods[1]);
            }

            if ($guardedEverywhere) {
                continue;
            }

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // Only methods declared on the controller itself; inherited
                // framework helpers are not routes.
                if ($method->class !== $controller) {
                    continue;
                }

                if (str_starts_with($method->getName(), '__')) {
                    continue;
                }

                if (! in_array($method->getName(), $guardedMethods, true)) {
                    $uncovered[] = class_basename($controller) . '::' . $method->getName() . '()';
                }
            }
        }

        $this->assertSame(
            [],
            $uncovered,
            "These fee controller methods have no permission guard, so any authenticated user can call them:\n  - "
                . implode("\n  - ", $uncovered)
        );
    }

    // ────────────────────────────── RUNTIME CHECKS ──────────────────────────────

    /**
     * @dataProvider deniedRoleProvider
     */
    public function test_previously_unguarded_fee_endpoints_return_403_for_non_finance_roles(string $role): void
    {
        $user = $this->userWithRole($role);

        // Both ownership groups are checked: a Teacher, Parent or Student holds
        // neither the fee permissions nor academics.settings.manage, so every
        // one of these must be forbidden.
        $endpoints = $this->previouslyUnguardedEndpoints() + $this->academicSettingsFeeEndpoints();

        foreach ($endpoints as $label => $endpoint) {
            [$method, $uri] = $endpoint;
            $response = $this->actingAs($user)->callEndpoint($method, $uri, $endpoint[2] ?? []);

            $this->assertSame(
                403,
                $this->statusOf($response),
                "[{$role}] {$label} ({$method} {$uri}) must be forbidden, got {$this->statusOf($response)}."
            );
        }
    }

    public static function deniedRoleProvider(): array
    {
        return array_combine(self::DENIED_ROLES, array_map(fn ($r) => [$r], self::DENIED_ROLES));
    }

    /**
     * The roles that actually administer fees must keep working. Without this,
     * "fix authorization" could be satisfied by locking everyone out.
     */
    public function test_previously_unguarded_fee_endpoints_remain_reachable_for_fee_administrators(): void
    {
        foreach (self::PERMITTED_ROLES as $role) {
            $user = $this->userWithRole($role);

            foreach ($this->previouslyUnguardedEndpoints() as $label => $endpoint) {
                [$method, $uri] = $endpoint;
                $response = $this->actingAs($user)->callEndpoint($method, $uri, $endpoint[2] ?? []);

                $this->assertNotSame(
                    403,
                    $this->statusOf($response),
                    "[{$role}] {$label} ({$method} {$uri}) was locked out by the new guard — "
                        . "{$role} holds the fees permissions and must still reach it."
                );
            }
        }
    }

    /**
     * Term activation is protected, and protected by the right permission.
     *
     * The audit required that term activation not be reachable by an ordinary
     * user. It is gated by `academics.settings.manage`, which the seeders grant
     * to Owner, Super Admin and Admin — and deliberately NOT to the Accountant,
     * who holds every `fees.*` permission. Anchoring both halves here means a
     * future change to either the guard or the seeder shows up as a failure
     * rather than as a silent widening of who can change the school's term.
     */
    public function test_term_activation_requires_the_academic_settings_permission(): void
    {
        $accountant = $this->userWithRole('Accountant');

        $this->assertFalse(
            $accountant->hasPermission('academics.settings.manage'),
            'The Accountant is not expected to hold academics.settings.manage; this test depends on it.'
        );

        $this->actingAs($accountant)
            ->post('/fees/terms/999999999/activate')
            ->assertForbidden();

        foreach (['Admin', 'Super Admin', 'Owner'] as $role) {
            $user = $this->userWithRole($role);

            $this->assertTrue(
                $user->hasPermission('academics.settings.manage'),
                "{$role} is expected to hold academics.settings.manage."
            );

            $this->assertNotSame(
                403,
                $this->statusOf($this->actingAs($user)->post('/fees/terms/999999999/activate')),
                "{$role} holds academics.settings.manage and must still reach term activation."
            );
        }
    }

    /**
     * The fee module's own "Terms" menu entry advertises the permission that
     * actually guards the route. A menu entry gated on a permission the route
     * does not enforce (or vice versa) is how "the button is visible but 403s"
     * and "the feature exists but is unreachable" both happen.
     */
    public function test_the_fee_terms_menu_entry_advertises_the_permission_the_route_enforces(): void
    {
        // config/menu.php nests the item list under a 'sections' key.
        $menu = config('menu.sections', []);

        $termEntry = null;

        $walk = function (array $items) use (&$walk, &$termEntry) {
            foreach ($items as $item) {
                if (($item['route'] ?? null) === 'fees.terms.index') {
                    $termEntry = $item;
                }

                if (! empty($item['children']) && is_array($item['children'])) {
                    $walk($item['children']);
                }

                if (! empty($item['items']) && is_array($item['items'])) {
                    $walk($item['items']);
                }
            }
        };

        $walk($menu);

        $this->assertNotNull($termEntry, 'The fee module should configure a fees.terms.index menu entry.');

        $this->assertSame(
            ['academics.settings.manage'],
            $termEntry['permission'],
            'The Terms menu entry must advertise the same permission TermController enforces.'
        );

        // And the route itself resolves, so the menu cannot point nowhere.
        $this->assertSame(url('/fees/terms'), route('fees.terms.index'));
    }

    /**
     * The seeders grant fees.* to Accountant but not to Teacher. Anchor that
     * assumption so the tests above cannot silently become meaningless if the
     * RBAC seed changes.
     */
    public function test_rbac_fixture_assumptions_still_hold(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $teacher = $this->userWithRole('Teacher');

        foreach (['fees.view', 'fees.manage', 'fees.approve', 'fees.collect'] as $permission) {
            $this->assertTrue(
                $accountant->hasPermission($permission),
                "Accountant is expected to hold {$permission}."
            );
        }

        foreach (['fees.view', 'fees.manage', 'fees.approve', 'fees.collect'] as $permission) {
            $this->assertFalse(
                $teacher->hasPermission($permission),
                "Teacher must not hold {$permission} — the 403 expectations depend on this."
            );
        }
    }

    // ───────────────────────── ROUTE SHADOWING REGRESSION ─────────────────────────

    /**
     * `/fees/adjustments/pending` must be handled by pendingApprovals(), not
     * captured as {id} by show(). Static routes have to be registered before
     * parameterised ones.
     */
    public function test_pending_approvals_route_is_not_shadowed_by_the_show_route(): void
    {
        $this->assertSame(
            url('/fees/adjustments/pending'),
            route('fees.adjustments.pending'),
            'The pending-approvals route must resolve to its own URL.'
        );

        $accountant = $this->userWithRole('Accountant');

        $this->actingAs($accountant)
            ->get('/fees/adjustments/pending')
            ->assertOk();

        // And the parameterised route must still work as before.
        $this->actingAs($accountant)
            ->get('/fees/adjustments/999999999')
            ->assertNotFound();
    }

    /**
     * No other literal segment in the adjustments group may be swallowed by the
     * {id} route either.
     */
    public function test_no_other_static_adjustment_segment_is_shadowed(): void
    {
        $source = file_get_contents(base_path('routes/web.php'));

        $showPosition = strpos($source, "Route::get('/adjustments/{id}'");
        $this->assertNotFalse($showPosition, 'The adjustments show route should exist.');

        foreach (['/adjustments/pending', '/adjustments/create'] as $literal) {
            $literalPosition = strpos($source, "Route::get('{$literal}'");
            $this->assertNotFalse($literalPosition, "Route {$literal} should exist.");

            $this->assertLessThan(
                $showPosition,
                $literalPosition,
                "Route {$literal} must be registered before /adjustments/{id} or it will be captured as an id."
            );
        }
    }
}
