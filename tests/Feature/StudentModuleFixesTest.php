<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\User;
use App\Services\AdmissionNumberService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Student module app-level fixes:
 *  - admission numbers (nothing generated one; the field had to be typed)
 *  - one current enrollment per learner (the column defaults to true and
 *    nothing cleared the others)
 *  - sibling reciprocal relationship type (used to write the invalid enum
 *    value 'sibling' for half/step relations)
 */
class StudentModuleFixesTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'student-fixes@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'admission_no' => '',
            'first_name' => 'Test',
            'middle_name' => '',
            'last_name' => 'Learner',
            'date_of_birth' => '2013-05-05',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => '2026-01-15',
            'status' => 'active',
        ], $overrides);
    }

    private function makeStudent(string $gender = 'female'): Student
    {
        return Student::create([
            // admission_no is varchar(20), so keep this short.
            'admission_no' => 'FX' . substr(uniqid(), -8),
            'first_name' => 'Existing',
            'last_name' => 'Learner',
            'date_of_birth' => '2012-01-01',
            'gender' => $gender,
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
    }

    // ---------------------------------------------------------------------
    // Item 6: admission number generation
    // ---------------------------------------------------------------------

    public function test_a_blank_admission_number_is_generated_in_the_documented_format(): void
    {
        $this->actingAs($this->admin)
            ->post(route('students.store'), $this->studentPayload())
            ->assertRedirect(route('students.index'));

        $student = Student::where('first_name', 'Test')->firstOrFail();

        $this->assertMatchesRegularExpression(
            '/^ADM-2026-\d{3}$/',
            $student->admission_no,
            'A generated admission number must follow the documented ADM-YYYY-NNN shape.'
        );

        $this->assertSame('ADM-2026-001', $student->admission_no);
    }

    public function test_generated_admission_numbers_increment_within_the_year(): void
    {
        $service = app(AdmissionNumberService::class);

        $first = $service->next(2026);
        $this->makeStudent()->update(['admission_no' => $first]);

        $second = $service->next(2026);

        $this->assertSame('ADM-2026-001', $first);
        $this->assertSame('ADM-2026-002', $second);
    }

    public function test_a_typed_admission_number_is_respected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('students.store'), $this->studentPayload(['admission_no' => 'SCHOOL-CUSTOM-77']))
            ->assertRedirect(route('students.index'));

        $this->assertDatabaseHas('students', ['admission_no' => 'SCHOOL-CUSTOM-77']);
    }

    /**
     * NOT DISCRIMINATING — this passes on the unfixed code too, and is kept as
     * behaviour-preservation only.
     *
     * CreateStudentRequest appends `|unique:students,admission_no`, so a typed
     * duplicate is caught by validation and never reaches the controller. The
     * controller keeps its own guard as defence in depth for non-FormRequest
     * callers, but the web path cannot exercise it. What actually used to be
     * broken was the blank case, covered by
     * test_a_blank_admission_number_is_generated_in_the_documented_format().
     */
    public function test_a_duplicate_admission_number_is_rejected_without_creating_a_second_learner(): void
    {
        $this->makeStudent()->update(['admission_no' => 'ADM-2026-001']);

        $this->actingAs($this->admin)
            ->post(route('students.store'), $this->studentPayload(['admission_no' => 'ADM-2026-001']))
            ->assertSessionHasErrors('admission_no');

        $this->assertSame(
            1,
            Student::where('admission_no', 'ADM-2026-001')->count(),
            'The duplicate must not create a second learner.'
        );
    }

    // ---------------------------------------------------------------------
    // Item 7: sibling relationship type
    // ---------------------------------------------------------------------

    public function test_sibling_reciprocal_type_is_derived_from_gender(): void
    {
        $female = $this->makeStudent('female');
        $male = $this->makeStudent('male');

        $this->actingAs($this->admin)
            ->post(route('students.add-sibling', $female->student_id), [
                'sibling_id' => $male->student_id,
                'relationship_type' => 'half_brother',
            ])
            ->assertRedirect();

        $forward = DB::table('student_siblings')
            ->where('student_id', $female->student_id)
            ->where('sibling_student_id', $male->student_id)
            ->first();

        $reciprocal = DB::table('student_siblings')
            ->where('student_id', $male->student_id)
            ->where('sibling_student_id', $female->student_id)
            ->first();

        $this->assertNotNull($forward);
        $this->assertSame('half_brother', $forward->relationship_type);

        $this->assertNotNull($reciprocal, 'The reciprocal link must be created.');
        $this->assertSame(
            'half_sister',
            $reciprocal->relationship_type,
            'The reverse of "he is her half brother" is "she is his half sister". "sibling" is not a valid enum value.'
        );
    }

    public function test_step_sibling_reciprocal_is_valid_for_a_male_learner(): void
    {
        $male = $this->makeStudent('male');
        $female = $this->makeStudent('female');

        $this->actingAs($this->admin)
            ->post(route('students.add-sibling', $male->student_id), [
                'sibling_id' => $female->student_id,
                'relationship_type' => 'step_sister',
            ])
            ->assertRedirect();

        $reciprocal = DB::table('student_siblings')
            ->where('student_id', $female->student_id)
            ->where('sibling_student_id', $male->student_id)
            ->first();

        $this->assertNotNull($reciprocal);
        $this->assertSame('step_brother', $reciprocal->relationship_type);
    }

    public function test_an_invalid_relationship_type_is_rejected(): void
    {
        $a = $this->makeStudent('female');
        $b = $this->makeStudent('female');

        $this->actingAs($this->admin)
            ->post(route('students.add-sibling', $a->student_id), [
                'sibling_id' => $b->student_id,
                'relationship_type' => 'cousin',
            ])
            ->assertSessionHasErrors('relationship_type');

        $this->assertSame(0, DB::table('student_siblings')->count());
    }

    // ---------------------------------------------------------------------
    // Item 5: one current enrollment per learner
    // ---------------------------------------------------------------------

    private function classSection(): ClassSection
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $class = SchoolClass::create(['name' => 'Grade 4', 'numeric_value' => 4]);
        $section = Section::create(['name' => 'A']);

        return ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
    }

    public function test_creating_a_current_enrollment_clears_the_previous_one(): void
    {
        $student = $this->makeStudent();
        $sectionA = $this->classSection();
        $sectionB = $this->classSection();

        $first = StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $sectionA->class_section_id,
            'academic_year_id' => $sectionA->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('student-class-enrollments.store'), [
                'student_id' => $student->student_id,
                'class_section_id' => $sectionB->class_section_id,
                'academic_year_id' => $sectionB->academic_year_id,
                'enrollment_date' => now()->toDateString(),
                'status' => 'active',
                'is_current' => 1,
            ])
            ->assertRedirect(route('student-class-enrollments.index'));

        $this->assertSame(
            1,
            StudentClassEnrollment::where('student_id', $student->student_id)->where('is_current', true)->count(),
            'A learner must never hold two current enrollments.'
        );
        $this->assertFalse((bool) $first->fresh()->is_current);
    }

    public function test_an_enrollment_can_be_saved_as_not_current(): void
    {
        $student = $this->makeStudent();
        $section = $this->classSection();

        $this->actingAs($this->admin)
            ->post(route('student-class-enrollments.store'), [
                'student_id' => $student->student_id,
                'class_section_id' => $section->class_section_id,
                'academic_year_id' => $section->academic_year_id,
                'enrollment_date' => now()->toDateString(),
                'status' => 'active',
                'is_current' => 0,
            ])
            ->assertRedirect(route('student-class-enrollments.index'));

        $this->assertSame(
            0,
            StudentClassEnrollment::where('student_id', $student->student_id)->where('is_current', true)->count(),
            'The column defaults to true, so an explicit "not current" must be honoured.'
        );
    }

    public function test_the_enrollment_form_exposes_the_current_flag(): void
    {
        $this->actingAs($this->admin)
            ->get(route('student-class-enrollments.create'))
            ->assertOk()
            ->assertSee('name="is_current"', false);
    }
}
