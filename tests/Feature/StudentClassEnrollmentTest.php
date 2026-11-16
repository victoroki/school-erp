<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Task 1: "Student Class Enrollments" restoration.
 * The resource route/controller/views were still in place — the removal that
 * broke the feature was the sidebar entry (plus the parent's active-URL
 * pattern) in config/menu.php. These tests pin that restoration down.
 */
class StudentClassEnrollmentTest extends TestCase
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

        $this->academicYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);
        $section = Section::create(['name' => 'A']);

        $this->classSection = ClassSection::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
    }

    public function test_class_enrollments_nav_entry_is_restored(): void
    {
        $sections = config('menu.sections');
        $studentsSection = collect($sections)->firstWhere('key', 'students');

        $this->assertNotNull($studentsSection, 'Students section must exist in the menu config');
        $this->assertContains('student-class-enrollments*', $studentsSection['active']);

        $entry = collect($studentsSection['children'] ?? [])->firstWhere('key', 'student-enrollments');
        $this->assertNotNull($entry, 'Class Enrollments sidebar entry is missing');
        $this->assertSame('Class Enrollments', $entry['label']);
        $this->assertSame('student-class-enrollments.index', $entry['route']);
    }

    public function test_class_enrollments_index_renders(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('student-class-enrollments.index'))
            ->assertOk();
    }

    public function test_class_enrollment_can_be_created(): void
    {
        $student = Student::create([
            'admission_no' => 'ADM-ER'.substr(uniqid(), 0, 8),
            'first_name' => 'Brian',
            'last_name' => 'Kiprop',
            'date_of_birth' => '2011-03-03',
            'gender' => 'male',
            'city' => 'Eldoret',
            'country' => 'Kenya',
            'admission_date' => '2025-01-10',
            'emergency_contact' => '0722000000',
            'status' => 'active',
            'is_active' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('student-class-enrollments.store'), [
                'student_id' => $student->student_id,
                'class_section_id' => $this->classSection->class_section_id,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'roll_number' => 'R-01',
                'enrollment_date' => '2026-01-10',
                'status' => 'active',
            ])
            ->assertRedirect(route('student-class-enrollments.index'));

        $this->assertDatabaseHas('student_class_enrollments', [
            'student_id' => $student->student_id,
            'class_section_id' => $this->classSection->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'status' => 'active',
        ]);
    }

    public function test_edit_form_renders_saved_enrollment_date(): void
    {
        $student = Student::create([
            'admission_no' => 'ADM-EE'.substr(uniqid(), 0, 8),
            'first_name' => 'Diana',
            'last_name' => 'Wanjiku',
            'date_of_birth' => '2011-03-03',
            'gender' => 'female',
            'city' => 'Nakuru',
            'country' => 'Kenya',
            'admission_date' => '2025-01-10',
            'emergency_contact' => '0722000000',
            'status' => 'active',
            'is_active' => true,
        ]);

        $enrollment = \App\Models\StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $this->classSection->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'enrollment_date' => '2026-02-14',
            'status' => 'transferred',
        ]);

        $this->actingAs($this->superAdmin)
            ->get(route('student-class-enrollments.edit', $enrollment->enrollment_id))
            ->assertOk()
            ->assertSee('value="2026-02-14"', false)
            ->assertDontSee('2026-02-14 00:00:00', false)
            // Saved status (not the hardcoded default "active").
            ->assertSee('value="transferred" selected', false);
    }
}
