<?php

namespace Tests\Feature;

use App\Models\Staff;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Super-role access.
 *
 * Regression coverage for the reported "Owner cannot access /medical-incidents"
 * fault, which also affected Homework and Student Notices. The root cause was
 * never the permission rows — RbacSeeder grants medical.view, homework.* and
 * student-notices.* to Owner, Super Admin and Admin alike. It was that
 * `Gate::before` only fires for dotted ability names, so while the Owner sailed
 * through every `can:some.permission` middleware, any module that also consulted
 * a policy enumerating roles (`['Super Admin', 'Admin', ...]`) refused the
 * Owner, because that list simply did not mention the role.
 *
 * The fix is centralised in config/rbac.php + User::isSuperUser(), and these
 * tests pin the behaviour for every role in the system — the Owner is admitted,
 * Super Admin/Admin are unaffected, and the operational roles (Teacher, Parent,
 * Student) stay refused.
 */
class SuperRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private User $superAdmin;
    private User $admin;
    private User $teacher;
    private User $parent;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->owner      = $this->userWithRole('Owner');
        $this->superAdmin = $this->userWithRole('Super Admin');
        $this->admin      = $this->userWithRole('Admin');
        $this->teacher    = $this->userWithRole('Teacher');
        $this->parent     = $this->userWithRole('Parent');
        $this->student    = $this->userWithRole('Student');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user->fresh();
    }

    // ---------------------------------------------------------------------
    // Gate level
    // ---------------------------------------------------------------------

    /**
     * @test
     */
    public function the_owner_is_recognised_as_a_super_user(): void
    {
        $this->assertTrue($this->owner->isSuperUser());
        $this->assertTrue($this->owner->hasPermission('medical.view'));
        $this->assertTrue($this->owner->hasPermission('homework.manage'));
        $this->assertTrue($this->owner->hasPermission('student-notices.view'));

        // Even a permission the Owner has no pivot row for at all. This is the
        // guarantee the role must carry module-wide, not per-slug.
        $this->assertTrue($this->owner->hasPermission('a.permission.that.does.not.exist'));
    }

    /**
     * @test
     */
    public function no_operational_role_is_treated_as_a_super_user(): void
    {
        $this->assertFalse($this->superAdmin->isSuperUser());
        $this->assertFalse($this->admin->isSuperUser());
        $this->assertFalse($this->teacher->isSuperUser());
        $this->assertFalse($this->parent->isSuperUser());
        $this->assertFalse($this->student->isSuperUser());
    }

    /**
     * @test
     */
    public function the_owner_is_admitted_by_the_ability_gate(): void
    {
        foreach (['medical.view', 'homework.view', 'student-notices.view', 'academics.view'] as $ability) {
            $this->assertTrue(
                Gate::forUser($this->owner)->allows($ability),
                "Expected the Owner to be allowed [{$ability}]."
            );
        }
    }

    // ---------------------------------------------------------------------
    // The three reported modules
    // ---------------------------------------------------------------------

    /**
     * @test
     * @dataProvider superRoleProvider
     */
    public function super_roles_can_open_the_medical_incident_log(string $role): void
    {
        $this->actingAs($this->{$this->propertyForRole($role)})
            ->get(route('medical-incidents.index'))
            ->assertOk();
    }

    /**
     * @test
     * @dataProvider superRoleProvider
     */
    public function super_roles_can_open_homework(string $role): void
    {
        $this->actingAs($this->{$this->propertyForRole($role)})
            ->get(route('homework.index'))
            ->assertOk();
    }

    /**
     * @test
     * @dataProvider superRoleProvider
     */
    public function super_roles_can_open_student_notices(string $role): void
    {
        $this->actingAs($this->{$this->propertyForRole($role)})
            ->get(route('student-notices.index'))
            ->assertOk();
    }

    public static function superRoleProvider(): array
    {
        return [
            'owner'      => ['owner'],
            'super admin' => ['superAdmin'],
            'admin'      => ['admin'],
        ];
    }

    /**
     * @test
     * @dataProvider operationalRoleProvider
     */
    public function operational_roles_are_refused_the_medical_incident_log(string $role): void
    {
        $this->actingAs($this->{$this->propertyForRole($role)})
            ->get(route('medical-incidents.index'))
            ->assertForbidden();
    }

    /**
     * Parent and Student hold `homework.view` / `student-notices.view` by design
     * — their controllers scope the listing to their own children and to
     * themselves. So the module is reachable for them; what must stay closed is
     * the write side. Asserting a flat 403 here would be asserting the wrong
     * thing and would push a future fix towards over-restricting the portal.
     *
     * @test
     * @dataProvider portalRoleProvider
     */
    public function portal_roles_may_read_but_not_write_homework_and_notices(string $role): void
    {
        $user = $this->{$this->propertyForRole($role)};

        $this->actingAs($user)->get(route('homework.index'))->assertOk();
        $this->actingAs($user)->get(route('student-notices.index'))->assertOk();

        $this->actingAs($user)->get(route('homework.create'))->assertForbidden();
        $this->actingAs($user)->get(route('student-notices.create'))->assertForbidden();
    }

    public static function portalRoleProvider(): array
    {
        return [
            'parent' => ['parent'],
            'student' => ['student'],
        ];
    }

    /**
     * A teacher runs homework and notices for their own classes, so the write
     * side is legitimately theirs. The medical log, however, is not: no
     * operational role holds `medical.view` and none should gain it here.
     *
     * @test
     */
    public function a_teacher_may_write_homework_but_not_open_the_medical_log(): void
    {
        $this->actingAs($this->teacher)->get(route('homework.create'))->assertOk();
        $this->actingAs($this->teacher)->get(route('student-notices.create'))->assertOk();
        $this->actingAs($this->teacher)->get(route('medical-incidents.index'))->assertForbidden();
    }

    public static function operationalRoleProvider(): array
    {
        return [
            'teacher' => ['teacher'],
            'parent'  => ['parent'],
            'student' => ['student'],
        ];
    }

    // ---------------------------------------------------------------------
    // Teacher timetable
    // ---------------------------------------------------------------------

    /**
     * @test
     * @dataProvider superRoleProvider
     */
    public function super_roles_may_open_the_teacher_timetable(string $role): void
    {
        $teacher = $this->makeTeacher('Wanjiku');

        $response = $this->actingAs($this->{$this->propertyForRole($role)})
            ->get(route('timetables.teacher', ['staff_id' => $teacher->staff_id]));

        $response->assertOk();

        // The picker is only rendered for a user allowed to choose a teacher.
        // Its absence was the visible symptom of the Owner being routed into
        // the "this is my own timetable" branch.
        $response->assertSee('Wanjiku');
    }

    /**
     * @test
     */
    public function a_teacher_may_open_their_own_timetable(): void
    {
        $this->makeTeacher('Otieno', $this->teacher);

        $this->actingAs($this->teacher)
            ->get(route('timetables.teacher'))
            ->assertOk()
            ->assertSee('My Teaching Schedule');
    }

    /**
     * `?staff_id=` is only honoured for a user allowed to pick a teacher. A
     * teacher passing someone else's id must not be escalated into that
     * teacher's schedule — the controller pins the id to the signed-in staff
     * record in the non-admin branch.
     *
     * @test
     */
    public function a_teacher_cannot_promote_themselves_to_another_teachers_timetable(): void
    {
        $this->makeTeacher('Otieno', $this->teacher);
        $someonesElse = $this->makeTeacher('Wanyama');

        $response = $this->actingAs($this->teacher)
            ->get(route('timetables.teacher', ['staff_id' => $someonesElse->staff_id]));

        $response->assertOk();
        $response->assertSee('My Teaching Schedule');
        $response->assertDontSee('Wanyama');
    }

    /**
     * A signed-in user with no staff record at all is told so, rather than
     * being handed a blank schedule that looks like "you have no lessons".
     *
     * @test
     */
    public function a_user_without_a_staff_record_is_told_so(): void
    {
        $this->actingAs($this->parent)
            ->get(route('timetables.teacher'))
            ->assertOk()
            ->assertSee('You are not registered as a staff member');
    }

    // ---------------------------------------------------------------------

    private function propertyForRole(string $role): string
    {
        return match ($role) {
            'owner'      => 'owner',
            'superAdmin' => 'superAdmin',
            'admin'      => 'admin',
            'teacher'    => 'teacher',
            'parent'     => 'parent',
            'student'    => 'student',
        };
    }

    private function makeTeacher(string $lastName, ?User $user = null): Staff
    {
        static $seq = 0;
        $seq++;

        return Staff::create([
            'user_id'           => $user?->id,
            'first_name'        => 'Test',
            'last_name'         => $lastName,
            'date_of_birth'     => '1990-01-01',
            'gender'            => 'male',
            'phone_primary'     => '0700' . str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
            'work_email'        => 'staff' . $seq . '.' . uniqid() . '@test.local',
            'current_address'   => '',
            'city'              => '',
            'country'           => '',
            'designation'       => 'Teacher',
            'date_of_joining'   => now()->toDateString(),
            'staff_type'        => 'teaching',
            'employment_type'   => 'full_time',
            'employment_status' => 'active',
        ]);
    }
}
