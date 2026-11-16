<?php

namespace Tests\Feature;

use App\Http\Controllers\RefundController;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\ClassSection;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\Refund;
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
 * The refund rules, and the balance consequence of a refund.
 *
 * Two things were wrong before this suite existed, and both are financial:
 *
 *   1. A refund could be requested for any amount at all. There was no ceiling,
 *      so the live register holds a KES 200,000 refund against a student who had
 *      paid KES 28,900. Nothing in the request path, the approval path or the
 *      payout path compared the refund to the money actually held.
 *
 *   2. A completed refund did not reduce what the student had paid. The balance
 *      service ignored refunds completely, while the ledger posted a refund as a
 *      CREDIT — money handed back was recorded as if it settled a fee. So
 *      refunding a student made them look like they owed less, which is the
 *      wrong direction for cash leaving the school.
 *
 * The rule now is the one a bursar would state out loud: a student can only be
 * refunded money the school is actually holding for them, and once it is handed
 * back they owe it again.
 *
 *     maximum refundable = valid payments − completed refunds − requests in flight
 *     effective paid     = valid payments − completed refunds
 *     outstanding        = charges − effective paid
 */
class RefundReconciliationTest extends TestCase
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

        $this->student = $this->makeStudent();
    }

    // ───────────────────────────── helpers ─────────────────────────────

    private function makeAccountant(): User
    {
        $user = User::factory()->create([
            'name' => 'Refund Reconciliation Accountant',
            'email' => 'refund.recon.' . uniqid() . '@test.local',
        ]);
        $user->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));

        $user->staff()->create([
            'first_name' => 'Refund',
            'middle_name' => null,
            'last_name' => 'Accountant',
            'date_of_birth' => '2000-01-01',
            'gender' => 'female',
            'phone_primary' => '0711000001',
            'work_email' => 'refund-staff.' . uniqid() . '@test.local',
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

        return $user->load('roles.permissions');
    }

    private function makeStudent(): Student
    {
        $student = Student::create([
            'admission_no' => 'RR' . substr(uniqid(), -8),
            'first_name' => 'Refund',
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
            'class_section_id' => $this->classSection->class_section_id,
            'academic_year_id' => $this->year->academic_year_id,
            'enrollment_date' => now(),
            'status' => 'active',
            'is_current' => true,
        ]);

        return $student;
    }

    private function assignFee(Student $student, float $amount): StudentFeeAssignment
    {
        $category = FeeCategory::create(['name' => 'Tuition-' . uniqid(), 'type' => 'mandatory']);

        $structure = FeeStructure::create([
            'academic_year_id' => $this->year->academic_year_id,
            'class_id' => $this->class->class_id,
            'category_id' => $category->category_id,
            'amount' => $amount,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'due_date' => '2026-02-01',
            'status' => 'active',
            'created_by' => $this->accountant->id,
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $this->year->academic_year_id,
            'term' => 'T1',
            'term_id' => $this->term->term_id,
            'amount' => $amount,
            'final_amount' => $amount,
            'paid_amount' => 0,
            'assigned_by' => $this->accountant->id,
            'assigned_date' => now(),
            'status' => 'active',
        ]);
    }

    /** Record a payment through the real collection endpoint. */
    private function pay(Student $student, string $assignmentId, float $amount): FeePayment
    {
        $this->actingAs($this->accountant)
            ->post("/fee-management/{$student->student_id}/store-payment", [
                'student_fee_assignment_id' => $assignmentId,
                'amount' => $amount,
                'payment_date' => now()->format('Y-m-d'),
                'payment_method' => 'cash',
                'transaction_id' => 'TXN-' . uniqid(),
                'remarks' => 'refund reconciliation test',
            ])
            ->assertRedirect();

        return FeePayment::latest('payment_id')->firstOrFail();
    }

    private function reverse(FeePayment $payment): void
    {
        $this->actingAs($this->accountant)
            ->post(route('fees.payments.reverse', $payment->payment_id), ['reason' => 'refund reconciliation test'])
            ->assertRedirect();
    }

    private function account(float $balance): BankAccount
    {
        return BankAccount::create([
            'account_name' => 'Equity Main — ' . uniqid(),
            'account_number' => (string) random_int(1000000000, 9999999999),
            'bank_name' => 'Equity Bank',
            'opening_balance' => $balance,
            'current_balance' => $balance,
            'account_type' => 'bank',
            'status' => 'active',
        ]);
    }

    /**
     * Raise a refund through the real request endpoint, then approve and
     * complete it, so every gate the application has is exercised.
     */
    private function refund(Student $student, float $amount, ?StudentFeeAssignment $assignment = null, ?BankAccount $account = null): Refund
    {
        $account = $account ?? $this->account(500000);

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.store'), [
                'student_id' => $student->student_id,
                'student_fee_assignment_id' => optional($assignment)->id,
                'amount' => $amount,
                'reason' => 'Refund reconciliation test',
            ])
            ->assertRedirect();

        $refund = Refund::latest('id')->firstOrFail();

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.approve', $refund->id), ['approval_notes' => 'ok'])
            ->assertRedirect();

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.complete', $refund->id), [
                'refund_method' => 'bank',
                'bank_account_id' => $account->account_id,
                'refund_reference' => 'RF-' . $refund->id,
            ])
            ->assertRedirect();

        return $refund->fresh();
    }

    /**
     * The payment-status report needs students.* rather than fees.*, so it is
     * checked as an administrator.
     */
    private function makeAdmin(): User
    {
        $user = User::factory()->create([
            'name' => 'Refund Reconciliation Admin',
            'email' => 'refund-admin.' . uniqid() . '@test.local',
        ]);
        $user->roles()->sync(Role::where('role_name', 'Admin')->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function balances(): FeeBalanceService
    {
        return app(FeeBalanceService::class);
    }

    private function statement(Student $student): array
    {
        return app(LedgerService::class)->getStudentStatement($student->student_id);
    }

    private function attemptRefund(Student $student, float $amount, ?StudentFeeAssignment $assignment): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->accountant)->post(route('fees.refunds.store'), [
            'student_id' => $student->student_id,
            'student_fee_assignment_id' => optional($assignment)->id,
            'amount' => $amount,
            'reason' => 'Refund reconciliation test',
        ]);
    }

    /** The outstanding figure must be identical on every screen that shows it. */
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

        // The payment-status report (students.* permission, so as an admin).
        $this->actingAs($this->makeAdmin())
            ->get(route('student-reports.fee-status', ['academic_year_id' => $this->year->academic_year_id]))
            ->assertOk()
            ->assertSee($formatted, false);
    }

    // ───────────────────── 1. exact refund ─────────────────────

    /**
     * Charges 30,000, paid 30,000, refunded 5,000: the student paid 25,000 and
     * owes 5,000 again, on every screen.
     */
    public function test_exact_refund_reduces_effective_paid_and_raises_outstanding(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 30000);

        $refund = $this->refund($this->student, 5000, $assignment);

        $this->assertSame('completed', $refund->status);

        $summary = $this->balances()->summaryForStudent($this->student->student_id);
        $this->assertSame(30000.00, round($summary['payments'], 2), 'Gross valid payments must stay 30,000.');
        $this->assertSame(5000.00, round($summary['refunded'], 2));
        $this->assertSame(25000.00, round($summary['paid'], 2), 'Effective paid must be 25,000.');
        $this->assertSame(5000.00, round($summary['balance'], 2), 'Outstanding must be 5,000.');

        // The denormalised column every report and dashboard reads.
        $this->assertSame(25000.00, round((float) $assignment->fresh()->paid_amount, 2));

        $this->assertPositionAgreesEverywhere($this->student, 5000.00);
    }

    // ───────────────────── 2. partial payment refund ─────────────────────

    public function test_refund_against_a_partial_payment(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $this->refund($this->student, 3000, $assignment);

        $summary = $this->balances()->summaryForStudent($this->student->student_id);
        $this->assertSame(7000.00, round($summary['paid'], 2), 'Effective paid must be 7,000.');
        $this->assertSame(23000.00, round($summary['balance'], 2), 'Outstanding must be 23,000.');

        $this->assertPositionAgreesEverywhere($this->student, 23000.00);
    }

    // ───────────────────── 3. refund cannot exceed payments ─────────────────────

    public function test_refund_cannot_exceed_the_amount_paid(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $response = $this->attemptRefund($this->student, 12000, $assignment);

        $response->assertSessionHasErrors('amount');

        $this->assertStringContainsString(
            RefundController::EXCEEDS_REFUNDABLE,
            session('errors')->get('amount')[0],
            'The rejection must carry the documented wording.'
        );

        // Refused means nothing was written. The payment itself does post a
        // ledger entry, so this asserts on refund movements specifically.
        $this->assertSame(0, Refund::count());
        $this->assertSame(0, DB::table('ledger_entries')->where('entry_type', 'refund')->count());
    }

    public function test_refund_within_the_paid_amount_is_accepted(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $this->attemptRefund($this->student, 10000, $assignment)->assertSessionHasNoErrors();

        $this->assertSame(1, Refund::count());
        $this->assertSame(0.00, $this->balances()->refundSummaryForStudent($this->student->student_id)['maxRefundable']);
    }

    // ───────────────────── 4. multiple refunds ─────────────────────

    public function test_multiple_refunds_reduce_the_refundable_balance(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 20000);
        $account = $this->account(500000);

        $this->refund($this->student, 5000, $assignment, $account);

        $this->assertSame(
            15000.00,
            $this->balances()->refundSummaryForStudent($this->student->student_id)['maxRefundable']
        );

        $this->refund($this->student, 4000, $assignment, $account);

        $this->assertSame(
            11000.00,
            $this->balances()->refundSummaryForStudent($this->student->student_id)['maxRefundable'],
            '20,000 paid less 5,000 and 4,000 refunded leaves 11,000.'
        );

        $this->assertSame(11000.00, round($this->balances()->summaryForStudent($this->student->student_id)['paid'], 2));
        $this->assertPositionAgreesEverywhere($this->student, 19000.00);
    }

    public function test_refunds_that_would_together_exceed_payments_are_refused(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 20000);
        $account = $this->account(500000);

        $this->refund($this->student, 5000, $assignment, $account);
        $this->refund($this->student, 4000, $assignment, $account);

        // 11,000 is available; 11,001 is not.
        $this->attemptRefund($this->student, 11000, $assignment)->assertSessionHasNoErrors();
        $this->attemptRefund($this->student, 1000, $assignment)->assertSessionHasErrors('amount');
    }

    // ───────────────────── 5. reversed payment ─────────────────────

    public function test_a_reversed_payment_is_not_refundable(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $payment = $this->pay($this->student, (string) $assignment->id, 10000);

        $this->reverse($payment);

        $refundable = $this->balances()->refundSummaryForStudent($this->student->student_id);

        $this->assertSame(0.00, $refundable['payments'], 'A reversed payment is not money held.');
        $this->assertSame(0.00, $refundable['maxRefundable']);

        $this->attemptRefund($this->student, 1000, $assignment)->assertSessionHasErrors('amount');
        $this->assertSame(0, Refund::count());
    }

    // ───────────────────── 6. split / allocation payment ─────────────────────

    /**
     * A refund must not disturb how a split payment was attributed across
     * charges, and the SQL and PHP definitions must still agree afterwards.
     */
    public function test_refund_after_a_split_allocation_payment_keeps_attribution_intact(): void
    {
        $first = $this->assignFee($this->student, 20000);
        $second = $this->assignFee($this->student, 10000);

        // 'total' spreads the payment across outstanding charges, writing
        // payment_allocations rows.
        $this->pay($this->student, 'total', 12000);

        $allocationsBefore = DB::table('payment_allocations')
            ->selectRaw('student_fee_assignment_id, SUM(amount) as total')
            ->groupBy('student_fee_assignment_id')
            ->pluck('total', 'student_fee_assignment_id')
            ->all();

        $this->assertNotEmpty($allocationsBefore, 'Expected the split payment to write allocation rows.');

        $this->refund($this->student, 3000, $first);

        // Attribution of the payment itself is untouched.
        $this->assertSame(
            array_map(fn ($v) => round((float) $v, 2), $allocationsBefore),
            DB::table('payment_allocations')
                ->selectRaw('student_fee_assignment_id, SUM(amount) as total')
                ->groupBy('student_fee_assignment_id')
                ->pluck('total', 'student_fee_assignment_id')
                ->map(fn ($v) => round((float) $v, 2))
                ->all(),
            'Refunding must not rewrite how the original payment was allocated.'
        );

        $gross = $this->balances()->paidForAssignments([$first->id, $second->id]);
        $effective = $this->balances()->effectivePaidForAssignments([$first->id, $second->id]);

        $this->assertSame(round($gross[$first->id] - 3000, 2), $effective[$first->id], 'The refund must come off the charge it names.');
        $this->assertSame(round($gross[$second->id], 2), $effective[$second->id], 'The other charge must be untouched.');

        // The SQL definition the reports use agrees with the PHP definition.
        $sql = DB::table('student_fee_assignments as sfa')
            ->leftJoin(DB::raw($this->balances()->paidTotalsSubquery()), 'paid_totals.student_fee_assignment_id', '=', 'sfa.id')
            ->whereIn('sfa.id', [$first->id, $second->id])
            ->pluck('paid_totals.paid_total', 'sfa.id');

        foreach ([$first->id, $second->id] as $id) {
            $this->assertSame(
                round($effective[$id], 2),
                round((float) $sql[$id], 2),
                "paidTotalsSubquery() and effectivePaidForAssignments() disagree for assignment {$id} after a refund."
            );
        }

        // 30,000 charged, 12,000 paid, 3,000 handed back = 21,000 outstanding.
        $this->assertPositionAgreesEverywhere($this->student, 21000.00);
    }

    // ───────────────────── 7. visibility of the refund ─────────────────────

    public function test_a_completed_refund_is_visible_in_the_statement_audit_trail_and_bank_ledger(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 30000);
        $account = $this->account(50000);

        $refund = $this->refund($this->student, 5000, $assignment, $account);

        // --- student ledger: a DEBIT, because money left the school ---
        $entry = DB::table('ledger_entries')->where('id', $refund->ledger_entry_id)->first();

        $this->assertNotNull($entry, 'The completion must post a ledger entry.');
        $this->assertSame('refund', $entry->entry_type);
        $this->assertSame(5000.00, round((float) $entry->debit, 2), 'A refund is a debit: the student owes it again.');
        $this->assertSame(0.00, round((float) $entry->credit, 2), 'A refund must not be posted as a credit.');

        // --- the statement shows it and still closes on the balance ---
        $statement = $this->statement($this->student);
        $refundLines = collect($statement['entries'])->filter(fn ($line) => $line->entry_type === 'refund');

        $this->assertTrue($refundLines->contains(fn ($line) => round((float) $line->debit, 2) === 5000.00));
        $this->assertSame(5000.00, round($statement['closing'], 2));

        // --- audit trail ---
        $this->assertSame(1, AuditTrail::where('module', 'Fees')->where('action', 'REFUND COMPLETED')->where('record_id', $refund->id)->count());
        $this->assertSame(1, AuditTrail::where('module', 'Fees')->where('action', 'REFUND REQUEST')->where('record_id', $refund->id)->count());
        $this->assertSame(1, AuditTrail::where('module', 'Fees')->where('action', 'REFUND APPROVED')->where('record_id', $refund->id)->count());

        // --- bank ledger: the payout is on the statement and the balance moved ---
        $withdrawal = BankTransaction::where('account_id', $account->account_id)
            ->where('transaction_type', 'withdrawal')
            ->where('amount', 5000.00)
            ->first();

        $this->assertNotNull($withdrawal, 'The payout must appear on the bank statement.');
        $this->assertStringStartsWith('Refund #' . $refund->id, $withdrawal->description);
        $this->assertSame(45000.00, round((float) $account->fresh()->current_balance, 2));
    }

    // ───────────────────── completion-time enforcement ─────────────────────

    /**
     * The request path can be bypassed (a row created directly, or the position
     * changing between approval and payout). Completion is where money moves, so
     * it must refuse on its own.
     */
    public function test_completion_refuses_a_refund_larger_than_the_position(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        // 10,000 paid, 50,000 requested — created directly to simulate a legacy
        // or out-of-band row that never passed the request-time guard.
        $refund = Refund::create([
            'student_id' => $this->student->student_id,
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 50000,
            'reason' => 'Legacy-shaped oversized refund',
            'status' => 'requested',
            'requested_by' => $this->accountant->id,
            'requested_at' => now(),
        ]);

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.approve', $refund->id), ['approval_notes' => 'ok'])
            ->assertRedirect();

        $account = $this->account(500000);

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.complete', $refund->id), [
                'refund_method' => 'bank',
                'bank_account_id' => $account->account_id,
            ])
            ->assertRedirect();

        $levels = collect(session('flash_notification'))->pluck('level')->all();
        $this->assertContains('danger', $levels);

        // Nothing moved.
        $this->assertSame('approved', $refund->fresh()->status);
        $this->assertNull($refund->fresh()->ledger_entry_id);
        $this->assertSame(0, BankTransaction::where('account_id', $account->account_id)->count());
        $this->assertSame(500000.00, round((float) $account->fresh()->current_balance, 2));
    }

    /**
     * A request that is still in flight holds its amount: otherwise a queue of
     * requests could each pass validation and the last to be paid would overdraw
     * the student.
     */
    public function test_a_request_in_flight_reserves_the_refundable_amount(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 20000);

        $this->attemptRefund($this->student, 20000, $assignment)->assertSessionHasNoErrors();

        $refundable = $this->balances()->refundSummaryForStudent($this->student->student_id);

        $this->assertSame(20000.00, round($refundable['pending'], 2));
        $this->assertSame(0.00, $refundable['maxRefundable']);

        $this->attemptRefund($this->student, 1000, $assignment)->assertSessionHasErrors('amount');

        // Rejecting the request releases the reservation.
        $pending = Refund::latest('id')->firstOrFail();
        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.reject', $pending->id), ['rejection_reason' => 'not needed'])
            ->assertRedirect();

        $this->assertSame(
            20000.00,
            $this->balances()->refundSummaryForStudent($this->student->student_id)['maxRefundable']
        );
    }

    // ───────────────────── attribution ─────────────────────

    /**
     * A refund with no charge cannot be attributed, so it cannot be taken off a
     * specific fee and would leave the student's balance disagreeing with every
     * per-charge figure. The request is refused.
     */
    public function test_a_refund_must_name_the_charge_or_payment_it_comes_from(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.store'), [
                'student_id' => $this->student->student_id,
                'amount' => 1000,
                'reason' => 'No charge selected',
            ])
            ->assertSessionHasErrors('student_fee_assignment_id');

        $this->assertSame(0, Refund::count());
    }

    /**
     * The charge can be inferred from the payment being refunded, which is the
     * normal case for an overpayment.
     */
    public function test_the_charge_is_taken_from_the_payment_when_not_chosen_directly(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $payment = $this->pay($this->student, (string) $assignment->id, 10000);

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.store'), [
                'student_id' => $this->student->student_id,
                'payment_id' => $payment->payment_id,
                'amount' => 1000,
                'reason' => 'Refund against a specific receipt',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame($assignment->id, (int) Refund::latest('id')->firstOrFail()->student_fee_assignment_id);
    }

    public function test_a_refund_cannot_be_raised_against_another_students_charge(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $other = $this->makeStudent();

        $this->actingAs($this->accountant)
            ->post(route('fees.refunds.store'), [
                'student_id' => $other->student_id,
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 1000,
                'reason' => 'Wrong learner',
            ])
            ->assertSessionHasErrors('student_fee_assignment_id');

        $this->assertSame(0, Refund::count());
    }

    // ───────────────────── legacy shape ─────────────────────

    /**
     * The live database holds a completed refund with no charge attached. It must
     * not be rewritten or dropped, and it must still reduce what the student has
     * effectively paid — money left the school, whichever charge it came from.
     */
    public function test_an_unattributed_completed_refund_still_reduces_effective_paid(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        // Deliberately the legacy shape: no assignment, no payment.
        Refund::create([
            'student_id' => $this->student->student_id,
            'amount' => 4000,
            'reason' => 'Legacy unattributed refund',
            'status' => 'completed',
            'completed_at' => now(),
            'requested_by' => $this->accountant->id,
            'requested_at' => now(),
        ]);

        $summary = $this->balances()->summaryForStudent($this->student->student_id);

        $this->assertSame(10000.00, round($summary['payments'], 2));
        $this->assertSame(4000.00, round($summary['refunded'], 2), 'An unattributable refund still counts against the student.');
        $this->assertSame(6000.00, round($summary['paid'], 2));
        $this->assertSame(24000.00, round($summary['balance'], 2));
    }

    /**
     * A refund cannot be requested for more than the payments it is based on,
     * even when those payments are spread across charges.
     */
    public function test_the_cap_is_the_students_total_payments_not_a_single_charge(): void
    {
        $first = $this->assignFee($this->student, 30000);
        $second = $this->assignFee($this->student, 20000);

        $this->pay($this->student, (string) $first->id, 30000);
        $this->pay($this->student, (string) $second->id, 20000);

        $this->assertSame(
            50000.00,
            $this->balances()->refundSummaryForStudent($this->student->student_id)['maxRefundable']
        );

        // More than the first charge alone, but within the student's payments.
        $this->attemptRefund($this->student, 35000, $first)->assertSessionHasNoErrors();
    }

    // ───────────────────── the refund form ─────────────────────

    /**
     * The figures the form shows must be the figures the server enforces, and
     * they must be visible before submission.
     */
    public function test_the_refund_form_shows_the_refundable_position_before_submission(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 20000);
        $this->refund($this->student, 5000, $assignment);

        $payload = $this->actingAs($this->accountant)
            ->getJson(route('fees.refunds.ajax.student-payments', $this->student->student_id))
            ->assertOk()
            ->json();

        $this->assertSame(20000.00, round($payload['summary']['payments'], 2), 'Total valid payments.');
        $this->assertSame(5000.00, round($payload['summary']['refunded'], 2), 'Already refunded.');
        $this->assertSame(15000.00, round($payload['summary']['maxRefundable'], 2), 'Maximum refundable.');

        $this->actingAs($this->accountant)
            ->get(route('fees.refunds.create'))
            ->assertOk()
            ->assertSee('Total valid payments', false)
            ->assertSee('Already refunded', false)
            ->assertSee('Maximum refundable', false)
            ->assertSee('Requested refund amount', false);
    }

    public function test_a_reversed_payment_is_not_offered_as_a_refund_source(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $payment = $this->pay($this->student, (string) $assignment->id, 10000);
        $this->reverse($payment);

        $payload = $this->actingAs($this->accountant)
            ->getJson(route('fees.refunds.ajax.student-payments', $this->student->student_id))
            ->assertOk()
            ->json();

        $this->assertSame([], $payload['payments'], 'A reversed payment is not money the school holds.');
        $this->assertSame(0.00, round($payload['summary']['maxRefundable'], 2));
    }

    // ───────────────────── 5. the legacy anomaly ─────────────────────

    /**
     * The live database holds a completed refund far larger than the student's
     * payments, with a ledger row crediting the student for it.
     *
     * Both must be REPORTED, and neither may be touched: the money genuinely left
     * the school, and re-pointing a posted movement is an accounting decision.
     */
    public function test_a_legacy_over_refund_is_reported_and_left_completely_untouched(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        // The legacy shape: completed, one charge named, and a ledger row posted
        // as a CREDIT (the old direction).
        $refund = Refund::create([
            'student_id' => $this->student->student_id,
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 50000,
            'reason' => 'Legacy over-refund',
            'status' => 'completed',
            'completed_at' => now()->subDays(30),
            'requested_by' => $this->accountant->id,
            'requested_at' => now()->subDays(31),
        ]);

        $entry = app(LedgerService::class)->addEntry([
            'student_id' => $this->student->student_id,
            'student_fee_assignment_id' => $assignment->id,
            'entry_date' => now()->subDays(30),
            'description' => 'Refund #' . $refund->id . ': legacy',
            'entry_type' => 'refund',
            'debit' => 0,
            'credit' => 50000,
            'reference_type' => Refund::class,
            'reference_id' => $refund->id,
            'source' => 'refund',
        ]);

        $refund->update(['ledger_entry_id' => $entry->id]);

        // Snapshot every row the report is about.
        $refundsBefore = DB::table('refunds')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all();
        $ledgerBefore = DB::table('ledger_entries')->orderBy('id')->get()->map(fn ($e) => (array) $e)->all();

        $findings = app(\App\Services\FeeIntegrityService::class)->findings();

        $codes = array_column($findings, 'code');
        $this->assertContains('refund_exceeds_payments', $codes);
        $this->assertContains('refund_ledger_direction', $codes);

        $overRefund = collect($findings)->firstWhere('code', 'refund_exceeds_payments');
        $this->assertStringContainsString($this->student->admission_no, $overRefund['summary'] . implode(' ', $overRefund['detail']));
        $this->assertStringContainsString(Money::format(40000), implode(' ', $overRefund['detail']));
        $this->assertContains($refund->id, $overRefund['records']['refund_ids']);
        $this->assertContains($entry->id, $overRefund['records']['ledger_entry_ids']);

        // The explanation must state the position under the enforced rule.
        $this->assertStringContainsString(
            'Under the refund rule now enforced',
            implode(' ', $overRefund['detail'])
        );

        // NOTHING was rewritten.
        $this->assertSame($refundsBefore, DB::table('refunds')->orderBy('id')->get()->map(fn ($r) => (array) $r)->all());
        $this->assertSame($ledgerBefore, DB::table('ledger_entries')->orderBy('id')->get()->map(fn ($e) => (array) $e)->all());
    }

    public function test_a_clean_refund_raises_no_integrity_findings(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        $this->refund($this->student, 3000, $assignment);

        $this->assertSame([], app(\App\Services\FeeIntegrityService::class)->findings());

        $this->actingAs($this->accountant)
            ->get(route('fees.integrity'))
            ->assertOk()
            ->assertSee('No fee data needs review', false);

        // ...and the dashboard does not nag about it either.
        $this->actingAs($this->accountant)
            ->get(route('fees.dashboard'))
            ->assertOk()
            ->assertDontSee('Fee data needs review', false);
    }

    public function test_the_integrity_report_warns_on_the_dashboard_and_is_permission_guarded(): void
    {
        $assignment = $this->assignFee($this->student, 30000);
        $this->pay($this->student, (string) $assignment->id, 10000);

        Refund::create([
            'student_id' => $this->student->student_id,
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 50000,
            'reason' => 'Legacy over-refund',
            'status' => 'completed',
            'completed_at' => now()->subDays(30),
            'requested_by' => $this->accountant->id,
            'requested_at' => now()->subDays(31),
        ]);

        $this->actingAs($this->accountant)
            ->get(route('fees.integrity'))
            ->assertOk()
            ->assertSee('Refunds paid out for more than the student ever paid', false)
            ->assertSee($this->student->admission_no, false);

        $this->actingAs($this->accountant)
            ->get(route('fees.dashboard'))
            ->assertOk()
            ->assertSee('Fee data needs review', false);

        // A user without fees.view cannot read student financial positions.
        $stranger = User::factory()->create([
            'name' => 'Unprivileged',
            'email' => 'refund-nobody.' . uniqid() . '@test.local',
        ]);

        $this->actingAs($stranger)->get(route('fees.integrity'))->assertForbidden();
    }
}
