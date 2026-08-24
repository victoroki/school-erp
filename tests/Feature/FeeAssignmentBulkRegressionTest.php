<?php

namespace Tests\Feature;

use App\Jobs\SendBulkMessage;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\SentMessage;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression tests for class-scoped bulk fee assignment and bulk messaging.
 *
 * Both features previously crashed with a QueryException: they read the
 * non-existent `class_id` column on student_class_enrollments, which actually
 * links to a class via class_section_id -> class_sections.class_id.
 */
class FeeAssignmentBulkRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->superAdmin = $this->createUserWithRole('Super Admin');

        $this->academicYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->category = FeeCategory::create([
            'name' => 'Tuition',
            'type' => 'mandatory',
            'status' => 'active',
        ]);

        $this->classOne = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);
        $this->classTwo = SchoolClass::create(['name' => 'Form 2', 'numeric_value' => 2]);

        $this->classSectionOne = ClassSection::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $this->classOne->class_id,
            'section_id' => Section::create(['name' => 'A'])->section_id,
        ]);

        $this->classSectionTwo = ClassSection::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $this->classTwo->class_id,
            'section_id' => Section::create(['name' => 'A'])->section_id,
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole($roleName);

        return $user;
    }

    private function createStudent(string $admissionNo, ClassSection $classSection, string $phone = '0722000000'): Student
    {
        $student = Student::create([
            'admission_no' => $admissionNo,
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'status' => 'active',
            'is_active' => true,
            'phone' => $phone,
        ]);

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        return $student;
    }

    private function createClassScopedFee(int $classId, float $amount = 1000): FeeStructure
    {
        return FeeStructure::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $classId,
            'category_id' => $this->category->category_id,
            'amount' => $amount,
            'payment_frequency' => 'termly',
            'status' => 'active',
        ]);
    }

    // ─── A1.1 bulk_all class-scoped fee assignment ──────────────

    public function test_bulk_all_assignment_with_class_scoped_fee_does_not_crash(): void
    {
        $student = $this->createStudent('ADM-BULK-1', $this->classSectionOne);
        $fee = $this->createClassScopedFee($this->classOne->class_id);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('fees.assignments.store'), [
                'assignment_type' => 'bulk_all',
                'academic_year_id' => $this->academicYear->academic_year_id,
                'term' => 'Term 1',
                'class_ids' => [$this->classOne->class_id],
                'fees' => [$fee->fee_structure_id],
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('fees.assignments.index'));

        $this->assertDatabaseHas('student_fee_assignments', [
            'student_id' => $student->student_id,
            'fee_structure_id' => $fee->fee_structure_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'term' => 'Term 1',
            'amount' => '1000.00',
        ]);
    }

    public function test_bulk_all_assignment_skips_students_not_in_the_fee_class(): void
    {
        $studentInClass = $this->createStudent('ADM-BULK-2', $this->classSectionOne);
        $studentOtherClass = $this->createStudent('ADM-BULK-3', $this->classSectionTwo);

        $fee = $this->createClassScopedFee($this->classOne->class_id);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('fees.assignments.store'), [
                'assignment_type' => 'bulk_all',
                'academic_year_id' => $this->academicYear->academic_year_id,
                'term' => 'Term 1',
                'class_ids' => [$this->classOne->class_id, $this->classTwo->class_id],
                'fees' => [$fee->fee_structure_id],
            ]);

        $response->assertRedirect(route('fees.assignments.index'));

        $this->assertDatabaseHas('student_fee_assignments', [
            'student_id' => $studentInClass->student_id,
            'fee_structure_id' => $fee->fee_structure_id,
        ]);

        $this->assertDatabaseMissing('student_fee_assignments', [
            'student_id' => $studentOtherClass->student_id,
            'fee_structure_id' => $fee->fee_structure_id,
        ]);
    }

    // ─── A1.2 bulk messaging to a class ─────────────────────────

    public function test_bulk_message_to_class_creates_recipients_without_crashing(): void
    {
        $studentInClass = $this->createStudent('ADM-MSG-1', $this->classSectionOne);
        $studentOtherClass = $this->createStudent('ADM-MSG-2', $this->classSectionTwo);

        $sentMessage = SentMessage::create([
            'message_type' => 'SMS',
            'content' => 'Hello from school',
            'recipient_type' => 'Class',
            'status' => 'Sending',
        ]);

        $job = new SendBulkMessage($sentMessage, ['class_id' => $this->classOne->class_id]);

        $job->handle();

        $this->assertDatabaseHas('message_recipients', [
            'sent_message_id' => $sentMessage->id,
            'recipient_type' => 'Student',
            'recipient_id' => $studentInClass->student_id,
            'contact' => '+254722000000',
        ]);

        $this->assertDatabaseMissing('message_recipients', [
            'sent_message_id' => $sentMessage->id,
            'recipient_id' => $studentOtherClass->student_id,
        ]);

        $this->assertDatabaseHas('sent_messages', [
            'id' => $sentMessage->id,
            'status' => 'Sent',
            'recipient_count' => 1,
        ]);
    }
}
