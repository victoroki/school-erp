<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use App\Services\FinanceService;
use App\Services\LedgerService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * A reversed payment is no longer money received.
 *
 * FeePayment::scopeNotReversed() documents the invariant: "Every balance,
 * collection total and report figure must apply this scope." Before this fix the
 * scope existed but was applied by exactly zero callers, so every collection
 * total, finance dashboard figure and report included voided money.
 *
 * There are currently no reversed payments in the live database, so this is a
 * latent defect: it would have started producing wrong figures the first time a
 * bursar voided a receipt.
 */
class FeeReversalReportingTest extends TestCase
{
    use RefreshDatabase;

    protected User $accountant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->accountant = User::factory()->create(['name' => 'Reporting Accountant']);
        $this->accountant->staff()->create([
            'first_name' => 'Reporting',
            'last_name' => 'Accountant',
            'date_of_birth' => '1990-01-01',
            'gender' => 'male',
            'phone_primary' => '0700000001',
            'work_email' => 'reporting-accountant@test.local',
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
        $this->accountant->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->accountant->load('roles.permissions');
    }

    private function makeAssignment(float $amount): StudentFeeAssignment
    {
        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Grade ' . random_int(1, 9), 'numeric_value' => random_int(1, 9)]);
        $section = Section::create(['name' => 'A' . uniqid()]);
        $classSection = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);

        $student = Student::create([
            'admission_no' => 'RV' . substr(uniqid(), -8),
            'first_name' => 'Reversal',
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
            'student_id' => $student->student_id,
            'class_section_id' => $classSection->class_section_id,
            'academic_year_id' => $year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        $category = FeeCategory::create(['name' => 'Cat-' . uniqid(), 'type' => 'mandatory']);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'category_id' => $category->category_id,
            'amount' => $amount,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'due_date' => now()->addDays(30),
            'status' => 'active',
            'created_by' => $this->accountant->id,
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $structure->academic_year_id,
            'term' => 'T1',
            'amount' => $amount,
            'final_amount' => $amount,
            'assigned_by' => $this->accountant->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);
    }

    private function pay(StudentFeeAssignment $assignment, float $amount, string $method = 'cash'): FeePayment
    {
        return app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => $amount,
            'payment_date' => now()->toDateString(),
            'payment_method' => $method,
        ]);
    }

    /**
     * Two payments of 3,000 each; the second is reversed. Only 3,000 was
     * actually received.
     *
     * @return array{0: FeePayment, 1: FeePayment}
     */
    private function scenario(): array
    {
        $assignment = $this->makeAssignment(10000);
        $valid = $this->pay($assignment, 3000, 'cash');
        $voided = $this->pay($assignment, 3000, 'cash');

        app(LedgerService::class)->reversePayment($voided, 'Cheque bounced', $this->accountant->id);

        return [$valid, $voided];
    }

    public function test_collections_report_excludes_reversed_payments_from_its_totals(): void
    {
        $this->scenario();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections'))
            ->assertOk();

        // 3,000 received, not 6,000.
        $response->assertSee('KES 3,000.00');
        $response->assertDontSee('KES 6,000.00');
    }

    public function test_collections_report_discloses_the_voided_amount(): void
    {
        $this->scenario();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections'))
            ->assertOk();

        // The exclusion must be visible, not silent.
        $response->assertSee('voided');
        $response->assertSee('excluded from the collected total');
    }

    public function test_reversed_payment_still_appears_in_the_register_marked_void(): void
    {
        [, $voided] = $this->scenario();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.receipt-register'))
            ->assertOk();

        // Retained for audit...
        $response->assertSee($voided->receipt_number);

        // ...and clearly marked.
        $response->assertSee('VOID');
    }

    public function test_receipt_register_total_excludes_the_voided_receipt(): void
    {
        $this->scenario();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.receipt-register'))
            ->assertOk();

        $response->assertSee('KES 3,000.00');
        $response->assertDontSee('KES 6,000.00');
    }

    public function test_payment_method_report_excludes_reversed_payments(): void
    {
        $this->scenario();

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.payment-method'))
            ->assertOk();

        // Cash totals 3,000 (one live payment), not 6,000 (both).
        $response->assertSee('3,000.00');
        $response->assertDontSee('6,000.00');
    }

    public function test_the_method_filter_offers_only_values_the_enum_can_hold(): void
    {
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections'))
            ->assertOk();

        foreach (FeePayment::PAYMENT_METHODS as $method) {
            $response->assertSee('value="' . $method . '"', false);
        }

        // None of these can match a row: the ENUM is
        // cash|check|card|bank_transfer|online, so 'mpesa', 'cheque' and 'other'
        // were options that always returned nothing.
        foreach (['mpesa', 'cheque', 'other'] as $impossible) {
            $response->assertDontSee('value="' . $impossible . '"', false);
        }
    }

    public function test_filtering_by_a_real_method_returns_only_that_method(): void
    {
        $assignment = $this->makeAssignment(10000);
        $this->pay($assignment, 2500, 'cash');
        $this->pay($assignment, 1100, 'online');

        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections', ['payment_method' => 'online']))
            ->assertOk();

        $response->assertSee('KES 1,100.00');
        $response->assertDontSee('KES 2,500.00');
    }

    public function test_the_method_breakdown_respects_the_method_filter(): void
    {
        $assignment = $this->makeAssignment(10000);
        $this->pay($assignment, 2500, 'cash');
        $this->pay($assignment, 1100, 'online');

        // "By Method (Filtered)" previously applied only the date filters and
        // ignored the method, so it always showed both cash and online.
        $response = $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections', ['payment_method' => 'online']))
            ->assertOk();

        $response->assertSee('By Method (Filtered)');

        // Target the breakdown table CELL. A bare '>Cash<' would also match the
        // method filter dropdown's own <option>Cash</option>.
        $response->assertDontSee('font-semibold">Cash</td>', false);
        $response->assertSee('font-semibold">Online</td>', false);
    }

    public function test_legacy_rows_with_an_empty_method_show_as_unspecified_not_blank(): void
    {
        $assignment = $this->makeAssignment(10000);

        $this->insertLegacyUnspecifiedMethod($assignment);

        $this->actingAs($this->accountant)
            ->get(route('fees.reports.collections'))
            ->assertOk()
            ->assertSee('Unspecified');

        // The standalone Payment Methods report groups the same column and must
        // label the empty group too, rather than rendering a blank cell.
        $this->actingAs($this->accountant)
            ->get(route('fees.reports.payment-method'))
            ->assertOk()
            ->assertSee('Unspecified');
    }

    public function test_the_scope_is_safe_to_apply_to_joined_queries(): void
    {
        // notReversed() must qualify the column; unqualified it would be
        // ambiguous against student_fee_assignments, which several report
        // queries join.
        $assignment = $this->makeAssignment(10000);
        $this->pay($assignment, 1200, 'cash');

        $sum = FeePayment::join('student_fee_assignments', 'fee_payments.student_fee_assignment_id', '=', 'student_fee_assignments.id')
            ->notReversed()
            ->sum('fee_payments.amount');

        $this->assertSame(1200.0, (float) $sum);
    }

    public function test_reversed_payments_are_still_returned_by_the_reversed_scope(): void
    {
        [, $voided] = $this->scenario();

        $this->assertTrue($voided->fresh()->isReversed());
        $this->assertSame(1, FeePayment::reversed()->count());
        $this->assertSame(1, FeePayment::notReversed()->count());
    }

    /**
     * Reproduce the empty payment_method found on all 209 live rows.
     *
     * payment_method is enum('cash','check','card','bank_transfer','online').
     * MySQL stores the empty string error value when an invalid ENUM member is
     * inserted with strict mode disabled — verified directly: inserting 'M-Pesa'
     * with sql_mode='' yields ''. The 2026-09-06 import that created those rows
     * therefore ran with strict mode off and silently discarded the method.
     *
     * The value cannot be re-inserted under the default strict mode, so the
     * session mode is relaxed for this insert only, then restored.
     */
    private function insertLegacyUnspecifiedMethod(StudentFeeAssignment $assignment): void
    {
        $previousMode = DB::selectOne('SELECT @@SESSION.sql_mode AS m')->m;

        DB::statement("SET SESSION sql_mode=''");

        try {
            DB::table('fee_payments')->insert([
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 750,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'M-Pesa',
                'receipt_number' => 'LEGACY-' . uniqid(),
            ]);
        } finally {
            DB::statement("SET SESSION sql_mode='" . $previousMode . "'");
        }

        $this->assertSame(
            '',
            (string) DB::table('fee_payments')->where('receipt_number', 'like', 'LEGACY-%')->value('payment_method'),
            'The import mechanism should have produced an empty method.'
        );
    }
}
