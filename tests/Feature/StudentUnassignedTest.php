<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Unassigned Students bulk class assignment page.
 * - Lists only active students with no current class enrollment.
 * - Assigning writes standard StudentClassEnrollment rows (same table the
 *   admission form and the Class Enrollments screen use).
 * - Assigned students drop off the list and show the class on their profile.
 */
class StudentUnassignedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->academicYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);

        $sectionA = Section::create(['name' => 'A']);
        $sectionB = Section::create(['name' => 'B']);

        $this->classSectionA = ClassSection::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $sectionA->section_id,
        ]);

        $this->classSectionB = ClassSection::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $sectionB->section_id,
        ]);
    }

    private function makeStudent(array $overrides = []): Student
    {
        return Student::create(array_merge([
            'admission_no' => 'ADM-U'.bin2hex(random_bytes(4)),
            'first_name' => 'Unassigned',
            'last_name' => 'Student',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => '2025-01-01',
            'emergency_contact' => '0722000000',
            'status' => 'active',
            'is_active' => true,
        ], $overrides));
    }

    public function test_unassigned_nav_entry_is_configured(): void
    {
        $sections = config('menu.sections');
        $studentsSection = collect($sections)->firstWhere('key', 'students');

        $this->assertNotNull($studentsSection);
        $this->assertContains('student-unassigned*', $studentsSection['active']);

        $entry = collect($studentsSection['children'] ?? [])->firstWhere('key', 'student-unassigned');
        $this->assertNotNull($entry, 'Unassigned Students sidebar entry is missing');
        $this->assertSame('Unassigned Students', $entry['label']);
        $this->assertSame('student-unassigned.index', $entry['route']);
        $this->assertSame(['students.manage'], $entry['permission']);
    }

    public function test_index_lists_only_active_students_without_current_enrollment(): void
    {
        $unassignedOne = $this->makeStudent(['first_name' => 'Alpha']);
        $unassignedTwo = $this->makeStudent(['first_name' => 'Beta']);

        // Has a current enrollment → must NOT appear.
        $assigned = $this->makeStudent(['first_name' => 'Gamma']);
        StudentClassEnrollment::create([
            'student_id' => $assigned->student_id,
            'class_section_id' => $this->classSectionA->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'is_current' => true,
            'enrollment_date' => '2026-01-10',
            'status' => 'active',
        ]);

        // Inactive → must NOT appear.
        $inactive = $this->makeStudent(['first_name' => 'Delta', 'status' => 'inactive']);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('student-unassigned.index'));

        $response->assertOk();
        $response->assertSee($unassignedOne->full_name);
        $response->assertSee($unassignedTwo->full_name);
        $response->assertDontSee($assigned->full_name);
        $response->assertDontSee($inactive->full_name);
    }

    public function test_bulk_assign_creates_current_enrollments_and_drops_students_off_list(): void
    {
        $studentA = $this->makeStudent(['first_name' => 'Charlie']);
        $studentB = $this->makeStudent(['first_name' => 'Dana']);

        // Different classes per student, one "Save All" action.
        $this->actingAs($this->superAdmin)
            ->post(route('student-unassigned.store'), [
                'student_ids' => [$studentA->student_id, $studentB->student_id],
                'assignments' => [
                    $studentA->student_id => $this->classSectionA->class_section_id,
                    $studentB->student_id => $this->classSectionB->class_section_id,
                ],
            ])
            ->assertRedirect(route('student-unassigned.index'));

        $this->assertDatabaseHas('student_class_enrollments', [
            'student_id' => $studentA->student_id,
            'class_section_id' => $this->classSectionA->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'is_current' => true,
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('student_class_enrollments', [
            'student_id' => $studentB->student_id,
            'class_section_id' => $this->classSectionB->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'is_current' => true,
            'status' => 'active',
        ]);

        // Both students drop off the unassigned list.
        $response = $this->actingAs($this->superAdmin)
            ->get(route('student-unassigned.index'));

        $response->assertOk();
        $response->assertDontSee($studentA->full_name);
        $response->assertDontSee($studentB->full_name);

        // And they show the correct class via the profile's current_enrollment
        // accessor (same one the show view uses).
        $studentA->refresh();
        $studentB->refresh();
        $this->assertSame($this->classSectionA->class_section_id, $studentA->current_enrollment->class_section_id);
        $this->assertSame($this->classSectionB->class_section_id, $studentB->current_enrollment->class_section_id);
    }

    public function test_checked_student_without_class_is_rejected(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($this->superAdmin)
            ->post(route('student-unassigned.store'), [
                'student_ids' => [$student->student_id],
                'assignments' => [$student->student_id => ''],
            ])
            ->assertSessionHasErrors('assignments.'.$student->student_id);

        $this->assertDatabaseMissing('student_class_enrollments', ['student_id' => $student->student_id]);
    }

    public function test_teacher_without_manage_permission_is_forbidden(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('student-unassigned.index'))
            ->assertForbidden();

        $student = $this->makeStudent();

        $this->actingAs($this->teacher)
            ->post(route('student-unassigned.store'), [
                'student_ids' => [$student->student_id],
                'assignments' => [$student->student_id => $this->classSectionA->class_section_id],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('student_class_enrollments', ['student_id' => $student->student_id]);
    }
}
