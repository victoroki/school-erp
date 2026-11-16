<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * "Apply for Leave" on the leave index used to render for every user who could
 * reach the page, but create() needs a `staff` row: leave balances and
 * applications are keyed to staff_id. Every admin/owner account in this
 * installation has no staff record, so the click 302'd straight back to the page
 * it came from — and because the leave views never rendered `flash::message`,
 * nothing explained why. The button simply appeared dead.
 *
 * @see HrUiConsistencyTest for the Bootstrap 4 dropdown guards.
 */
class HrLeaveApplyButtonTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_for_leave_is_hidden_from_a_user_with_no_staff_record(): void
    {
        $this->seedRbac();

        $admin = $this->userWithRole('Admin', 'hr.leave.admin');

        $this->assertDatabaseMissing('staff', ['user_id' => $admin->id]);

        $html = $this->actingAs($admin)
            ->get(route('leave-applications.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(
            route('leave-applications.create'),
            $html,
            'The Apply for Leave link is shown to a user with no staff record, but create() bounces them '
                .'straight back, so the button does nothing.'
        );

        $this->assertStringContainsString(
            'You need a staff record before you can apply for leave.',
            $html,
            'The button should be replaced by an explanation of why it is unavailable.'
        );
    }

    public function test_apply_for_leave_still_works_for_a_staff_member_who_can_use_it(): void
    {
        $this->seedRbac();

        $teacher = $this->userWithRole('Teacher', 'hr.leave.teacher');
        $this->staffFor($teacher);

        $html = $this->actingAs($teacher)
            ->get(route('leave-applications.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            route('leave-applications.create'),
            $html,
            'A teacher with a staff record and hr.leave.apply must still get the Apply for Leave button.'
        );

        $this->actingAs($teacher)->get(route('leave-applications.create'))->assertOk();
    }

    private function seedRbac(): void
    {
        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }
    }

    private function userWithRole(string $roleName, string $key): User
    {
        $user = User::firstOrCreate(
            ['email' => $key.'@example.test'],
            ['name' => $key, 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching(Role::where('role_name', $roleName)->firstOrFail());

        return $user->load('roles.permissions');
    }

    /**
     * staff has NOT NULL columns with no default, so the fixture has to fill
     * every one of them or the insert fails before the test can say anything.
     */
    private function staffFor(User $user): Staff
    {
        return Staff::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-'.$user->id,
            'first_name' => 'Ui',
            'last_name' => 'Tester',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'date_of_joining' => '2020-01-01',
            'work_email' => $user->email,
            'phone_primary' => '0700000000',
            'current_address' => 'Somewhere',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'department_id' => Department::create([
                'name' => 'UI Dept '.uniqid(),
                'code' => 'UI'.uniqid(),
            ])->department_id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'staff_type' => 'teaching',
        ]);
    }
}
