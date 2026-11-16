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
use App\Models\Term;
use App\Models\User;
use App\Services\FeeBalanceService;
use App\Services\LedgerService;
use App\Support\Money;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * THE cross-screen reconciliation suite.
 *
 * Every other fee test checks that one function returns one value. This suite
 * exists to prove the thing that actually matters to a bursar: that the SAME
 * student, with the SAME financial activity, is reported the same way wherever
 * staff look — the balance service, the student model accessors, the student's
 * fee page and statement, the student fee summary, the arrears report, the fee
 * dashboard and the expected-revenue report.
 *
 * That property was genuinely broken before this suite existed:
 *
 *   - the statement built its closing figure only from ledger_entries, and the
 *     ledger is written by payments/adjustments/refunds but never by a charge,
 *     so a student whose fees predate the ledger had a statement that disagreed
 *     with their own balance by the whole amount billed;
 *   - arrears, the student-fee-status report and the dashboard each summed
 *     fee_payments themselves, which counted REVERSED payments as collected and
 *     credited a split "pay total balance" payment wholly to its first fee;
 *   - the student fee status report plucked a column that does not exist
 *     (student_fee_assignment_id), so every learner showed total_paid = 0.
 *
 * If any screen drifts out of agreement again, these tests fail.
 */
class FeeReconciliationTest extends TestCase
{
    use RefreshDatabase;

    protected User $accountant;

    protected AcademicYear $year;

    protected Term $term;

    protected SchoolClass $class;

    protected ClassSection $classSection;

    protected Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->accountant = $this->makeAccountant();

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

        $this->classSection = ClassSection::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $this->class->class_id,
            'section_id' => $section->section_id,
        ]);

        $this->student = $this->makeStudent($this->year, $this->classSection);
    }

    // ───────────────────────────── helpers ─────────────────────────────

    private function makeAccountant(): User
    {
        $user = User::factory()->create(['name' => 'Reconciliation Accountant', 'email' => 'recon.' . uniqid() . '@test.local']);
        $user->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));

        $user->staff()->create([
            'first_name' => 'Recon',
            'middle_name' => null,
            'last_name' => 'Accountant',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'phone_primary' => '0711000000',
            'work_email' => 'recon-staff.' . uniqid() . '@test.local',
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

    /**
     * Student reports require students.* permissions, which the Accountant
     * deliberately does not hold (RbacSeeder), so those screens are checked
     * with a role that legitimately owns them.
     */
    private function makeAdmin(): User
    {
        $user = User::factory()->create(['name' => 'Reconciliation Admin', 'email' => 'recon-admin.' . uniqid() . '@test.local']);
        $user->roles()->sync(Role::where('role_name', 'Admin')->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function makeStudent(AcademicYear $year, ClassSection $classSection): Student
    {
        $student = Student::create([
            'admission_no' => 'REC' . substr(uniqid(), -8),
            'first_name' => 'Recon',
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

        return $student;
    }

    /** Assign a fee directly to the student and return the assignment. */
    private function assignFee(Student $student, float $amount, ?AcademicYear $year = null, ?string $term = null): StudentFeeAssignment
    {
        $year = $year ?? $this->year;

        $category = FeeCategory::create(['name' => 'Tuition-' . uniqid(), 'type' => 'mandatory']);

        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $this->class->class_id,
            'category_id' => $category->category_id,
            'amount' => $amount,
            'term' => $term ?? 'T1',
            'payment_frequency' => 'termly',
            'due_date' => '2026-02-01',
            'status' => 'active',
            'created_by' => $this->accountant->id,
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id,
            'term' => $term ?? 'T1',
            'term_id' => $term === null || $term === 'T1' ? $this->term->term_id : null,
            'amount' => $amount,
            'final_amount' => $amount,
            'paid_amount' => 0,
            'assigned_by' => $this->accountant->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);
    }

    /** Record a payment through the real collection endpoint. */
    private function pay(Student $student, string $assignmentId, float $amount, string $method = 'cash', ?string $date = null): FeePayment
    {
        $this->actingAs($this->accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => $assignmentId,
                'amount' => $amount,
                'payment_date' => $date ?? now()->format('Y-m-d'),
                'payment_method' => $method,
                'transaction_id' => 'TXN-' . uniqid(),
                'remarks' => 'reconciliation test',
            ])
            ->assertRedirect();

        return FeePayment::latest('payment_id')->firstOrFail();
    }

    private function reverse(FeePayment $payment): void
    {
        $this->actingAs($this->accountant)
            ->post(route('fees.payments.reverse', $payment->payment_id), ['reason' => 'reconciliation test reversal'])
            ->assertRedirect();
    }

    private function balances(): FeeBalanceService
    {
        return app(FeeBalanceService::class);
    }

    private function statement(Student $student): array
    {
        return app(LedgerService::class)->getStudentStatement($student->student_id);
    }

    /**
     * THE central assertion: the outstanding figure is identical on every screen.
     *
     * $formatted must be Money::format($expected) — the module's single money
     * standard — so this also proves the screens render it consistently.
     */
    private function assertPositionAgreesEverywhere(Student $student, float $expected): void
    {
        $student->refresh();
        $formatted = Money::format($expected);

        $this->assertSame(
            $expected,
            round($this->balances()->balanceForStudent($student->student_id), 2),
            'FeeBalanceService::balanceForStudent() disagrees.'
        );

        $this->assertSame(
            $expected,
            round((float) $student->balance_fee, 2),
            'Student::balance_fee disagrees with FeeBalanceService.'
        );

        $this->assertSame(
            $expected,
            round((float) $student->fee_summary['balance'], 2),
            'Student::fee_summary[balance] disagrees with FeeBalanceService.'
        );

        $this->assertSame(
            $expected,
            round((float) $this->statement($student)['closing'], 2),
            'The student statement does not reconcile with the student balance.'
        );

        // --- Rendered screens. Each must show the same KES figure. ---

        $this->actingAs($this->accountant)
            ->get('/fee-management/' . $student->student_id)
            ->assertOk()
            ->assertSee($formatted, false);

        $this->actingAs($this->accountant)
            ->get(route('fees.assignments.student-summary', $student->student_id))
            ->assertOk()
            ->assertSee($formatted, false);

        $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index'))
            ->assertOk()
            ->assertSee($formatted, false);

        $this->actingAs($this->accountant)
            ->get(route('fees.dashboard'))
            ->assertOk()
            ->assertSee($formatted, false);

        $this->actingAs($this->accountant)
            ->get(route('fees.reports.expected-revenue'))
            ->assertOk()
            ->assertSee($formatted, false);
    }

    // ───────────────────── the required scenario ─────────────────────

    /**
     * The scenario from the brief: assign KES 30,000, pay KES 10,000, and
     * KES 20,000 must be outstanding everywhere.
     */
    public function test_30000_assigned_and_10000_paid_shows_20000_outstanding_on_every_screen(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $this->assertPositionAgreesEverywhere($this->student, 20000.00);
    }

    public function test_arrears_summary_totals_equal_its_own_table(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $response = $this->actingAs($this->accountant)->get(route('fees.arrears.index'))->assertOk();

        // Expected − Collected = Outstanding, and Outstanding is the figure the
        // table row shows. The headline used to be computed from raw
        // fee_payments while the rows used assignments, so the two could differ.
        $response->assertSee(Money::format(30000), false);   // expected
        $response->assertSee(Money::format(10000), false);   // collected
        $response->assertSee(Money::format(20000), false);   // outstanding
    }

    public function test_full_payment_leaves_a_zero_balance_everywhere(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 30000);

        $this->assertPositionAgreesEverywhere($this->student, 0.00);
    }

    public function test_multiple_partial_payments_accumulate_consistently(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 5000);
        $this->pay($this->student, (string) $assignment->id, 2500, 'online');
        $this->pay($this->student, (string) $assignment->id, 1500, 'bank_transfer');

        $this->assertPositionAgreesEverywhere($this->student, 21000.00);
    }

    public function test_a_reversed_payment_is_not_counted_as_collected_anywhere(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);
        $reversed = $this->pay($this->student, (string) $assignment->id, 5000);

        $this->reverse($reversed);

        // Only the surviving 10,000 counts, so 20,000 is outstanding — on every
        // screen, including the student fee status report which used to show
        // total_paid = 0 for everybody.
        $this->assertPositionAgreesEverywhere($this->student, 20000.00);

        $this->assertSame(
            10000.00,
            round($this->balances()->paidForAssignment($assignment->id), 2),
            'A reversed payment must not count towards the paid total.'
        );
    }

    /** A reversed payment must also drop out of the student fee status report. */
    public function test_student_fee_status_report_excludes_reversed_payments(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);
        $reversed = $this->pay($this->student, (string) $assignment->id, 5000);
        $this->reverse($reversed);

        $this->actingAs($this->makeAdmin())
            ->get(route('student-reports.fee-status', ['academic_year_id' => $this->year->academic_year_id]))
            ->assertOk()
            // 10,000 paid (not 15,000) and 20,000 outstanding.
            ->assertSee(Money::format(10000), false)
            ->assertSee(Money::format(20000), false);
    }

    /**
     * A split "pay total balance" payment writes payment_allocations and points
     * its direct link at only the first fee. Both the SQL definition used by the
     * reports and the PHP definition used by the balance service must attribute
     * it identically, and every screen must agree.
     */
    public function test_split_allocation_payment_is_attributed_identically_by_sql_and_php(): void
    {
        $first = $this->assignFee($this->student, 20000);
        $second = $this->assignFee($this->student, 10000);

        // 'total' makes the controller spread the payment across outstanding fees.
        $this->pay($this->student, 'total', 12000);

        $this->assertGreaterThan(0, DB::table('payment_allocations')->count(), 'Expected the split payment to write allocation rows.');

        $php = $this->balances()->paidForAssignments([$first->id, $second->id]);
        $sql = DB::table('student_fee_assignments as sfa')
            ->leftJoin(DB::raw($this->balances()->paidTotalsSubquery()), 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->whereIn('sfa.id', [$first->id, $second->id])
            ->pluck('paid_totals.paid_total', 'sfa.id');

        foreach ([$first->id, $second->id] as $id) {
            $this->assertSame(
                round($php[$id] ?? 0.0, 2),
                round((float) $sql[$id], 2),
                "paidTotalsSubquery() and paidForAssignments() disagree for assignment {$id}."
            );
        }

        $this->assertSame(12000.00, round(array_sum($php), 2), 'The split payment must be fully attributed, exactly once.');
        $this->assertPositionAgreesEverywhere($this->student, 18000.00);
    }

    /**
     * The SQL definition drives the arrears screen. Prove it agrees with the PHP
     * definition the balance service exposes for a mixture of direct and
     * reversed payments.
     */
    public function test_paid_totals_sql_matches_php_for_direct_and_reversed_payments(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 4000);
        $reversed = $this->pay($this->student, (string) $assignment->id, 7000);
        $this->reverse($reversed);

        $php = round($this->balances()->paidForAssignment($assignment->id), 2);

        $sql = round((float) DB::table('student_fee_assignments as sfa')
            ->leftJoin(DB::raw($this->balances()->paidTotalsSubquery()), 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->where('sfa.id', $assignment->id)
            ->value('paid_totals.paid_total'), 2);

        $this->assertSame(4000.00, $php, 'Reversed payments must be excluded from the PHP paid total.');
        $this->assertSame($php, $sql, 'The SQL paid total must equal the PHP paid total.');
    }

    public function test_an_overpayment_shows_as_a_credit_consistently(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 35000);

        $this->assertPositionAgreesEverywhere($this->student, -5000.00);
    }

    /**
     * A previous-year balance is part of the student's position, while the
     * arrears report is deliberately scoped to one academic year — so the
     * position captured in both must reflect that distinction rather than
     * silently disagreeing with each other.
     */
    public function test_previous_year_balance_is_included_in_the_student_position(): void
    {
        $previousYear = AcademicYear::create([
            'name' => 'AY-PREV-' . uniqid(),
            'start_date' => '2025-01-01',
            'end_date' => '2025-12-31',
            'is_current' => false,
        ]);

        $this->assignFee($this->student, 5000, $previousYear, 'T1');
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        // All-time position: 35,000 charged − 10,000 paid = 25,000.
        $this->assertSame(
            25000.00,
            round($this->balances()->balanceForStudent($this->student->student_id), 2)
        );

        // The statement covers the student's whole position, so it agrees.
        $this->assertSame(25000.00, round((float) $this->statement($this->student)['closing'], 2));

        // Arrears defaults to the current year, so it shows only this year's
        // 20,000 — and says so via its year filter.
        $this->actingAs($this->accountant)
            ->get(route('fees.arrears.index'))
            ->assertOk()
            ->assertSee(Money::format(20000), false);
    }

    /**
     * Viewing a student's fee page must not write financial rows. Opening the
     * statement used to trigger a lazy "seedOpeningBalance" insert, which meant
     * the figures a student saw depended on whether anyone had opened their page
     * before, and a GET changed the books.
     */
    public function test_opening_the_statement_page_does_not_create_ledger_rows(): void
    {
        $assignment = $this->assignFee($this->student, 30000);

        $before = DB::table('ledger_entries')->count();

        $this->actingAs($this->accountant)
            ->get('/fee-management/' . $this->student->student_id)
            ->assertOk();

        $this->actingAs($this->accountant)
            ->get('/fee-management/' . $this->student->student_id)
            ->assertOk();

        $this->assertSame(
            $before,
            DB::table('ledger_entries')->count(),
            'Opening the statement page must not insert ledger entries.'
        );

        // And the statement still reconciles for a student with no ledger rows.
        $this->assertSame(30000.00, round((float) $this->statement($this->student)['closing'], 2));
    }

    /**
     * The pending-approvals screen must not be swallowed by the parameter route.
     *
     * `/fees/adjustments/{id}` was registered before `/fees/adjustments/pending`,
     * and Laravel matches in registration order — so "pending" was captured as an
     * id and the screen was unreachable. This asserts the real route collection
     * ORDER rather than `route:list` output (which is sorted by URI and would hide
     * the defect) and then proves the screen renders.
     */
    public function test_pending_adjustments_route_is_not_shadowed_by_the_id_route(): void
    {
        $routes = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
            ->map(fn ($route) => $route->uri())
            ->filter(fn ($uri) => str_starts_with($uri, 'fees/adjustments'))
            ->values()
            ->all();

        $literal = array_search('fees/adjustments/pending', $routes, true);
        $parameter = array_search('fees/adjustments/{id}', $routes, true);

        $this->assertNotFalse($literal, '/fees/adjustments/pending is not registered.');
        $this->assertNotFalse($parameter, '/fees/adjustments/{id} is not registered.');
        $this->assertLessThan(
            $parameter,
            $literal,
            'The literal /adjustments/pending route must be registered before /adjustments/{id}, '
                . 'otherwise "pending" is bound as an id and the screen 404s.'
        );

        $this->actingAs($this->accountant)
            ->get(route('fees.adjustments.pending'))
            ->assertOk();
    }

    /**
     * Legacy bootstrap opening rows must be folded into the derived opening
     * rather than listed, otherwise a student carries the same position twice.
     */
    public function test_legacy_bootstrap_opening_rows_do_not_double_count(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        DB::table('ledger_entries')->insert([
            'student_id' => $this->student->student_id,
            'entry_date' => now(),
            'description' => 'Opening balance (carried forward)',
            'entry_type' => 'opening_balance',
            'debit' => 20000,
            'credit' => 0,
            'balance_after' => 20000,
            'source' => 'bootstrap',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $statement = $this->statement($this->student);

        // The legacy row must not appear as a listed movement line...
        $this->assertSame(
            0,
            $statement['entries']->where('entry_type', 'opening_balance')->count(),
            'Legacy bootstrap rows must not be listed as statement movements.'
        );

        // ...and must not be counted twice: the closing figure is still the
        // student's real balance.
        $this->assertSame(20000.00, round((float) $statement['closing'], 2));
        $this->assertPositionAgreesEverywhere($this->student, 20000.00);
    }

    /**
     * A discount is applied by final_amount, so it must reduce the position the
     * student is shown — and reduce it identically everywhere.
     */
    public function test_a_discount_reduces_the_balance_consistently(): void
    {
        $category = FeeCategory::create(['name' => 'Tuition-' . uniqid(), 'type' => 'mandatory']);

        $structure = FeeStructure::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $this->class->class_id,
            'category_id' => $category->category_id,
            'amount' => 30000,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'due_date' => '2026-02-01',
            'status' => 'active',
            'created_by' => $this->accountant->id,
        ]);

        $assignment = StudentFeeAssignment::create([
            'student_id' => $this->student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $this->year->academic_year_id,
            'term' => 'T1',
            'term_id' => $this->term->term_id,
            'amount' => 30000,
            'discount_amount' => 5000,
            'final_amount' => 25000,
            'paid_amount' => 0,
            'assigned_by' => $this->accountant->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);

        $this->pay($this->student, (string) $assignment->id, 10000);

        // 25,000 net payable − 10,000 paid = 15,000.
        $this->assertPositionAgreesEverywhere($this->student, 15000.00);
    }
}
