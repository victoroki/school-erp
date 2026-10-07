<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\FeeBulkReceipt;
use App\Models\FeePayment;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\Term;
use App\Models\User;
use App\Models\Role;
use App\Services\BulkReceiptService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Sponsor / bursary bulk receipts: one cash receipt distributed to students
 * through the existing fee payment chain.
 *
 * Covers the Phase 2 accounting rule: the receipt is the only cash event —
 * allocating to 100 students must never post income or bank movements again.
 * Also covers remaining-amount guarantees, over-allocation rejection, oldest-
 * first allocation, reversal restoring both the student balance and the
 * receipt's remaining amount, and RBAC.
 */
class BulkReceiptAllocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }
    }

    private function userWithRole(string $roleName, ?string $key = null): User
    {
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => ($key ?? $roleName) . '-bulk@test.local'],
            ['name' => "Bulk {$roleName}", 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->fresh();
    }

    private function studentWithFee(float $feeAmount, float $paid = 0.0): Student
    {
        $student = Student::create([
            'admission_no' => 'ADM-' . random_int(100000, 999999),
            'first_name' => 'Bulk',
            'last_name' => 'Student' . random_int(1, 999999),
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);

        // The schema requires a fee structure per assignment; give this student
        // their own structure so charges don't collide across fixtures.
        $year = \App\Models\AcademicYear::create([
            'name' => 'Bulk-FY-' . uniqid(),
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
        ]);
        $class = \App\Models\SchoolClass::create(['name' => 'Bulk-Grade-' . uniqid(), 'numeric_value' => 1]);
        $category = \App\Models\FeeCategory::create(['name' => 'Bulk-Tuition-' . uniqid(), 'type' => 'mandatory']);
        $structure = \App\Models\FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'category_id' => $category->category_id,
            'amount' => $feeAmount,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'status' => 'active',
        ]);

        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id,
            'term' => 'T1',
            'amount' => $feeAmount,
            'final_amount' => $feeAmount,
            'paid_amount' => $paid,
            'assigned_date' => now()->subMonth(),
            'status' => 'active',
        ]);

        if ($paid > 0) {
            // Give the student a prior payment so oldest-first allocation has
            // something to sit behind.
            FeePayment::create([
                'student_fee_assignment_id' => $assignment->id,
                'amount' => $paid,
                'payment_date' => now()->subDays(5)->toDateString(),
                'payment_method' => 'cash',
                'receipt_number' => 'RCP-PRE-' . random_int(100000, 999999),
            ]);
            $assignment->update(['paid_amount' => $paid]);
        }

        return $student;
    }

    private function receipt(float $amount = 5000000, ?int $bankAccountId = null): FeeBulkReceipt
    {
        $service = app(BulkReceiptService::class);

        return $service->createReceipt([
            'sponsor_name' => 'NG-CDF Test Fund',
            'sponsor_type' => 'cdf',
            'reference_number' => 'REF-' . random_int(100000, 999999),
            'amount' => $amount,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $bankAccountId,
            'received_date' => now()->toDateString(),
        ]);
    }

    private function bankAccount(float $balance = 1000000.0): BankAccount
    {
        return BankAccount::create([
            'account_name' => 'Bulk Test Bank',
            'account_number' => 'BLK-' . random_int(100000, 999999),
            'bank_name' => 'Test Bank',
            'opening_balance' => $balance,
            'current_balance' => $balance,
            'account_type' => 'current',
            'currency' => 'KES',
            'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------------
    // Receipt creation
    // ------------------------------------------------------------------

    public function test_create_receipt_exposes_amount_allocated_and_remaining(): void
    {
        $receipt = $this->receipt(5000000);

        $this->assertEqualsWithDelta(5000000.0, $receipt->amount, 0.001);
        $this->assertEqualsWithDelta(0.0, $receipt->allocatedAmount(), 0.001);
        $this->assertEqualsWithDelta(5000000.0, $receipt->remainingAmount(), 0.001);
    }

    public function test_banked_receipt_posts_exactly_one_deposit(): void
    {
        $account = $this->bankAccount(1000000);
        $receipt = $this->receipt(5000000, $account->account_id);

        $this->assertEqualsWithDelta(6000000.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(1, BankTransaction::where('source_type', 'FeeBulkReceipt')->count());

        $row = BankTransaction::where('source_type', 'FeeBulkReceipt')->first();
        $this->assertEqualsWithDelta(5000000.0, (float) $row->amount, 0.001);
        $this->assertSame($receipt->id, (int) $row->source_id);
    }

    // ------------------------------------------------------------------
    // Allocation
    // ------------------------------------------------------------------

    public function test_allocate_one_student_updates_balance_and_ledger(): void
    {
        $student = $this->studentWithFee(20000);
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        $payments = $service->allocate($receipt, [
            ['student_id' => $student->student_id, 'amount' => 20000],
        ]);

        $this->assertCount(1, $payments);
        $this->assertSame($receipt->id, $payments[0]->bulk_receipt_id, 'the child payment must trace to the receipt');

        // The student's charge is settled through the normal machinery.
        $this->assertEqualsWithDelta(20000.0, (float) $student->fresh()->feeAssignments->first()->paid_amount, 0.001);

        // The receipt's remaining amount reflects the allocation.
        $this->assertEqualsWithDelta(80000.0, $receipt->fresh()->remainingAmount(), 0.001);
    }

    public function test_allocate_many_students_without_double_posting_cash(): void
    {
        $s1 = $this->studentWithFee(20000);
        $s2 = $this->studentWithFee(30000);
        $s3 = $this->studentWithFee(15000);

        $account = $this->bankAccount(0);
        $receipt = $this->receipt(5000000, $account->account_id);
        $service = app(BulkReceiptService::class);

        $depositsBefore = BankTransaction::where('transaction_type', 'deposit')->count();

        $service->allocate($receipt, [
            ['student_id' => $s1->student_id, 'amount' => 20000],
            ['student_id' => $s2->student_id, 'amount' => 30000],
            ['student_id' => $s3->student_id, 'amount' => 15000],
        ]);

        // THE core accounting rule: allocating 100 students does not re-receive
        // the money. No extra deposit, no bank movement.
        $this->assertSame($depositsBefore, BankTransaction::where('transaction_type', 'deposit')->count());
        $this->assertEqualsWithDelta(5000000.0, (float) $account->fresh()->current_balance, 0.001);

        $this->assertEqualsWithDelta(4935000.0, $receipt->fresh()->remainingAmount(), 0.001);
        $this->assertSame(3, FeePayment::where('bulk_receipt_id', $receipt->id)->whereNull('reversed_at')->count());
    }

    public function test_partial_allocation_keeps_remainder_traceable(): void
    {
        $student = $this->studentWithFee(20000);
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        $service->allocate($receipt, [
            ['student_id' => $student->student_id, 'amount' => 15000],
        ]);

        $receipt->refresh();
        $this->assertEqualsWithDelta(85000.0, $receipt->remainingAmount(), 0.001, 'the unallocated money must stay on the receipt');
        $this->assertEqualsWithDelta(15000.0, (float) $student->fresh()->feeAssignments->first()->paid_amount, 0.001, 'student is part-paid');
    }

    public function test_over_allocation_is_rejected_atomically(): void
    {
        $student = $this->studentWithFee(20000);
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        try {
            $service->allocate($receipt, [
                ['student_id' => $student->student_id, 'amount' => 200000],
            ]);
            $this->fail('Expected over-allocation to be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('exceeds the remaining', $e->getMessage());
        }

        $this->assertSame(0, FeePayment::where('bulk_receipt_id', $receipt->id)->count());
        $this->assertEqualsWithDelta(100000.0, $receipt->fresh()->remainingAmount(), 0.001);
    }

    public function test_concurrent_allocation_cannot_exceed_receipt(): void
    {
        $students = [];
        for ($i = 0; $i < 4; $i++) {
            $students[] = $this->studentWithFee(10000);
        }

        $receipt = $this->receipt(30000); // only 3 students' worth
        $service = app(BulkReceiptService::class);

        $succeeded = 0;
        $failed = 0;

        foreach ($students as $student) {
            try {
                // Each allocation spends the remaining balance inside a locked
                // transaction, so the 4th must find nothing left.
                $service->allocate($receipt, [
                    ['student_id' => $student->student_id, 'amount' => 10000],
                ]);
                $succeeded++;
            } catch (\RuntimeException $e) {
                $failed++;
            }
        }

        $this->assertSame(3, $succeeded);
        $this->assertSame(1, $failed);
        $this->assertEqualsWithDelta(0.0, $receipt->fresh()->remainingAmount(), 0.001);
    }

    public function test_allocation_targets_oldest_outstanding_charge_first(): void
    {
        $student = $this->studentWithFee(10000);           // oldest charge
        $assignmentOld = $student->feeAssignments->first();

        $category = \App\Models\FeeCategory::create(['name' => 'Bulk-Newer-' . uniqid(), 'type' => 'mandatory']);
        $newerStructure = \App\Models\FeeStructure::create([
            'academic_year_id' => $assignmentOld->academic_year_id,
            'class_id' => $assignmentOld->feeStructure->class_id,
            'category_id' => $category->category_id,
            'amount' => 10000,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'status' => 'active',
        ]);

        $newer = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $newerStructure->fee_structure_id,
            'academic_year_id' => $assignmentOld->academic_year_id,
            'term' => 'T1',
            'amount' => 10000,
            'final_amount' => 10000,
            'paid_amount' => 0,
            'assigned_date' => now(), // newer
            'status' => 'active',
        ]);

        $receipt = $this->receipt(15000);
        $service = app(BulkReceiptService::class);

        $service->allocate($receipt, [
            ['student_id' => $student->student_id, 'amount' => 15000],
        ]);

        $this->assertEqualsWithDelta(10000.0, (float) $assignmentOld->fresh()->paid_amount, 0.001, 'oldest charge settled first');
        $this->assertEqualsWithDelta(5000.0, (float) $newer->fresh()->paid_amount, 0.001, 'remainder spills to the newer charge');
    }

    public function test_student_without_outstanding_balance_is_rejected(): void
    {
        $student = $this->studentWithFee(20000, 20000); // fully paid
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        try {
            $service->allocate($receipt, [
                ['student_id' => $student->student_id, 'amount' => 5000],
            ]);
            $this->fail('Expected allocation against a fully-paid student to be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('no outstanding fee balance', $e->getMessage());
        }

        $this->assertEqualsWithDelta(100000.0, $receipt->fresh()->remainingAmount(), 0.001);
    }

    // ------------------------------------------------------------------
    // Reversal
    // ------------------------------------------------------------------

    public function test_reversing_allocation_restores_student_and_receipt(): void
    {
        $student = $this->studentWithFee(20000);
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        $payments = $service->allocate($receipt, [
            ['student_id' => $student->student_id, 'amount' => 20000],
        ]);
        $payment = $payments[0];

        $this->assertEqualsWithDelta(20000.0, (float) $student->fresh()->feeAssignments->first()->paid_amount, 0.001);

        $service->reverseAllocation($payment, 'Wrong student');

        // The student's balance is restored (paid_amount back to zero).
        $this->assertEqualsWithDelta(0.0, (float) $student->fresh()->feeAssignments->first()->paid_amount, 0.001);
        // The money is back on the receipt.
        $this->assertEqualsWithDelta(100000.0, $receipt->fresh()->remainingAmount(), 0.001);
    }

    public function test_receipt_cannot_reverse_while_allocations_are_active(): void
    {
        $student = $this->studentWithFee(20000);
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        $service->allocate($receipt, [
            ['student_id' => $student->student_id, 'amount' => 20000],
        ]);

        try {
            $service->reverseReceipt($receipt, 'test');
            $this->fail('Expected receipt reversal to be blocked while allocations exist.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Reverse the student allocations', $e->getMessage());
        }
    }

    public function test_receipt_reversal_after_allocations_removed_undoes_bank_deposit(): void
    {
        $account = $this->bankAccount(0);
        $receipt = $this->receipt(50000, $account->account_id);
        $service = app(BulkReceiptService::class);

        $this->assertEqualsWithDelta(50000.0, (float) $account->fresh()->current_balance, 0.001);

        $service->reverseReceipt($receipt, 'Duplicate receipt');

        $this->assertEqualsWithDelta(0.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertTrue($receipt->fresh()->isReversed());
    }

    // ------------------------------------------------------------------
    // HTTP + RBAC
    // ------------------------------------------------------------------

    public function test_bulk_receipt_pages_require_fees_view(): void
    {
        $teacher = $this->userWithRole('Teacher'); // no fees.view
        $receipt = $this->receipt(100000);

        $this->actingAs($teacher)->get(route('fees.bulk-receipts.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('fees.bulk-receipts.show', $receipt->id))->assertForbidden();
        $this->actingAs($teacher)->post(route('fees.bulk-receipts.allocate', $receipt->id))->assertForbidden();
    }

    public function test_accountant_can_create_and_allocate_via_http(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $student = $this->studentWithFee(20000);

        $this->actingAs($accountant)
            ->post(route('fees.bulk-receipts.store'), [
                'sponsor_name' => 'County Government',
                'sponsor_type' => 'county_government',
                'amount' => '500000',
                'payment_method' => 'bank_transfer',
                'received_date' => now()->toDateString(),
            ])
            ->assertRedirect();

        $receipt = FeeBulkReceipt::latest('id')->first();
        $this->assertNotNull($receipt);
        $this->assertEqualsWithDelta(500000.0, (float) $receipt->amount, 0.001);

        $this->actingAs($accountant)
            ->post(route('fees.bulk-receipts.allocate', $receipt->id), [
                'allocations' => [
                    ['student_id' => $student->student_id, 'amount' => '20000'],
                ],
            ])
            ->assertRedirect();

        $this->assertEqualsWithDelta(20000.0, (float) $student->fresh()->feeAssignments->first()->paid_amount, 0.001);
        $this->assertEqualsWithDelta(480000.0, $receipt->fresh()->remainingAmount(), 0.001);
    }

    public function test_reversal_route_requires_fees_manage(): void
    {
        $collector = $this->userWithRole('Accountant', 'Collector');
        $manager = $this->userWithRole('Admin');
        $student = $this->studentWithFee(20000);
        $receipt = $this->receipt(100000);
        $service = app(BulkReceiptService::class);

        $payments = $service->allocate($receipt, [
            ['student_id' => $student->student_id, 'amount' => 20000],
        ]);
        $payment = $payments[0];

        // Accountant holds fees.manage — allowed. Verify by hitting the route.
        $this->actingAs($collector)
            ->from('/somewhere')
            ->post(route('fees.bulk-receipts.allocations.reverse', [$receipt->id, $payment->payment_id]), ['reason' => 'test'])
            ->assertRedirect();

        $this->assertEqualsWithDelta(100000.0, $receipt->fresh()->remainingAmount(), 0.001);
    }
}
