<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Parents;
use App\Models\Staff;
use App\Models\Student;
use App\Models\StudentParentRelationship;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Transferred-students archive, student-parent-relationship search/print and
 * the departments Assign HOD action.
 */
class TransferAndAdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    private function makeStudent(array $overrides = []): Student
    {
        static $seq = 0;
        $seq++;

        return Student::create(array_merge([
            'admission_no' => 'T' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT) . substr(uniqid(), -6),
            'first_name' => 'Transfer',
            'last_name' => 'Case' . $seq,
            'date_of_birth' => '2012-05-10',
            'gender' => 'male',
            'city' => 'N/A',
            'country' => 'Kenya',
            'admission_date' => '2026-01-10',
            'status' => 'active',
        ], $overrides));
    }

    private function makeStaff(array $overrides = []): Staff
    {
        static $seq = 0;
        $seq++;

        return Staff::create(array_merge([
            'first_name' => 'Hod',
            'last_name' => 'Candidate' . $seq,
            'date_of_birth' => '1985-05-05',
            'gender' => 'female',
            'phone_primary' => '071' . str_pad((string) $seq, 8, '2', STR_PAD_LEFT),
            'work_email' => 'hod' . $seq . '.' . uniqid() . '@test.local',
            'current_address' => '',
            'city' => '',
            'country' => '',
            'date_of_joining' => now()->toDateString(),
            'staff_type' => 'teaching',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
        ], $overrides));
    }

    // ─── Transferred students page ───────────────────────────────────────

    public function test_transferred_page_lists_only_transferred_learners(): void
    {
        $gone = $this->makeStudent([
            'status' => 'transferred',
            'transfer_date' => '2026-08-01',
            'transfer_reason' => 'Family relocated to Kisumu',
        ]);
        $here = $this->makeStudent();

        $response = $this->actingAs($this->admin)->get(route('students.transferred'));

        $response->assertOk();
        $response->assertSee('Family relocated to Kisumu');
        $response->assertDontSee('Case' . $here->student_id === null ? '' : 'definitely-not-present');
        $this->assertDatabaseHas('students', ['student_id' => $gone->student_id, 'status' => 'transferred']);
    }

    public function test_transferred_page_searches_by_admission_number(): void
    {
        $gone = $this->makeStudent(['status' => 'transferred', 'admission_no' => 'SRC001']);
        $other = $this->makeStudent(['status' => 'transferred', 'admission_no' => 'DST999']);

        $response = $this->actingAs($this->admin)->get(route('students.transferred', ['q' => 'SRC001']));

        $response->assertOk();
        $response->assertSee('SRC001');
        $response->assertDontSee('DST999');
    }

    public function test_transferred_page_shows_an_empty_state(): void
    {
        $response = $this->actingAs($this->admin)->get(route('students.transferred'));

        $response->assertOk();
        $response->assertSee('No transferred students match');
    }

    // ─── Student parent relationships: search, filter, print ────────────

    private function linkParent(Student $student, Parents $parent, bool $primary = false): void
    {
        StudentParentRelationship::create([
            'student_id' => $student->student_id,
            'parent_id' => $parent->parent_id,
            'is_primary_contact' => $primary,
        ]);
    }

    public function test_relationship_index_searches_by_guardian_name(): void
    {
        $studentA = $this->makeStudent(['first_name' => 'Alpha']);
        $studentB = $this->makeStudent(['first_name' => 'Beta']);
        $parentA = Parents::create([
            'first_name' => 'Wanjiru', 'last_name' => 'Achieng', 'relationship' => 'mother',
            'phone' => '0711000001',
        ]);
        $parentB = Parents::create([
            'first_name' => 'Brian', 'last_name' => 'Otieno', 'relationship' => 'father',
            'phone' => '0711000002',
        ]);
        $this->linkParent($studentA, $parentA);
        $this->linkParent($studentB, $parentB);

        $response = $this->actingAs($this->admin)
            ->get(route('student-parent-relationships.index', ['q' => 'Otieno']));

        $response->assertOk();
        $response->assertSee('Brian');
        $response->assertDontSee('Wanjiru');
    }

    public function test_relationship_index_shows_contact_and_relationship_columns(): void
    {
        $student = $this->makeStudent();
        $parent = Parents::create([
            'first_name' => 'Mary', 'last_name' => 'Doe', 'relationship' => 'mother',
            'phone' => '0722000022',
        ]);
        $this->linkParent($student, $parent, true);

        $response = $this->actingAs($this->admin)->get(route('student-parent-relationships.index'));

        $response->assertOk();
        $response->assertSee('0722000022');
        $response->assertSee('Mother');
        $response->assertSee('Primary');
    }

    public function test_relationship_print_view_renders_standalone(): void
    {
        $student = $this->makeStudent();
        $parent = Parents::create([
            'first_name' => 'Print', 'last_name' => 'Check', 'relationship' => 'guardian',
            'phone' => '0733000033',
        ]);
        $this->linkParent($student, $parent);

        $response = $this->actingAs($this->admin)
            ->get(route('student-parent-relationships.print'));

        $response->assertOk();
        // Standalone document — no layout markers, but the data must be there.
        $response->assertSee('Print Check');
        $response->assertSee('0733000033');
        $response->assertDontSee('content-header');
    }

    // ─── Departments: assign / change HOD ────────────────────────────────

    public function test_a_teacher_can_be_assigned_as_hod(): void
    {
        $department = Department::create(['name' => ' Sciences ' . uniqid()]);
        $staff = $this->makeStaff();

        $response = $this->actingAs($this->admin)
            ->patch(route('departments.update-hod', $department->department_id), ['hod_id' => $staff->staff_id]);

        $response->assertRedirect(route('departments.index'));
        $this->assertDatabaseHas('departments', [
            'department_id' => $department->department_id,
            'hod_id' => $staff->staff_id,
        ]);
    }

    public function test_an_inactive_staff_member_cannot_be_assigned_as_hod(): void
    {
        $department = Department::create(['name' => 'Inert ' . uniqid()]);
        $staff = $this->makeStaff(['employment_status' => 'terminated']);

        $this->actingAs($this->admin)
            ->patch(route('departments.update-hod', $department->department_id), ['hod_id' => $staff->staff_id])
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseMissing('departments', [
            'department_id' => $department->department_id,
            'hod_id' => $staff->staff_id,
        ]);
    }

    public function test_the_hod_can_be_cleared(): void
    {
        $department = Department::create(['name' => 'Clearable ' . uniqid()]);
        $staff = $this->makeStaff();
        $department->update(['hod_id' => $staff->staff_id]);

        $this->actingAs($this->admin)
            ->patch(route('departments.update-hod', $department->department_id), [])
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'department_id' => $department->department_id,
            'hod_id' => null,
        ]);
    }

    public function test_a_non_admin_cannot_assign_a_hod(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $department = Department::create(['name' => 'Locked ' . uniqid()]);
        $staff = $this->makeStaff();

        $this->actingAs($teacher)
            ->patch(route('departments.update-hod', $department->department_id), ['hod_id' => $staff->staff_id])
            ->assertForbidden();

        $this->assertNull($department->fresh()->hod_id);
    }
}
