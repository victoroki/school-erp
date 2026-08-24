<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Permission;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Staff;
use App\Models\User;
use App\Models\UserRole;
use App\Services\MenuService;
use App\Services\ModuleManager;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Teacher Management — teaching-staff CRUD, shared onboarding flow and the
 * module gate that hides the screen + blocks its routes when disabled.
 *
 * Runs against the isolated school_erp_test database. Hermetic: seeds RBAC
 * and module tables when missing, creates fixture users per role, and resets
 * module state at the start of every test.
 */
class TeacherManagementTest extends TestCase
{
    private const FIXTURE_EMAILS = [
        'Super Admin' => 'teacher-mgmt-superadmin@test.local',
    ];

    private const TEACHER_EMAILS = [
        'onboarded' => 'teacher-mgmt-onboarded@test.local',
        'seeded' => 'teacher-mgmt-seeded@test.local',
        'non-teaching' => 'teacher-mgmt-nonteaching@test.local',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        if (Schema::hasTable('roles')) {
            $emails = array_merge(array_values(self::FIXTURE_EMAILS), array_values(self::TEACHER_EMAILS));
            $userIds = User::whereIn('email', $emails)->pluck('id');
            UserRole::whereIn('user_id', $userIds)->delete();
            Staff::withTrashed()->whereIn('user_id', $userIds)->forceDelete();
            User::whereIn('id', $userIds)->delete();
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
        $emails = array_merge(array_values(self::FIXTURE_EMAILS), array_values(self::TEACHER_EMAILS));
        $userIds = User::whereIn('email', $emails)->pluck('id');
        UserRole::whereIn('user_id', $userIds)->delete();
        Staff::withTrashed()->whereIn('user_id', $userIds)->forceDelete();
        User::whereIn('id', $userIds)->delete();
        parent::tearDown();
    }

    private function userWithRole(string $roleName): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => self::FIXTURE_EMAILS[$roleName]],
            ['name' => "Teacher Mgmt Test {$roleName}", 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->load('roles.permissions');
    }

    private function admin(): User
    {
        return $this->userWithRole('Super Admin');
    }

    private function makeUser(string $email, string $roleName = 'Teacher'): User
    {
        $user = User::create([
            'name' => 'Seeded Teacher',
            'email' => $email,
            'password' => bcrypt('password'),
            'user_type' => 'staff',
            'is_active' => true,
        ]);
        $user->roles()->sync([Role::where('role_name', $roleName)->firstOrFail()->role_id]);

        return $user;
    }

    private function makeStaff(array $overrides = []): Staff
    {
        $defaults = [
            'first_name' => 'Jane',
            'middle_name' => null,
            'last_name' => 'Doe',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'phone_primary' => '0711000000',
            'work_email' => 'jane.doe@school.test',
            'personal_email' => null,
            'current_address' => '',
            'city' => '',
            'country' => '',
            'employee_number' => null,
            'tsc_number' => null,
            'designation' => null,
            'qualification' => null,
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ];

        $input = array_merge($defaults, $overrides);
        $input['work_email'] = preg_replace('/@.*/', '', $input['work_email']).'.'.uniqid().'@test.local';

        return Staff::create($input);
    }

    private function visibleMenuFor(User $user): array
    {
        Auth::setUser($user->loadMissing('roles.permissions'));

        return app(MenuService::class)->getVisibleMenu();
    }

    private function academicsChildLabels(array $menu): array
    {
        $parent = collect($menu)->firstWhere('key', 'academics');
        $this->assertNotNull($parent, 'Expected academics section in visible menu');

        return array_values(array_map(
            fn ($c) => $c['label'],
            array_filter($parent['children'], fn ($c) => ! isset($c['header']))
        ));
    }

    private function validOnboardingPayload(): array
    {
        return [
            'first_name' => 'Grace',
            'middle_name' => '',
            'last_name' => 'Njoroge',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'phone_primary' => '0722000000',
            'work_email' => 'grace.njoroge@test.local',
            'personal_email' => '',
            'current_address' => 'Nairobi',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'employee_number' => 'T-1001',
            'tsc_number' => 'TSC-9001',
            'designation' => 'Mathematics Teacher',
            'qualification' => 'B.Ed Mathematics',
            'date_of_joining' => '2025-01-15',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'login_email' => self::TEACHER_EMAILS['onboarded'],
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    // ─── INDEX SCOPING ────────────────────────────────────────────

    public function test_index_lists_only_teaching_staff(): void
    {
        $teaching = $this->makeStaff([
            'user_id' => $this->makeUser(self::TEACHER_EMAILS['seeded'])->id,
            'last_name' => 'Kamau',
            'tsc_number' => 'TSC-1000',
        ]);
        $this->makeStaff([
            'staff_type' => 'non-teaching',
            'last_name' => 'Onyango',
        ]);

        $this->actingAs($this->admin())
            ->get('/teacher-management')
            ->assertOk()
            ->assertSee('Kamau')
            ->assertDontSee('Onyango');

        $this->assertDatabaseHas('staff', [
            'staff_id' => $teaching->staff_id,
            'staff_type' => 'teaching',
        ]);
    }

    // ─── ONBOARDING SHARED FLOW ───────────────────────────────────

    public function test_onboarding_creates_staff_user_and_role_and_lists_teacher(): void
    {
        $this->actingAs($this->admin())
            ->post('/teacher-onboarding', $this->validOnboardingPayload())
            ->assertRedirect(route('teacher-onboarding.create'));

        $this->assertDatabaseHas('users', ['email' => self::TEACHER_EMAILS['onboarded']]);
        $this->assertDatabaseHas('staff', [
            'work_email' => 'grace.njoroge@test.local',
            'staff_type' => 'teaching',
            'tsc_number' => 'TSC-9001',
        ]);

        $user = User::where('email', self::TEACHER_EMAILS['onboarded'])->firstOrFail();
        $this->assertTrue($user->hasRole('Teacher'));

        $this->actingAs($this->admin())
            ->get('/teacher-management')
            ->assertOk()
            ->assertSee('Njoroge');
    }

    // ─── UPDATE SCOPE ─────────────────────────────────────────────

    public function test_update_edits_staff_fields_without_touching_linked_user(): void
    {
        $user = $this->makeUser(self::TEACHER_EMAILS['seeded']);
        $teacher = $this->makeStaff([
            'user_id' => $user->id,
            'designation' => 'Science Teacher',
        ]);

        $this->actingAs($this->admin())
            ->put('/teacher-management/'.$teacher->staff_id, [
                'first_name' => 'Jane',
                'middle_name' => '',
                'last_name' => 'Mwangi',
                'date_of_birth' => '2000-01-01',
                'gender' => 'female',
                'phone_primary' => '0733000000',
                'work_email' => $teacher->work_email,
                'personal_email' => '',
                'current_address' => '',
                'city' => 'Kisumu',
                'country' => 'Kenya',
                'employee_number' => $teacher->employee_number,
                'tsc_number' => 'TSC-2000',
                'designation' => 'Physics Teacher',
                'qualification' => 'M.Sc Physics',
                'date_of_joining' => $teacher->date_of_joining->toDateString(),
                'employment_type' => 'full_time',
                'employment_status' => 'active',
            ])
            ->assertRedirect(route('teacher-management.show', $teacher->staff_id));

        $teacher->refresh();
        $this->assertSame('Physics Teacher', $teacher->designation);
        $this->assertSame('TSC-2000', $teacher->tsc_number);
        $this->assertSame('Kisumu', $teacher->city);

        // The linked user account and its role are untouched.
        $user->refresh();
        $this->assertSame('Seeded Teacher', $user->name);
        $this->assertTrue($user->hasRole('Teacher'));
    }

    // ─── MODULE GATE ──────────────────────────────────────────────

    public function test_disabling_module_hides_sidebar_child_and_blocks_routes(): void
    {
        $manager = app(ModuleManager::class);
        $admin = $this->admin();

        // Enabled by default: child visible, routes live.
        $this->assertContains('Teacher Management', $this->academicsChildLabels($this->visibleMenuFor($admin)));
        $this->actingAs($admin)->get('/teacher-management')->assertOk();

        $manager->toggle('academic-teacher-management', false);

        // Sidebar child hidden; the rest of Academic Management stays.
        $labels = $this->academicsChildLabels($this->visibleMenuFor($admin));
        $this->assertNotContains('Teacher Management', $labels);
        $this->assertContains('Teacher Onboarding', $labels);
        $this->assertContains('Academic Years', $labels);

        // Routes 404 while other modules keep working.
        $this->actingAs($admin)->get('/teacher-management')->assertNotFound();
        $this->actingAs($admin)->get('/teacher-management/1/edit')->assertNotFound();
        $this->actingAs($admin)->get('/staff')->assertOk();
        $this->actingAs($admin)->get('/teacher-onboarding')->assertOk();

        $manager->toggle('academic-teacher-management', true);

        $this->assertContains('Teacher Management', $this->academicsChildLabels($this->visibleMenuFor($admin)));
        $this->actingAs($admin)->get('/teacher-management')->assertOk();
    }

    public function test_non_teaching_staff_are_not_managed(): void
    {
        $nonTeaching = $this->makeStaff([
            'staff_type' => 'non-teaching',
            'last_name' => 'Otieno',
        ]);

        $this->actingAs($this->admin())
            ->get('/teacher-management/'.$nonTeaching->staff_id)
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->get('/teacher-management/'.$nonTeaching->staff_id.'/edit')
            ->assertNotFound();

        $this->actingAs($this->admin())
            ->delete('/teacher-management/'.$nonTeaching->staff_id)
            ->assertNotFound();

        $this->assertDatabaseHas('staff', ['staff_id' => $nonTeaching->staff_id]);
    }
}
