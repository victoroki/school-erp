<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Cash collection flow (Fee Management -> Collect Payment).
 *
 * Regression: the collect form posted StudentFeeAssignment::student_fee_assignment_id,
 * an attribute that does not exist on the model (the PK is `id`), so every
 * payment attempt died in StudentFeeAssignment::findOrFail() and no cash was
 * ever recorded. The Fee Management module then appeared to be about fee
 * assignment rather than collection.
 */
class FeeCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function accountant(): User
    {
        $user = User::factory()->create(['name' => 'Fee Collection Accountant']);
        $user->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));
        $user->staff()->create([
            'first_name' => 'Fee',
            'middle_name' => null,
            'last_name' => 'Accountant',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'phone_primary' => '0711000000',
            'work_email' => 'fee-accountant.' . uniqid() . '@test.local',
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
        return $user->load('roles.permissions', 'staff');
    }

    private function teacher(): User
    {
        $user = User::factory()->create(['name' => 'Fee Collection Teacher']);
        $user->roles()->sync(Role::where('role_name', 'Teacher')->pluck('role_id'));
        return $user->load('roles.permissions');
    }

    private function createAcademicYear(): AcademicYear
    {
        return AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
    }

    private function createStudent(): Student
    {
        $year = $this->createAcademicYear();
        $class = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);
        $section = Section::create(['name' => 'A']);
        $classSection = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $student = Student::create([
            'admission_no' => 'ADM-' . uniqid(),
            'first_name' => 'Collection',
            'last_name' => 'Test',
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
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

    private function createAssignment(Student $student, float $finalAmount, ?User $user = null): StudentFeeAssignment
    {
        $category = \App\Models\FeeCategory::create([
            'name' => 'Category-' . uniqid(),
            'type' => 'mandatory',
        ]);

        $structure = FeeStructure::create([
            'academic_year_id' => $student->studentClassEnrollments->first()->academic_year_id,
            'class_id' => $student->studentClassEnrollments->first()->classSection->class_id,
            'category_id' => $category->category_id,
            'amount' => $finalAmount,
            'term' => 'Term 1',
            'payment_frequency' => 'termly',
            'due_date' => now()->addDays(30),
            'status' => 'active',
            'created_by' => ($user ?? $this->accountant())->id,
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $structure->academic_year_id,
            'term' => 'Term 1',
            'amount' => $finalAmount,
            'final_amount' => $finalAmount,
            'assigned_by' => ($user ?? $this->accountant())->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);
    }

    public function test_collect_payment_page_lists_the_real_assignment_ids(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $this->actingAs($accountant)
            ->get("/fee-management/{$student->student_id}/collect-payment")
            ->assertOk()
            ->assertSee('value="' . $assignment->id . '"', false);
    }

    public function test_collect_fees_page_searches_students_and_links_to_collect_payment(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $this->createAssignment($student, 10000, $accountant);

        $this->actingAs($accountant)
            ->get('/fees/collect')
            ->assertOk()
            ->assertSee('Search Student');

        $this->actingAs($accountant)
            ->get('/fees/collect?q=' . urlencode($student->admission_no))
            ->assertOk()
            ->assertSee($student->full_name, false)
            ->assertSee(route('fee-management.collect-payment', $student->student_id), false);
    }

    public function test_collect_fees_page_requires_fees_collect_permission(): void
    {
        $teacher = $this->teacher();

        $this->actingAs($teacher)
            ->get('/fees/collect')
            ->assertForbidden();
    }

    public function test_single_fee_payment_is_recorded(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $accountant);

        $this->actingAs($accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 4000,
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'transaction_id' => 'TXN-001',
                'remarks' => 'First installment',
            ])
            ->assertRedirect(route('fee-management.show', $student->student_id));

        $this->assertDatabaseHas('fee_payments', [
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 4000,
            'payment_method' => 'cash',
        ]);
        $this->assertDatabaseHas('student_fee_assignments', [
            'id' => $assignment->id,
            'paid_amount' => 4000,
        ]);
    }

    public function test_total_payment_distributes_across_outstanding_fees(): void
    {
        $accountant = $this->accountant();
        $student = $this->createStudent();
        $first = $this->createAssignment($student, 10000, $accountant);
        $second = $this->createAssignment($student, 6000, $accountant);

        $this->actingAs($accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => 'total',
                'amount' => 12000,
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'online',
                'remarks' => 'Total settlement',
            ])
            ->assertRedirect(route('fee-management.show', $student->student_id));

        $this->assertSame('12000.00', FeePayment::sum('amount'));
        $this->assertDatabaseHas('student_fee_assignments', ['id' => $first->id, 'paid_amount' => 10000]);
        $this->assertDatabaseHas('student_fee_assignments', ['id' => $second->id, 'paid_amount' => 2000]);
    }

    public function test_collect_payment_requires_fees_collect_permission(): void
    {
        $teacher = $this->teacher();
        $student = $this->createStudent();
        $assignment = $this->createAssignment($student, 10000, $this->accountant());

        $this->actingAs($teacher)
            ->get("/fee-management/{$student->student_id}/collect-payment")
            ->assertForbidden();

        $this->actingAs($teacher)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 1000,
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
            ])
            ->assertForbidden();

        $this->assertSame(0, FeePayment::count());
    }
}
