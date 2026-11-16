<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\Term;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Fee assignment term scoping.
 *
 * The create form hardcoded <option value="Term 1">…</option>, but
 * StudentFeeAssignmentController resolves term_id with
 * `Term::where('code', $term)` and terms.code holds "T1"/"T2"/"T3". The lookup
 * therefore never matched, so every assignment made through this form was
 * written with term = 'Term 1' and term_id = NULL — which silently removes the
 * row from every term-filtered arrears view and report.
 *
 * Live data masked this: all 263 existing assignments carry term='T2' and
 * term_id=5 because they were created by a different path.
 */
class FeeTermAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    protected Term $term;

    protected SchoolClass $class;

    protected FeeStructure $structure;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'fee-term@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->term = Term::create([
            'academic_year_id' => $this->year->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-03',
            'status' => 'active',
        ]);

        $this->class = SchoolClass::create(['name' => 'Grade 7', 'numeric_value' => 7]);
        $section = Section::create(['name' => 'A']);
        $classSection = ClassSection::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $this->class->class_id,
            'section_id' => $section->section_id,
        ]);

        $category = FeeCategory::create(['name' => 'Tuition-' . uniqid(), 'type' => 'mandatory']);

        $this->structure = FeeStructure::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $this->class->class_id,
            'category_id' => $category->category_id,
            'amount' => 12000,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'due_date' => '2026-02-01',
            'status' => 'active',
            'created_by' => $this->admin->id,
        ]);

        $this->student = Student::create([
            'admission_no' => 'FT' . substr(uniqid(), -8),
            'first_name' => 'FeeTerm',
            'last_name' => 'Learner',
            'date_of_birth' => '2011-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);

        StudentClassEnrollment::create([
            'student_id' => $this->student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $this->year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);
    }

    public function test_the_form_offers_real_term_codes_as_values(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('fees.assignments.create'))
            ->assertOk();

        // The submitted value must be the code the controller resolves on...
        $response->assertSee('value="T1"', false);

        // ...not the human label, which never matched any term.
        $response->assertDontSee('value="Term 1"', false);

        // The label is still shown to the user.
        $response->assertSee('Term 1');
    }

    public function test_assigning_a_fee_stores_a_resolved_term_id(): void
    {
        // StudentClassEnrollmentObserver auto-assigns fees for T1 when the
        // enrollment is created in setUp (which is why live data is clean — the
        // 263 existing rows came from that path, not from this form). Use a
        // SECOND term so the row under test is genuinely created by the request.
        $secondTerm = Term::create([
            'academic_year_id' => $this->year->academic_year_id,
            'name' => 'Term 2',
            'code' => 'T2',
            'start_date' => '2026-05-04',
            'end_date' => '2026-08-07',
            'status' => 'upcoming',
        ]);

        $before = StudentFeeAssignment::count();

        $this->actingAs($this->admin)
            ->post(route('fees.assignments.store'), [
                'assignment_type' => 'bulk_class',
                'academic_year_id' => $this->year->academic_year_id,
                'term' => 'T2',
                'class_id' => $this->class->class_id,
                'fees' => [$this->structure->fee_structure_id],
            ])
            ->assertRedirect();

        $this->assertSame($before + 1, StudentFeeAssignment::count(), 'The request should have created exactly one assignment.');

        $assignment = StudentFeeAssignment::orderByDesc('id')->first();

        $this->assertSame('T2', $assignment->term);
        $this->assertSame(
            $secondTerm->id,
            (int) $assignment->term_id,
            'term_id must resolve; a NULL term_id removes the row from term-filtered arrears.'
        );
    }

    public function test_auto_assignment_also_resolves_a_term(): void
    {
        // The observer path that produced all 263 live rows.
        $auto = StudentFeeAssignment::where('student_id', $this->student->student_id)
            ->where('term', 'T1')
            ->first();

        $this->assertNotNull($auto, 'Creating an enrollment should auto-assign the term\'s fees.');
        $this->assertSame($this->term->id, (int) $auto->term_id);
    }

    public function test_the_label_style_term_is_rejected_instead_of_stored_as_null(): void
    {
        // Measure the delta: the enrollment observer already auto-assigned T1.
        $before = StudentFeeAssignment::count();

        // Exactly what the old form submitted.
        $this->actingAs($this->admin)
            ->post(route('fees.assignments.store'), [
                'assignment_type' => 'bulk_class',
                'academic_year_id' => $this->year->academic_year_id,
                'term' => 'Term 1',
                'class_id' => $this->class->class_id,
                'fees' => [$this->structure->fee_structure_id],
            ])
            ->assertSessionHasErrors('term');

        $this->assertSame(
            $before,
            StudentFeeAssignment::count(),
            'An unmatched term must not create an assignment with a NULL term_id.'
        );

        $this->assertSame(
            0,
            StudentFeeAssignment::whereNull('term_id')->count(),
            'No assignment may be written without a resolved term.'
        );
    }

    public function test_a_term_from_another_academic_year_is_rejected(): void
    {
        $otherYear = AcademicYear::create([
            'name' => 'AY-other-' . uniqid(),
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => false,
        ]);

        Term::create([
            'academic_year_id' => $otherYear->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2025-01-05',
            'end_date' => '2025-04-03',
            'status' => 'completed',
        ]);

        // 'ZZ' is not a code for the academic year being billed, and the 'T1'
        // that exists belongs to the other year.
        $this->actingAs($this->admin)
            ->post(route('fees.assignments.store'), [
                'assignment_type' => 'bulk_class',
                'academic_year_id' => $this->year->academic_year_id,
                'term' => 'ZZ',
                'class_id' => $this->class->class_id,
                'fees' => [$this->structure->fee_structure_id],
            ])
            ->assertSessionHasErrors('term');

        $this->assertSame(0, StudentFeeAssignment::where('term', 'ZZ')->count());
    }
}
