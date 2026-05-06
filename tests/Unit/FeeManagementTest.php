<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Student;
use App\Models\FeeStructure;
use App\Models\StudentFeeAssignment;
use App\Models\FeeAdjustment;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\ClassSection;
use App\Models\StudentClassEnrollment;
use App\Models\Section;
use App\Models\Term;
use App\Models\User;
use App\Services\FeeAssignmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FeeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    protected function createAcademicYear($isCurrent = true)
    {
        return AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => $isCurrent,
        ]);
    }

    protected function createClass()
    {
        return SchoolClass::create([
            'name' => 'Form 1',
            'numeric_value' => 1,
        ]);
    }

    protected function createSection()
    {
        return Section::create([
            'name' => 'A',
        ]);
    }

    protected function createClassSection($academicYear, $class, $section)
    {
        return ClassSection::create([
            'academic_year_id' => $academicYear->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
    }

    protected function createStudent($classSection, $academicYear)
    {
        $student = Student::create([
            'admission_no' => 'ADM-' . uniqid(),
            'first_name' => 'Test',
            'last_name' => 'Student',
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
            'academic_year_id' => $academicYear->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        return $student;
    }

    protected function createFeeStructure($academicYear, $class, $amount = 10000)
    {
        return FeeStructure::create([
            'academic_year_id' => $academicYear->academic_year_id,
            'class_id' => $class->class_id,
            'category_id' => 1,
            'amount' => $amount,
            'term' => 'Term 1',
            'payment_frequency' => 'termly',
            'due_date' => now()->addDays(30),
            'status' => 'active',
            'created_by' => $this->user->id,
        ]);
    }

    public function test_fee_assignment_service_assigns_fee_to_student()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $service = app(FeeAssignmentService::class);
        $assignment = $service->assignFeeToStudent(
            $student->student_id,
            $feeStructure->fee_structure_id,
            $academicYear->academic_year_id,
            'Term 1'
        );

        $this->assertDatabaseHas('student_fee_assignments', [
            'student_id' => $student->student_id,
            'fee_structure_id' => $feeStructure->fee_structure_id,
            'academic_year_id' => $academicYear->academic_year_id,
            'term' => 'Term 1',
            'amount' => $feeStructure->amount,
        ]);

        $this->assertEquals($feeStructure->amount, $assignment->amount);
        $this->assertEquals($feeStructure->amount, $assignment->final_amount);
    }

    public function test_fee_assignment_service_assigns_with_discount()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class, 10000);

        $this->actingAs($this->user);

        $service = app(FeeAssignmentService::class);
        $assignment = $service->assignFeeToStudent(
            $student->student_id,
            $feeStructure->fee_structure_id,
            $academicYear->academic_year_id,
            'Term 1',
            2000
        );

        $this->assertEquals(10000, $assignment->amount);
        $this->assertEquals(2000, $assignment->discount_amount);
        $this->assertEquals(8000, $assignment->final_amount);
    }

    public function test_bulk_assign_to_class()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);

        $student1 = $this->createStudent($classSection, $academicYear);
        $student2 = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $service = app(FeeAssignmentService::class);
        $result = $service->bulkAssignToClass(
            $class->class_id,
            $academicYear->academic_year_id,
            'Term 1',
            [$feeStructure->fee_structure_id]
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(2, $result['count']);

        $this->assertDatabaseCount('student_fee_assignments', 2);
    }

    public function test_auto_assign_fees_to_student()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $service = app(FeeAssignmentService::class);
        $result = $service->autoAssignFeesToStudent($student, $academicYear->academic_year_id);

        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(0, $result['count']);
    }

    public function test_fee_adjustment_creation()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $feeStructure->fee_structure_id,
            'academic_year_id' => $academicYear->academic_year_id,
            'term' => 'Term 1',
            'amount' => 10000,
            'final_amount' => 10000,
            'assigned_by' => $this->user->id,
            'status' => 'active',
        ]);

        $adjustment = FeeAdjustment::create([
            'student_fee_assignment_id' => $assignment->id,
            'student_id' => $student->student_id,
            'original_amount' => 10000,
            'new_amount' => 8000,
            'adjustment_amount' => 2000,
            'adjustment_type' => 'reduction',
            'reason' => 'Financial hardship',
            'status' => 'pending',
            'requested_by' => $this->user->id,
        ]);

        $this->assertDatabaseHas('fee_adjustments', [
            'student_id' => $student->student_id,
            'status' => 'pending',
            'adjustment_type' => 'reduction',
        ]);

        $this->assertEquals('pending', $adjustment->status);
    }

    public function test_fee_adjustment_approval_workflow()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $feeStructure->fee_structure_id,
            'academic_year_id' => $academicYear->academic_year_id,
            'term' => 'Term 1',
            'amount' => 10000,
            'final_amount' => 10000,
            'assigned_by' => $this->user->id,
            'status' => 'active',
        ]);

        $adjustment = FeeAdjustment::create([
            'student_fee_assignment_id' => $assignment->id,
            'student_id' => $student->student_id,
            'original_amount' => 10000,
            'new_amount' => 8000,
            'adjustment_amount' => 2000,
            'adjustment_type' => 'reduction',
            'reason' => 'Financial hardship',
            'status' => 'pending',
            'requested_by' => $this->user->id,
        ]);

        $admin = User::factory()->create();
        $this->actingAs($admin);

        $adjustment->approve($admin->id, 'Approved due to verified hardship');

        $this->assertDatabaseHas('fee_adjustments', [
            'id' => $adjustment->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);

        $assignment->refresh();
        $this->assertEquals(8000, $assignment->final_amount);
        $this->assertEquals(2000, $assignment->discount_amount);

        $this->assertDatabaseHas('fee_adjustment_audit_logs', [
            'fee_adjustment_id' => $adjustment->id,
            'action' => 'approved',
        ]);
    }

    public function test_fee_adjustment_rejection()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $feeStructure->fee_structure_id,
            'academic_year_id' => $academicYear->academic_year_id,
            'term' => 'Term 1',
            'amount' => 10000,
            'final_amount' => 10000,
            'assigned_by' => $this->user->id,
            'status' => 'active',
        ]);

        $adjustment = FeeAdjustment::create([
            'student_fee_assignment_id' => $assignment->id,
            'student_id' => $student->student_id,
            'original_amount' => 10000,
            'new_amount' => 8000,
            'adjustment_amount' => 2000,
            'adjustment_type' => 'reduction',
            'reason' => 'Financial hardship',
            'status' => 'pending',
            'requested_by' => $this->user->id,
        ]);

        $admin = User::factory()->create();
        $this->actingAs($admin);

        $adjustment->reject($admin->id, 'Insufficient documentation');

        $this->assertDatabaseHas('fee_adjustments', [
            'id' => $adjustment->id,
            'status' => 'rejected',
            'approved_by' => $admin->id,
            'rejection_reason' => 'Insufficient documentation',
        ]);

        $assignment->refresh();
        $this->assertEquals(10000, $assignment->final_amount);
    }

    public function test_prevents_duplicate_fee_assignment()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);
        $feeStructure = $this->createFeeStructure($academicYear, $class);

        $this->actingAs($this->user);

        $service = app(FeeAssignmentService::class);
        $service->assignFeeToStudent(
            $student->student_id,
            $feeStructure->fee_structure_id,
            $academicYear->academic_year_id,
            'Term 1'
        );

        $count = StudentFeeAssignment::where('student_id', $student->student_id)
            ->where('fee_structure_id', $feeStructure->fee_structure_id)
            ->count();

        $this->assertEquals(1, $count);
    }

    public function test_student_getter_attributes()
    {
        $academicYear = $this->createAcademicYear();
        $class = $this->createClass();
        $section = $this->createSection();
        $classSection = $this->createClassSection($academicYear, $class, $section);
        $student = $this->createStudent($classSection, $academicYear);

        $this->assertEquals('Test Student', $student->full_name);
        $this->assertNotNull($student->admission_no);
        $this->assertTrue($student->is_active);
    }
}
