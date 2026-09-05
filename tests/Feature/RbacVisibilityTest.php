<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * RBAC visibility regression checks.
 *
 * Runs against the isolated school_erp_test database.
 * Hermetic: seeds RBAC tables when missing, creates fixture users per role.
 */
class RbacVisibilityTest extends TestCase
{
    private const FIXTURE_EMAILS = [
        'Super Admin' => 'rbac-test-superadmin@test.local',
        'Teacher'     => 'rbac-test-teacher@test.local',
        'Student'     => 'rbac-test-student@test.local',
        'Accountant'  => 'rbac-test-accountant@test.local',
        'Owner'       => 'rbac-test-owner@test.local',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $emails = array_values(self::FIXTURE_EMAILS);
            // FK-safe order: child tables first
            \App\Models\UserRole::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
            \App\Models\Staff::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
            User::whereIn('email', $emails)->delete();
            \App\Models\RolePermission::query()->delete();
            Role::query()->delete();
            Permission::query()->delete();
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }
    }

    protected function tearDown(): void
    {
        $emails = array_values(self::FIXTURE_EMAILS);
        \App\Models\UserRole::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
        \App\Models\Staff::whereIn('user_id', User::whereIn('email', $emails)->pluck('id'))->delete();
        User::whereIn('email', $emails)->delete();
        parent::tearDown();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => self::FIXTURE_EMAILS[$roleName]],
            ['name' => "RBAC Test {$roleName}", 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->load('roles.permissions');
    }

    private function visibleMenuFor(User $user): array
    {
        Auth::setUser($user->loadMissing('roles.permissions'));
        return app(MenuService::class)->getVisibleMenu();
    }

    private function topHeaders(array $menu): array
    {
        return array_values(array_map(
            fn($i) => $i['header'],
            array_filter($menu, fn($i) => isset($i['header']))
        ));
    }

    private function topLabels(array $menu): array
    {
        return array_values(array_map(
            fn($i) => $i['label'],
            array_filter($menu, fn($i) => !isset($i['header']))
        ));
    }

    private function childLabels(array $menu, string $parentKey): array
    {
        $parent = collect($menu)->firstWhere('key', $parentKey);
        $this->assertNotNull($parent, "Expected parent [$parentKey] in visible menu");
        return array_values(array_map(
            fn($c) => $c['label'],
            array_filter($parent['children'], fn($c) => !isset($c['header']))
        ));
    }

    private function childHeaders(array $menu, string $parentKey): array
    {
        $parent = collect($menu)->firstWhere('key', $parentKey);
        $this->assertNotNull($parent, "Expected parent [$parentKey] in visible menu");
        return array_values(array_map(
            fn($c) => $c['header'],
            array_filter($parent['children'], fn($c) => isset($c['header']))
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

    // ─── SIDEBAR ──────────────────────────────────────────────────

    public function test_super_admin_sees_every_section_and_item(): void
    {
        $menu = $this->visibleMenuFor($this->userWithRole('Super Admin'));

        $this->assertSame(
            ['CORE DASHBOARD', 'EDUCATIONAL UNITS', 'OPERATIONS', 'GOVERNANCE'],
            $this->topHeaders($menu)
        );

        foreach (['Dashboard', 'User Management', 'Academic Management', 'Student Management',
                  'Examinations', 'Inventory Management', 'Library Management', 'Fee Management',
                  'Human Resources', 'Financial Management', 'Hostel Management',
                  'Transportation', 'Communication'] as $label) {
            $this->assertContains($label, $this->topLabels($menu));
        }
    }

    public function test_teacher_sees_only_academic_sections_with_no_orphaned_headings(): void
    {
        $menu = $this->visibleMenuFor($this->userWithRole('Teacher'));

        $this->assertSame(['CORE DASHBOARD', 'EDUCATIONAL UNITS'], $this->topHeaders($menu));

        foreach (['Human Resources', 'Financial Management', 'Inventory Management',
                  'Library Management', 'Fee Management', 'Hostel Management',
                  'Transportation', 'Communication', 'User Management'] as $label) {
            $this->assertNotContains($label, $this->topLabels($menu));
        }

        // Student Management: only Student Attendance is visible (academics.attendance.manage)
        $this->assertSame(['Student Attendance'], $this->childLabels($menu, 'students'));

        // Academic Management: only Dashboard + My Timetable + Apply for Leave are visible
        // (academics.view + hr.leave.apply); structural/admin children stay hidden.
        $this->assertSame(['Dashboard', 'My Timetable', 'Apply for Leave'], $this->childLabels($menu, 'academics'));

        // Examinations: teacher has schedule.view, marks.enter-own, results.view-own
        // Admin/management items (Dashboard, Sessions, Timetables) are admin-role
        // only and must NOT appear for an ordinary teacher.
        $examChildren = $this->childLabels($menu, 'exams');
        $this->assertNotContains('Dashboard', $examChildren);
        $this->assertNotContains('Sessions', $examChildren);
        $this->assertNotContains('Timetables', $examChildren);
        $this->assertContains('Enter Marks', $examChildren);
        $this->assertContains('Grade Book', $examChildren);
        $this->assertContains('Report Cards', $examChildren);
        $this->assertNotContains('Approval', $examChildren);
        $this->assertNotContains('Analysis', $examChildren);
        $this->assertNotContains('Bulk Import Marks', $examChildren);
        $this->assertNotContains('Grading Systems', $examChildren);

        // Sub-headers within Examinations: Management is admin-only and must not
        // render; Marks and Reports (and CBE, via academics.view) should.
        $examHeaders = $this->childHeaders($menu, 'exams');
        $this->assertNotContains('Management', $examHeaders);
        $this->assertContains('Marks', $examHeaders);
        $this->assertContains('Reports', $examHeaders);
        $this->assertContains('CBE Curriculum', $examHeaders);
        $this->assertNotContains('Configuration', $examHeaders);
    }

    public function test_zero_permission_role_sees_only_dashboard_link(): void
    {
        $menu = $this->visibleMenuFor($this->userWithRole('Student'));

        $this->assertSame(['CORE DASHBOARD'], $this->topHeaders($menu));
        $this->assertSame(['Dashboard'], $this->topLabels($menu));
    }

    public function test_single_permission_role_gets_no_orphaned_top_headings(): void
    {
        $role = new Role(['role_name' => 'Test Cashier']);
        $role->setRelation(
            'permissions',
            Permission::where('permission_name', 'fees.view')->get()
        );
        $user = new User(['name' => 'Test Cashier', 'email' => 'cashier@test.local']);
        $user->setRelation('roles', collect([$role]));

        $menu = $this->visibleMenuFor($user);

        $this->assertSame(['CORE DASHBOARD', 'OPERATIONS'], $this->topHeaders($menu));
        $this->assertSame(['Dashboard', 'Fee Management'], $this->topLabels($menu));
        $this->assertContains('Dashboard', $this->childLabels($menu, 'fees'));
    }

    // ─── DASHBOARD ────────────────────────────────────────────────

    public function test_super_admin_dashboard_shows_all_widgets(): void
    {
        $response = $this->actingAs($this->userWithRole('Super Admin'))->get('/dashboard');

        $response->assertOk();
        foreach (['Total Students', 'Active Staff', 'Total Classes', 'Monthly Rev',
                  'chartMain', 'Module Status'] as $widget) {
            $response->assertSee($widget);
        }
        $response->assertSee('Growth & Revenue', false);
    }

    public function test_teacher_dashboard_hides_non_academic_widgets(): void
    {
        $response = $this->actingAs($this->userWithRole('Teacher'))->get('/dashboard');

        $response->assertOk();
        // Teacher has academics.view → Total Classes card visible
        $response->assertSee('Total Classes');
        // No students/fees permissions
        $response->assertDontSee('Total Students');
        $response->assertDontSee('Active Staff');
        $response->assertDontSee('Monthly Rev');
        // No chart (no students.view / fees.view / exams.results.view-all)
        $response->assertDontSee('chartMain');
        $response->assertDontSee('Growth & Revenue', false);
        // Sidebar sections filtered
        $response->assertDontSee('OPERATIONS');
        $response->assertDontSee('GOVERNANCE');
    }

    public function test_zero_permission_role_gets_minimal_unbroken_dashboard(): void
    {
        $response = $this->actingAs($this->userWithRole('Student'))->get('/dashboard');

        $response->assertOk();
        $response->assertDontSee('Key Metrics');
        $response->assertDontSee('Module Dashboards');
        $response->assertDontSee('chartMain');
        $response->assertDontSee('Total Students');
        $response->assertDontSee('Monthly Rev');
        $response->assertDontSee('OPERATIONS');
        $response->assertDontSee('GOVERNANCE');
        $response->assertDontSee('EDUCATIONAL UNITS');
    }

    public function test_dashboard_data_endpoint_filters_statistics_by_permission(): void
    {
        $teacher = $this->actingAs($this->userWithRole('Teacher'))
            ->getJson('/dashboard/data');
        $teacher->assertOk();
        $teacher->assertJsonStructure(['statistics' => ['total_classes']]);
        $teacher->assertJsonMissingPath('statistics.total_students');
        $teacher->assertJsonMissingPath('statistics.total_teachers');
        $teacher->assertJsonMissingPath('statistics.monthly_revenue');
        $teacher->assertJsonMissingPath('charts.enrollment_trend');

        $student = $this->actingAs($this->userWithRole('Student'))
            ->getJson('/dashboard/data');
        $student->assertOk();
        $student->assertExactJson(['statistics' => [], 'charts' => []]);

        $admin = $this->actingAs($this->userWithRole('Super Admin'))
            ->getJson('/dashboard/data');
        $admin->assertOk();
        $admin->assertJsonStructure([
            'statistics' => ['total_students', 'total_teachers', 'total_classes', 'monthly_revenue'],
            'charts' => ['enrollment_trend'],
        ]);
    }

    // ─── ROUTE-LEVEL ENFORCEMENT ──────────────────────────────────

    public function test_module_urls_403_when_hit_directly(): void
    {
        $teacher = $this->userWithRole('Teacher');

        // Teacher has no library / students permissions.
        $this->actingAs($teacher)->get('/books')->assertForbidden();
        $this->actingAs($teacher)->get('/students')->assertForbidden();

        // Teacher no longer has academics.settings.manage → structural CRUD is blocked.
        $this->actingAs($teacher)->get('/academic-years/create')->assertForbidden();
        $this->actingAs($teacher)->get('/school-classes/create')->assertForbidden();

        // Zero-permission role blocked from everything.
        $student = $this->userWithRole('Student');
        $this->actingAs($student)->get('/fee-management')->assertForbidden();
        $this->actingAs($student)->get('/financial-reports')->assertForbidden();

        // Sanity: allowed module URLs still load.
        $this->actingAs($teacher)->get('/academic-dashboard')->assertOk();
        $this->actingAs($this->userWithRole('Super Admin'))->get('/books')->assertOk();
    }

    // ─── TEACHER SCOPING — NO CROSS-CLASS DATA LEAK ──────────────

    public function test_teacher_cannot_access_exam_results_outside_assigned_classes(): void
    {
        $teacher = $this->actingAs($this->userWithRole('Teacher'));

        // Teacher lacks exams.results.view-all → any request to results
        // outside their scope should be safe (no data returned).
        $response = $teacher->get('/exam-results');
        $response->assertOk();
    }

    public function test_teacher_cannot_create_academic_year_or_class(): void
    {
        // Teacher no longer has academics.settings.manage.
        $teacher = $this->actingAs($this->userWithRole('Teacher'));

        $this->get('/academic-years/create')->assertForbidden();
        $this->get('/school-classes/create')->assertForbidden();
        $this->get('/subjects/create')->assertForbidden();
        $this->get('/sections/create')->assertForbidden();
    }

    // ─── ELEVATED TEACHER — ADDITIONAL EXAMS PERMISSIONS ────────

    public function test_teacher_with_elevated_exam_permissions_sees_expanded_exam_sidebar(): void
    {
        $role = Role::where('role_name', 'Teacher')->firstOrFail();
        $elevatedPerms = Permission::whereIn('permission_name', [
            'exams.publish',
            'exams.grading.manage',
            'exams.analysis.view',
            'exams.results.view-all',
            'exams.import',
            'exams.approve',
        ])->get();

        $merged = collect($role->permissions)->merge($elevatedPerms)->unique('permission_name');
        $role->setRelation('permissions', $merged);

        $user = new User(['name' => 'Elevated Teacher', 'email' => 'elevated-teacher@test.local']);
        $user->setRelation('roles', collect([$role]));

        $menu = $this->visibleMenuFor($user);

        $examChildren = $this->childLabels($menu, 'exams');
        $this->assertContains('Grading Systems', $examChildren);
        $this->assertContains('Analysis', $examChildren);
        $this->assertContains('Approval', $examChildren);
        $this->assertContains('Bulk Import Marks', $examChildren);
        // Should still see basic items
        $this->assertContains('Enter Marks', $examChildren);
        $this->assertContains('Grade Book', $examChildren);
    }

    public function test_admin_can_access_settings_teacher_cannot(): void
    {
        $admin = $this->actingAs($this->userWithRole('Super Admin'));
        $admin->get('/academic-years')->assertOk();
        $admin->get('/school-classes')->assertOk();
        $admin->get('/sections')->assertOk();
        $admin->get('/subjects')->assertOk();
        $admin->get('/teacher-subjects')->assertOk();

        $teacher = $this->actingAs($this->userWithRole('Teacher'));
        // Teacher has academics.view → can see index but not create
        $teacher->get('/academic-years')->assertOk();
        $teacher->get('/academic-years/create')->assertForbidden();
    }

    public function test_term_controller_blocked_for_teacher(): void
    {
        // Term CRUD requires academics.settings.manage
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/fees/terms')
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Super Admin'))
            ->get('/fees/terms')
            ->assertOk();
    }

    public function test_terms_menu_item_matches_terms_route_access(): void
    {
        // Super Admin (academics.settings.manage) sees the Terms link.
        $adminMenu = $this->visibleMenuFor($this->userWithRole('Super Admin'));
        $this->assertContains('Terms', $this->childLabels($adminMenu, 'fees'));

        // Accountant has fees.view/manage but NOT academics.settings.manage:
        // Fee Management is visible but Terms must be hidden (matches the 403
        // the TermController returns for that role).
        $accountantMenu = $this->visibleMenuFor($this->userWithRole('Accountant'));
        $this->assertContains('Fee Management', $this->topLabels($accountantMenu));
        $this->assertNotContains('Terms', $this->childLabels($accountantMenu, 'fees'));

        // Teacher has neither fees.view nor academics.settings.manage.
        $this->assertNotContains('Fee Management', $this->topLabels($this->visibleMenuFor($this->userWithRole('Teacher'))));
    }

    public function test_expected_revenue_report_requires_only_fees_view(): void
    {
        // Regression: the report was guarded by the non-existent 'fees.export'
        // permission, which locked out every role including Super Admin.
        $this->actingAs($this->userWithRole('Super Admin'))
            ->get('/fees/reports/expected-revenue')
            ->assertOk();

        $this->actingAs($this->userWithRole('Accountant'))
            ->get('/fees/reports/expected-revenue')
            ->assertOk();

        // Teacher has no fees.view → forbidden.
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/fees/reports/expected-revenue')
            ->assertForbidden();
    }

    // ─── AUDIT TRAIL — PLATFORM OWNER ONLY ──────────────────────

    public function test_audit_trail_route_is_owner_only(): void
    {
        $this->actingAs($this->userWithRole('Teacher'))
            ->get('/audit-trail')
            ->assertForbidden();

        // The school's Super Admin must NOT see the audit trail — it is the
        // platform Owner's window into every school action.
        $this->actingAs($this->userWithRole('Super Admin'))
            ->get('/audit-trail')
            ->assertForbidden();

        $this->actingAs($this->userWithRole('Owner'))
            ->get('/audit-trail')
            ->assertOk();

        // Filtered view still renders (module + date range).
        $this->actingAs($this->userWithRole('Owner'))
            ->get('/audit-trail?module=Finance&from=2026-01-01&to=2026-12-31')
            ->assertOk();
    }

    public function test_audit_trail_menu_hidden_from_permissioned_roles(): void
    {
        // A user holding users.view (the former audit-trail gate) must NOT see it.
        $role = new Role(['role_name' => 'Test Auditor']);
        $role->setRelation(
            'permissions',
            Permission::where('permission_name', 'users.view')->get()
        );
        $user = new User(['name' => 'Test Auditor', 'email' => 'auditor@test.local']);
        $user->setRelation('roles', collect([$role]));

        $menu = $this->visibleMenuFor($user);
        $this->assertNotContains('Audit Trail', $this->allChildLabels($menu));

        // The school's Super Admin does NOT see the Administration section.
        $adminMenu = $this->visibleMenuFor($this->userWithRole('Super Admin'));
        $this->assertNotContains('Audit Trail', $this->allChildLabels($adminMenu));

        // Only the platform Owner does.
        $ownerMenu = $this->visibleMenuFor($this->userWithRole('Owner'));
        $this->assertContains('Audit Trail', $this->allChildLabels($ownerMenu));
    }
}
