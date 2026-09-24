<?php

namespace Tests\Feature;

use App\Models\AcademicEvent;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PHASE 3 — ADMIN DASHBOARD + FINANCE SUMMARY API.
 *
 * The admin home is a briefing computed server-side; these tests pin the
 * summary arithmetic (so the app can never disagree with Laravel) and the
 * authorization matrix (STEP 19): accountant gets money endpoints, never
 * the admin dashboard; teacher/parent/student get neither.
 */
class MobileAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    // ── Fixtures ────────────────────────────────────────────────────────────

    private function staffUser(string $roleName): User
    {
        $user = User::factory()->create(['name' => $roleName . ' Admin']);
        $user->roles()->sync(Role::where('role_name', $roleName)->pluck('role_id'));
        $user->staff()->create([
            'first_name' => $roleName,
            'last_name' => 'Tester',
            'date_of_birth' => '1990-01-01',
            'gender' => 'female',
            'phone_primary' => '0712341111',
            'work_email' => strtolower($roleName) . '.staff.' . uniqid() . '@test.local',
            'personal_email' => null,
            'current_address' => '',
            'city' => '',
            'country' => '',
            'employee_number' => null,
            'tsc_number' => null,
            'designation' => null,
            'qualification' => null,
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'non-teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ]);

        return $user->load('roles.permissions');
    }

    private function mobileToken(User $user): string
    {
        // Requests in one test method share the app instance, and Sanctum's
        // guard caches the first resolved user — forget guards so each token
        // below authenticates as ITS OWN user (otherwise an early accountant
        // request would leak its identity into a later teacher assertion).
        $this->app['auth']->forgetGuards();

        return $user->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    private function year(): AcademicYear
    {
        return AcademicYear::firstOrCreate(
            ['name' => '2026'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );
    }

    private function section(string $className = 'Form 1', string $sectionName = 'A'): array
    {
        $year = $this->year();
        $class = SchoolClass::create(['name' => $className, 'numeric_value' => 1]);
        $section = Section::create(['class_id' => $class->class_id, 'name' => $sectionName]);
        $cs = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
        return compact('year', 'cs');
    }

    private function enroll($classSection, AcademicYear $year, string $first = 'Jane'): Student
    {
        $student = Student::create([
            'admission_no' => 'ADM-' . substr(md5((string) uniqid('', true)), 0, 12),
            'first_name' => $first,
            'last_name' => 'Doe',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);
        return $student;
    }

    // ── Access control (STEP 19) ────────────────────────────────────────────

    public function test_admin_dashboard_is_admin_role_only(): void
    {
        ['year' => $year, 'cs' => $cs] = $this->section();
        $this->enroll($cs, $year);

        foreach (['Admin', 'Super Admin', 'Owner'] as $role) {
            $user = $this->staffUser($role);
            $this->withToken($this->mobileToken($user))
                ->getJson('/api/mobile/admin/dashboard')
                ->assertOk()
                ->assertJsonPath('date', now()->toDateString())
                ->assertJsonPath('school.students', 1);
        }
    }

    public function test_teacher_parent_student_and_accountant_cannot_see_admin_dashboard(): void
    {
        // The accountant gets fee endpoints, NOT the school-wide admin
        // briefing — role separation enforced server-side, not hidden in UI.
        foreach (['Teacher', 'Parent', 'Student', 'Accountant'] as $role) {
            $user = $this->staffUser($role);
            $this->withToken($this->mobileToken($user))
                ->getJson('/api/mobile/admin/dashboard')
                ->assertForbidden();
        }
    }

    public function test_finance_summary_requires_fees_view(): void
    {
        $accountant = $this->staffUser('Accountant');
        $this->withToken($this->mobileToken($accountant))
            ->getJson('/api/mobile/finance/summary')
            ->assertOk()
            ->assertJsonStructure(['date', 'finance' => ['collected_today', 'payments_today', 'by_method', 'total_outstanding'], 'recent_payments']);

        $teacher = $this->staffUser('Teacher');
        $this->withToken($this->mobileToken($teacher))
            ->getJson('/api/mobile/finance/summary')
            ->assertForbidden();
    }

    // ── Attendance summary correctness ──────────────────────────────────────

    public function test_attendance_summary_counts_only_counted_students_honest_rate(): void
    {
        $admin = $this->staffUser('Admin');
        ['year' => $year, 'cs' => $cs] = $this->section();
        $s1 = $this->enroll($cs, $year, 'Alpha');
        $s2 = $this->enroll($cs, $year, 'Beta');
        $s3 = $this->enroll($cs, $year, 'Gamma');
        $today = now()->toDateString();

        // s1 present, s2 absent, s3 unmarked.
        foreach ([[$s1, 'present'], [$s2, 'absent']] as [$st, $status]) {
            StudentAttendance::create([
                'student_id' => $st->student_id,
                'class_section_id' => $cs->class_section_id,
                'date' => $today,
                'status' => $status,
            ]);
        }

        $res = $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/admin/dashboard')
            ->assertOk();

        $res->assertJsonPath('attendance.enrolled', 3)
            ->assertJsonPath('attendance.marked', 2)
            ->assertJsonPath('attendance.unmarked', 1)
            ->assertJsonPath('attendance.present', 1)
            ->assertJsonPath('attendance.absent', 1)
            // rate is over counted students: 1 of 2 present = 50%.
            ->assertJsonPath('attendance.attendance_rate', 50);

        // The section still has an outstanding register (s3 unmarked).
        $res->assertJsonPath('pending_registers.0.class_section_id', $cs->class_section_id)
            ->assertJsonPath('pending_registers.0.enrolled', 3)
            ->assertJsonPath('pending_registers.0.marked', 2)
            ->assertJsonPath('pending_registers.0.pending', 1);
    }

    public function test_completed_registers_disappear_from_the_pending_list(): void
    {
        $admin = $this->staffUser('Admin');
        ['year' => $year, 'cs' => $cs] = $this->section();
        $s1 = $this->enroll($cs, $year);
        $today = now()->toDateString();
        StudentAttendance::create([
            'student_id' => $s1->student_id,
            'class_section_id' => $cs->class_section_id,
            'date' => $today,
            'status' => 'present',
        ]);

        $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/admin/dashboard')
            ->assertOk()
            ->assertJsonCount(0, 'pending_registers')
            ->assertJsonPath('attendance.unmarked', 0);
    }

    // ── Staff + leave gating ────────────────────────────────────────────────

    public function test_staff_block_for_admin_uses_real_attendance_and_approved_leave(): void
    {
        $admin = $this->staffUser('Admin');
        $token = $this->mobileToken($admin);
        $today = now()->toDateString();

        $staffA = Staff::create([
            'first_name' => 'Ops', 'last_name' => 'One', 'date_of_birth' => '1985-05-05',
            'gender' => 'male', 'phone_primary' => '0700000001',
            'work_email' => 'ops1.' . uniqid() . '@test.local',
            'current_address' => '', 'city' => '', 'country' => '',
            'date_of_joining' => now()->subYear()->toDateString(),
            'employment_status' => 'active', 'staff_type' => 'teaching', 'employment_type' => 'full_time',
        ]);
        Staff::create([
            'first_name' => 'Ops', 'last_name' => 'Two', 'date_of_birth' => '1985-05-06',
            'gender' => 'male', 'phone_primary' => '0700000002',
            'work_email' => 'ops2.' . uniqid() . '@test.local',
            'current_address' => '', 'city' => '', 'country' => '',
            'date_of_joining' => now()->subYear()->toDateString(),
            'employment_status' => 'active', 'staff_type' => 'teaching', 'employment_type' => 'full_time',
        ]);
        StaffAttendance::create(['staff_id' => $staffA->staff_id, 'date' => $today, 'status' => 'present']);

        $leave = LeaveType::create(['name' => 'Annual-' . uniqid(), 'days_allowed' => 10, 'is_paid' => true]);
        LeaveApplication::create([
            'staff_id' => $staffA->staff_id,
            'leave_type_id' => $leave->leave_type_id,
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'working_days' => 3,
            'reason' => 'Family',
            'application_status' => 'approved',
            'final_status' => 'approved',
        ]);

        $res = $this->withToken($token)->getJson('/api/mobile/admin/dashboard')->assertOk();
        // Admin holds hr.view → staff block present (not the null/unavailable
        // case). active_staff counts the Admin tester's own staff record too.
        $this->assertNotNull($res->json('staff'));
        $res->assertJsonPath('staff.active_staff', 3)
            ->assertJsonPath('staff.present', 1)
            ->assertJsonPath('staff.on_leave', 1)
            ->assertJsonPath('staff.not_recorded', 2);

        // Pending leave shows up as a real task with a real count.
        LeaveApplication::create([
            'staff_id' => $staffA->staff_id,
            'leave_type_id' => $leave->leave_type_id,
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
            'working_days' => 1,
            'reason' => 'Errand',
            'application_status' => 'pending',
        ]);
        $tasks = collect($this->withToken($token)->getJson('/api/mobile/admin/dashboard')->json('tasks'));
        $this->assertEquals(1, $tasks->firstWhere('key', 'pending_leave')['count']);
    }

    // ── Money summary correctness ───────────────────────────────────────────

    public function test_finance_today_block_uses_payment_date_not_created_at(): void
    {
        $admin = $this->staffUser('Admin');
        ['year' => $year, 'cs' => $cs] = $this->section();
        $student = $this->enroll($cs, $year);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $cs->class_id,
            'category_id' => FeeCategory::create(['name' => 'Tuit-' . uniqid(), 'type' => 'mandatory'])->category_id,
            'amount' => 10000, 'term' => 'Term 1', 'payment_frequency' => 'termly',
            'due_date' => now()->addDays(30), 'status' => 'active',
        ]);
        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id, 'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id, 'term' => 'Term 1',
            'amount' => 10000, 'final_amount' => 10000, 'paid_amount' => 5500,
            'assigned_date' => now(), 'status' => 'active',
        ]);

        // A payment recorded YESTERDAY (payment_date) but inserted only now
        // (created_at) — an offline payment that synced late. It must NOT
        // pollute today's collections.
        FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 4000,
            'payment_date' => now()->subDay()->toDateString(),
            'payment_method' => 'cash',
            'receipt_number' => 'RCP-YESTERDAY',
        ]);
        FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 1500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'online',
            'receipt_number' => 'RCP-TODAY',
        ]);

        $res = $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/admin/dashboard')
            ->assertOk();

        $res->assertJsonPath('finance.collected_today', 1500)
            ->assertJsonPath('finance.payments_today', 1)
            ->assertJsonPath('finance.by_method.0.method', 'online')
            ->assertJsonPath('finance.by_method.0.total', 1500)
            ->assertJsonPath('finance.total_expected', 10000)
            ->assertJsonPath('finance.total_collected', 5500)
            ->assertJsonPath('finance.total_outstanding', 4500)
            ->assertJsonPath('finance.students_in_arrears', 1);

        // recent payments carry the server receipts (newest payment_date first).
        $res->assertJsonCount(2, 'recent_payments')
            ->assertJsonPath('recent_payments.0.receipt_number', 'RCP-TODAY')
            ->assertJsonPath('recent_payments.1.receipt_number', 'RCP-YESTERDAY')
            ->assertJsonPath('recent_payments.1.student_name', 'Jane Doe');
    }

    public function test_finance_today_excludes_reversed_payments(): void
    {
        $admin = $this->staffUser('Admin');
        ['year' => $year, 'cs' => $cs] = $this->section();
        $student = $this->enroll($cs, $year);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $cs->class_id,
            'category_id' => FeeCategory::create(['name' => 'Tuit-' . uniqid(), 'type' => 'mandatory'])->category_id,
            'amount' => 3000, 'term' => 'Term 1', 'payment_frequency' => 'termly',
            'due_date' => now()->addDays(30), 'status' => 'active',
        ]);

        // paid_amount stays 0 because LedgerService rebuilds it from the single
        // source of truth on reversal, and FeeBalanceService excludes reversed.
        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id, 'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id, 'term' => 'Term 1',
            'amount' => 3000, 'final_amount' => 3000, 'paid_amount' => 0,
            'assigned_date' => now(), 'status' => 'active',
        ]);

        // Sized so the voided payment would flip the arrears verdict if it were
        // counted: 4000 >= the 3000 due. The array-scope figures and the raw
        // paid_totals subquery must both ignore it.
        FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 4000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'receipt_number' => 'RCP-VOIDED',
            'reversed_at' => now(),
        ]);

        $res = $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/admin/dashboard')
            ->assertOk();

        $res->assertJsonPath('finance.collected_today', 0)
            ->assertJsonPath('finance.payments_today', 0)
            ->assertJsonCount(0, 'finance.by_method')
            ->assertJsonPath('finance.students_in_arrears', 1);

        // Field names and types are unchanged — data correctness only.
        $res->assertJsonStructure(['finance' => ['collected_today', 'payments_today', 'by_method', 'students_in_arrears']]);
    }

    public function test_upcoming_events_and_empty_states_are_honest(): void
    {
        $admin = $this->staffUser('Admin');
        $year = $this->year();

        AcademicEvent::create([
            'title' => 'Speech Day', 'event_type' => 'parent_meeting',
            'start_date' => now()->addDays(3)->toDateString(), 'end_date' => now()->addDays(3)->toDateString(),
            'is_public' => true, 'academic_year_id' => $year->academic_year_id,
        ]);
        // Past event must not appear.
        AcademicEvent::create([
            'title' => 'Old Thing', 'event_type' => 'other',
            'start_date' => now()->subDays(10)->toDateString(), 'end_date' => now()->subDays(10)->toDateString(),
            'is_public' => true, 'academic_year_id' => $year->academic_year_id,
        ]);

        $res = $this->withToken($this->mobileToken($admin))
            ->getJson('/api/mobile/admin/dashboard')
            ->assertOk();

        $res->assertJsonPath('events.0.title', 'Speech Day')->assertJsonCount(1, 'events');

        // No attendance marked yet, but nobody enrolled either... students=0
        // in a fresh section? We enrolled 0 students in THIS test, so the
        // rollup is honestly zero-marked with a null rate (not a fake 100%).
        $res->assertJsonPath('attendance.marked', 0)
            ->assertJsonPath('attendance.attendance_rate', null)
            ->assertJsonPath('finance.collected_today', 0);
    }
}
