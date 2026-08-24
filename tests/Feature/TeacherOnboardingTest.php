<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;

class TeacherOnboardingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->superAdmin = User::factory()->create([
            'name'  => 'Super Admin',
            'email' => 'teacher-onboarding-sa@test.local',
        ]);
        $role = Role::where('role_name', 'Super Admin')->firstOrFail();
        $this->superAdmin->roles()->sync([$role->role_id]);
    }

    private function validPayload(): array
    {
        return [
            'first_name'    => 'Grace',
            'middle_name'   => 'Wanjiru',
            'last_name'     => 'Njeri',
            'date_of_birth' => '1995-04-12',
            'gender'        => 'female',
            'phone_primary' => '0712345678',
            'work_email'    => 'grace.njeri@school.test',
            'employee_number' => 'T-2099',
            'tsc_number'    => 'TSC123456',
            'designation'   => 'Mathematics Teacher',
            'date_of_joining' => '2024-01-15',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'login_email'   => 'grace.njeri@school.test',
            'password'      => 'secretpass123',
            'password_confirmation' => 'secretpass123',
        ];
    }

    public function test_create_form_is_reachable_by_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('teacher-onboarding.create'))
            ->assertOk()
            ->assertSee('Teacher Onboarding');
    }

    public function test_onboards_staff_user_and_role_in_one_submission(): void
    {
        $teacherRole = Role::where('role_name', 'Teacher')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->post(route('teacher-onboarding.store'), $this->validPayload())
            ->assertRedirect(route('teacher-onboarding.create'));

        // Staff record created, linked to the user, marked teaching/active.
        $staff = Staff::where('work_email', 'grace.njeri@school.test')->first();
        $this->assertNotNull($staff);
        $this->assertSame('teaching', $staff->staff_type);
        $this->assertSame('active', $staff->employment_status);
        $this->assertSame('Mathematics Teacher', $staff->designation);
        $this->assertSame('TSC123456', $staff->tsc_number);

        // User created with hashed password and linked staff.
        $user = User::where('email', 'grace.njeri@school.test')->first();
        $this->assertNotNull($user);
        $this->assertSame('Grace Wanjiru Njeri', $user->name);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secretpass123', $user->password));
        $this->assertSame($staff->staff_id, $user->staff->staff_id);

        // Teacher role assigned (default when role_id omitted).
        $this->assertTrue($user->hasRole('Teacher'));
        $this->assertTrue($teacherRole->role_id === $user->roles->first()->role_id);
    }

    public function test_role_is_locked_to_teacher_ignoring_requested_role(): void
    {
        $accountantRole = Role::where('role_name', 'Accountant')->firstOrFail();
        $teacherRole = Role::where('role_name', 'Teacher')->firstOrFail();
        $payload = $this->validPayload();
        $payload['role_id'] = $accountantRole->role_id;
        $payload['login_email'] = 'grace.njeri2@school.test';
        $payload['work_email'] = 'grace.njeri2@school.test';
        $payload['password_confirmation'] = $payload['password'] = 'secretpass123';

        $this->actingAs($this->superAdmin)
            ->post(route('teacher-onboarding.store'), $payload)
            ->assertRedirect(route('teacher-onboarding.create'));

        // The requested role must be ignored: the user is always a Teacher.
        $user = User::where('email', 'grace.njeri2@school.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole('Teacher'));
        $this->assertFalse($user->hasRole('Accountant'));
        $this->assertSame(1, $user->roles()->count());
        $this->assertSame($teacherRole->role_id, $user->roles->first()->role_id);
    }

    public function test_rejects_duplicate_login_email(): void
    {
        $payload = $this->validPayload();
        $this->actingAs($this->superAdmin)->post(route('teacher-onboarding.store'), $payload);
        $this->assertDatabaseHas('users', ['email' => 'grace.njeri@school.test']);

        $payload['employee_number'] = 'T-3000';
        $this->actingAs($this->superAdmin)
            ->post(route('teacher-onboarding.store'), $payload)
            ->assertSessionHasErrors(['login_email']);

        $this->assertSame(1, User::where('email', 'grace.njeri@school.test')->count());
        $this->assertSame(1, Staff::where('work_email', 'grace.njeri@school.test')->count());
    }

    public function test_requires_permission_to_access(): void
    {
        // A user with no roles cannot view or submit the form.
        $plain = User::factory()->create(['email' => 'plain-user@test.local']);

        $this->actingAs($plain)
            ->get(route('teacher-onboarding.create'))
            ->assertForbidden();

        $this->actingAs($plain)
            ->post(route('teacher-onboarding.store'), $this->validPayload())
            ->assertForbidden();
    }
}
