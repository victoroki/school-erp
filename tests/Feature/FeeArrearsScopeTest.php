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
use App\Services\FeeBalanceService;
use App\Support\Money;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The arrears report's academic-year scope.
 *
 * A student's profile and statement report their ALL-TIME position. The arrears
 * report has always reported ONE academic year, and it still does by default —
 * that default was not changed, because bursars run their chasing lists term by
 * term. What was missing was any way to say which scope you were looking at, or
 * to ask for all years at all, so the page could appear to disagree with the
 * same student's own page while both were right.
 *
 * These tests pin all three scopes, and pin the all-years figures to the balance
 * service — the all-time authority the profile and statement use.
 */
class FeeArrearsScopeTest extends TestCase
{
    use RefreshDatabase;

    protected User $accountant;

    protected AcademicYear $currentYear;

    protected AcademicYear $priorYear;

    protected Term $currentTerm;

    protected Term $priorTerm;

    protected SchoolClass $class;

    protected ClassSection $currentClassSection;

    protected ClassSection $priorClassSection;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->accountant = $this->makeAccountant();

        $this->priorYear = AcademicYear::create([
            'name' => 'AY-PRIOR-' . uniqid(),
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => false,
        ]);

        $this->currentYear = AcademicYear::create([
            'name' => 'AY-CURRENT-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->priorTerm = Term::create([
            'academic_year_id' => $this->priorYear->academic_year_id,
            'name' => 'Term 1',
            'code' => 'P1',
            'start_date' => '2025-01-05',
            'end_date' => '2025-04-03',
            'status' => 'active',
        ]);

        $this->currentTerm = Term::create([
            'academic_year_id' => $this->currentYear->academic_year_id,
            'name' => 'Term 1',
            'code' => 'C1',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-03',
            'status' => 'active',
        ]);

        $this->class = SchoolClass::create(['name' => 'Grade 7', 'numeric_value' => 7]);
        $section = Section::create(['name' => 'A']);

        $this->priorClassSection = ClassSection::create([
            'academic_year_id' => $this->priorYear->academic_year_id,
            'class_id' => $this->class->class_id,
            'section_id' => $section->section_id,
        ]);

        $this->currentClassSection = ClassSection::create([
            'academic_year_id' => $this->currentYear->academic_year_id,
            'class_id' => $this->class->class_id,
            'section_id' => $section->section_id,
        ]);

        $this->student = Student::create([
            'admission_no' => 'AS' . substr(uniqid(), -8),
            'first_name' => 'Scope',
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
            'class_section_id' => $this->currentClassSection->class_section_id,
            'academic_year_id' => $this->currentYear->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);
    }

    private function makeAccountant(): User
    {
        $user = User::factory()->create([
            'name' => 'Arrears Scope Accountant',
            'email' => 'arrears.' . uniqid() . '@test.local',
        ]);
        $user->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function assignFee(float $amount, AcademicYear $year, Term $term, string $code): StudentFeeAssignment
    {
        $category = FeeCategory::create(['name' => 'Tuition-' . uniqid(), 'type' => 'mandatory']);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $this->class->class_id,
            'category_id' => $category->category_id,
            'amount' => $amount,
            'term' => $code,
            'payment_frequency' => 'termly',
            'due_date' => $year->start_date,
            'status' => 'active',
            'created_by' => $this->accountant->id,
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $this->student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id,
            'term' => $code,
            'term_id' => $term->term_id,
            'amount' => $amount,
            'final_amount' => $amount,
            'paid_amount' => 0,
            'assigned_by' => $this->accountant->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);
    }

    private function pay(StudentFeeAssignment $assignment, float $amount): void
    {
        $this->actingAs($this->accountant)
            ->post("/fee-management/{$this->student->student_id}/store-payment", [
                'student_fee_assignment_id' => (string) $assignment->id,
                'amount' => $amount,
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'transaction_id' => 'TXN-' . uniqid(),
                'remarks' => 'arrears scope test',
            ])
            ->assertRedirect();
    }

    /**
     * Current year 30,000 assigned / 10,000 paid. Prior year 50,000 / nothing.
     * All years therefore 80,000 / 10,000 / 70,000 outstanding.
     */
    private function seedTwoYears(): void
    {
        $current = $this->assignFee(30000, $this->currentYear, $this->currentTerm, 'C1');
        $this->assignFee(50000, $this->priorYear, $this->priorTerm, 'P1');

        $this->pay($current, 10000);
    }

    public function test_arrears_still_defaults_to_the_current_academic_year_and_says_so(): void
    {
        $this->seedTwoYears();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index'))
            ->assertOk();

        // The heading names the scope.
        $response->assertSee('Arrears &mdash; Academic Year ' . $this->currentYear->name, false);

        // This year's figures only.
        $response->assertSee(Money::format(30000), false);
        $response->assertSee(Money::format(20000), false);

        // Not the all-years totals.
        $response->assertDontSee(Money::format(80000), false);
        $response->assertDontSee(Money::format(70000), false);
    }

    public function test_a_named_year_reports_only_that_year(): void
    {
        $this->seedTwoYears();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index', ['academic_year_id' => $this->priorYear->academic_year_id]))
            ->assertOk();

        $response->assertSee('Arrears &mdash; Academic Year ' . $this->priorYear->name, false);
        $response->assertSee(Money::format(50000), false);
        $response->assertDontSee(Money::format(80000), false);
    }

    /**
     * The all-years scope must agree with the balance service, which is the
     * all-time authority the student profile and statement use. This is the
     * assertion that makes the two screens comparable rather than contradictory.
     */
    public function test_all_years_agrees_with_the_students_all_time_balance(): void
    {
        $this->seedTwoYears();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index', ['academic_year_id' => 'all']))
            ->assertOk();

        $response->assertSee('Arrears &mdash; All Years', false);

        // 80,000 expected, 10,000 collected, 70,000 outstanding.
        $response->assertSee(Money::format(80000), false);
        $response->assertSee(Money::format(10000), false);
        $response->assertSee(Money::format(70000), false);

        $summary = app(FeeBalanceService::class)->summaryForStudent($this->student->student_id);

        $this->assertSame(80000.00, round($summary['assigned'], 2));
        $this->assertSame(70000.00, round($summary['balance'], 2), 'The all-years outstanding must equal the all-time balance.');

        // The same figure appears on the student's own page.
        $this->actingAs($this->accountant)
            ->get(route('fees.assignments.student-summary', $this->student->student_id))
            ->assertOk()
            ->assertSee(Money::format(70000), false);
    }

    public function test_the_all_years_scope_survives_into_the_exports(): void
    {
        $this->seedTwoYears();

        $this->actingAs($this->accountant)
            ->get(route('fees.arrears.export-pdf', ['academic_year_id' => 'all']))
            ->assertOk();

        // The CSV is streamed; assert it carries both years' outstanding.
        $csv = $this->actingAs($this->accountant)
            ->get(route('fees.arrears.export-csv', ['academic_year_id' => 'all']))
            ->streamedContent();

        $this->assertStringContainsString('70,000.00', $csv, 'The all-years CSV must total both years.');
    }

    public function test_an_empty_scope_choice_does_not_silently_widen_the_report(): void
    {
        $this->seedTwoYears();

        // A blank parameter used to fall through to "every year" because the
        // filter used a truthy check. It now means the documented default.
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index', ['academic_year_id' => '']))
            ->assertOk();

        $response->assertSee('Arrears &mdash; Academic Year ' . $this->currentYear->name, false);
        $response->assertDontSee(Money::format(80000), false);
    }
}
