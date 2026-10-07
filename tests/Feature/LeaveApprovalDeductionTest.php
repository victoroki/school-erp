<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\Staff;
use App\Models\StaffLeaveBalance;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Cover the approve() revamp: HR approval is the single finalizing action
 * and must always deduct the leave balance — regardless of whether the HOD
 * step ran first, and even when no balance row exists yet (auto-created
 * from the leave type's entitlement).
 */
class LeaveApprovalDeductionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
            'is_current' => true,
        ]);

        $this->leaveType = LeaveType::create([
            'name' => 'Annual Leave ' . uniqid(),
            'days_allowed' => 20,
            'status' => 'active',
            'is_paid' => true,
        ]);

        $this->department = Department::create([
            'name' => 'Deduction Dept ' . uniqid(),
            'code' => 'DD' . uniqid(),
        ]);
    }

    private function userWithRole(string $roleName, string $key): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();
        $user = User::firstOrCreate(
            ['email' => $key . '@example.test'],
            ['name' => $key, 'password' => bcrypt('secret')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->load('roles.permissions');
    }

    private function staffFor(User $user, Department $dept): Staff
    {
        return Staff::create([
            'user_id' => $user->id,
            'employee_number' => 'EMP-' . $user->id,
            'first_name' => 'Test',
            'last_name' => 'Person',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '0700000000',
            'date_of_joining' => '2020-01-01',
            'current_address' => 'Somewhere',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'email' => null,
            'work_email' => $user->email,
            'department_id' => $dept->department_id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'staff_type' => 'teaching',
        ]);
    }

    private function application(Staff $applicant): LeaveApplication
    {
        return LeaveApplication::create([
            'staff_id' => $applicant->staff_id,
            'leave_type_id' => $this->leaveType->leave_type_id,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'working_days' => 5,
            'reason' => 'Family commitment requiring travel.',
            'application_status' => 'pending',
            'submitted_date' => now(),
        ]);
    }

    /** @test */
    public function hr_approval_finalizes_and_deducts_without_hod_approval(): void
    {
        $applicant = $this->staffFor($this->userWithRole('Teacher', 'ded.applicant'), $this->department);
        $admin = $this->userWithRole('Admin', 'ded.admin');
        $leave = $this->application($applicant);

        StaffLeaveBalance::create([
            'staff_id' => $applicant->staff_id,
            'leave_type_id' => $this->leaveType->leave_type_id,
            'academic_year_id' => $this->year->academic_year_id,
            'total_entitlement' => 20,
            'total_available' => 20,
            'used' => 0,
            'remaining' => 20,
        ]);

        $this->actingAs($admin)
            ->post(route('leave-applications.approve', $leave->id), ['comments' => 'Ok'])
            ->assertRedirect(route('leave-applications.show', $leave->id));

        $leave->refresh();
        $this->assertSame('approved', $leave->application_status);
        $this->assertSame('approved', $leave->hr_approval_status);
        $this->assertSame('approved', $leave->final_status);

        $balance = StaffLeaveBalance::where('staff_id', $applicant->staff_id)
            ->where('leave_type_id', $this->leaveType->leave_type_id)
            ->where('academic_year_id', $this->year->academic_year_id)
            ->first();
        $this->assertSame(5, $balance->used);
        $this->assertSame(15, $balance->remaining);
    }

    /** @test */
    public function hr_approval_still_deducts_when_hod_already_approved(): void
    {
        $applicant = $this->staffFor($this->userWithRole('Teacher', 'ded.hodfirst'), $this->department);
        $admin = $this->userWithRole('Admin', 'ded.admin2');
        $leave = $this->application($applicant);

        StaffLeaveBalance::create([
            'staff_id' => $applicant->staff_id,
            'leave_type_id' => $this->leaveType->leave_type_id,
            'academic_year_id' => $this->year->academic_year_id,
            'total_entitlement' => 20,
            'total_available' => 20,
            'used' => 0,
            'remaining' => 20,
        ]);

        $leave->update(['hod_approval_status' => 'approved']);

        $this->actingAs($admin)
            ->post(route('leave-applications.approve', $leave->id))
            ->assertRedirect(route('leave-applications.show', $leave->id));

        $leave->refresh();
        $this->assertSame('approved', $leave->application_status);

        $balance = StaffLeaveBalance::where('staff_id', $applicant->staff_id)->first();
        $this->assertSame(5, $balance->used);
        $this->assertSame(15, $balance->remaining);
    }

    /** @test */
    public function approval_without_balance_row_creates_one_and_deducts(): void
    {
        $applicant = $this->staffFor($this->userWithRole('Teacher', 'ded.norow'), $this->department);
        $admin = $this->userWithRole('Admin', 'ded.admin3');
        $leave = $this->application($applicant);

        $this->assertDatabaseCount('staff_leave_balances', 0);

        $this->actingAs($admin)
            ->post(route('leave-applications.approve', $leave->id))
            ->assertRedirect(route('leave-applications.show', $leave->id));

        $balance = StaffLeaveBalance::where('staff_id', $applicant->staff_id)->first();
        $this->assertNotNull($balance, 'Balance row should be auto-created on approval.');
        $this->assertSame(20, $balance->total_entitlement);
        $this->assertSame(20, $balance->total_available);
        $this->assertSame(5, $balance->used);
        $this->assertSame(15, $balance->remaining);
    }

    /** @test */
    public function approver_who_is_the_department_hod_gets_both_badges(): void
    {
        $hodUser = $this->userWithRole('Admin', 'ded.hod.approver');
        $hodStaff = $this->staffFor($hodUser, $this->department);
        $applicant = $this->staffFor($this->userWithRole('Teacher', 'ded.teacher2'), $this->department);
        $this->department->update(['hod_id' => $hodStaff->staff_id]);
        $leave = $this->application($applicant);

        $this->actingAs($hodUser)
            ->post(route('leave-applications.approve', $leave->id))
            ->assertRedirect(route('leave-applications.show', $leave->id));

        $leave->refresh();
        $this->assertSame('approved', $leave->application_status);
        $this->assertSame('approved', $leave->hod_approval_status);
        $this->assertSame($hodUser->id, (int) $leave->hr_approved_by);
        $this->assertSame($hodStaff->staff_id, $this->department->fresh()->hod_id);

        $balance = StaffLeaveBalance::where('staff_id', $applicant->staff_id)->first();
        $this->assertSame(5, $balance->used);
    }

    /** @test */
    public function already_approved_application_cannot_be_approved_again(): void
    {
        $applicant = $this->staffFor($this->userWithRole('Teacher', 'ded.double'), $this->department);
        $admin = $this->userWithRole('Admin', 'ded.admin4');
        $leave = $this->application($applicant);

        StaffLeaveBalance::create([
            'staff_id' => $applicant->staff_id,
            'leave_type_id' => $this->leaveType->leave_type_id,
            'academic_year_id' => $this->year->academic_year_id,
            'total_entitlement' => 20,
            'total_available' => 20,
            'used' => 0,
            'remaining' => 20,
        ]);

        $this->actingAs($admin)->post(route('leave-applications.approve', $leave->id));

        $this->actingAs($admin)
            ->post(route('leave-applications.approve', $leave->id))
            ->assertRedirect();

        $balance = StaffLeaveBalance::where('staff_id', $applicant->staff_id)->first();
        $this->assertSame(5, $balance->used, 'Second approval must not deduct twice.');
        $this->assertSame(15, $balance->remaining);
    }

    /** @test */
    public function user_without_hr_approve_permission_gets_403(): void
    {
        $applicant = $this->staffFor($this->userWithRole('Teacher', 'ded.noauth'), $this->department);
        $leave = $this->application($applicant);

        $this->actingAs($this->userWithRole('Teacher', 'ded.noauth.approver'))
            ->post(route('leave-applications.approve', $leave->id))
            ->assertStatus(403);

        $leave->refresh();
        $this->assertSame('pending', $leave->application_status);
    }
}
