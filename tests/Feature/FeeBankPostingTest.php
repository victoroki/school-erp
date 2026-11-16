<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\FeeCategory;
use App\Models\FeePayment;
use App\Models\FeeStructure;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentFeeAssignment;
use App\Models\User;
use App\Models\Role;
use App\Services\FinanceService;
use App\Services\LedgerService;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Fee-to-bank integration: every NEW banked fee payment becomes exactly one
 * bank deposit; reversal undoes it; cash payments never touch the bank;
 * historical payments stay untouched (no silent backfill).
 */
class FeeBankPostingTest extends TestCase
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

    private function collector(): User
    {
        $role = Role::where('role_name', 'Accountant')->firstOrFail();
        $user = User::firstOrCreate(
            ['email' => 'feebank-collector@test.local'],
            ['name' => 'FeeBank Collector', 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->fresh();
    }

    private function bankAccount(float $balance = 100000.0): BankAccount
    {
        return BankAccount::create([
            'account_name' => 'FeeBank Account',
            'account_number' => 'FB-' . random_int(100000, 999999),
            'bank_name' => 'Test Bank',
            'opening_balance' => $balance,
            'current_balance' => $balance,
            'account_type' => 'current',
            'currency' => 'KES',
            'status' => 'active',
        ]);
    }

    private function assignmentWithStudent(float $feeAmount): StudentFeeAssignment
    {
        $year = AcademicYear::create([
            'name' => 'FB-FY-' . uniqid(),
            'start_date' => '2030-01-01', 'end_date' => '2030-12-31',
        ]);
        $class = SchoolClass::create(['name' => 'FB-Grade-' . uniqid(), 'numeric_value' => 2]);
        $category = FeeCategory::create(['name' => 'FB-Tuition-' . uniqid(), 'type' => 'mandatory']);
        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'category_id' => $category->category_id,
            'amount' => $feeAmount,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'status' => 'active',
        ]);

        $student = Student::create([
            'admission_no' => 'FB-' . random_int(100000, 999999),
            'first_name' => 'Fee', 'last_name' => 'Banker' . random_int(1, 999999),
            'date_of_birth' => '2010-01-01', 'gender' => 'male',
            'city' => 'Nairobi', 'country' => 'Kenya', 'admission_date' => now(),
            'is_active' => true, 'status' => 'active',
        ]);

        return StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id,
            'term' => 'T1',
            'amount' => $feeAmount, 'final_amount' => $feeAmount, 'paid_amount' => 0,
            'assigned_date' => now(), 'status' => 'active',
        ]);
    }

    public function test_new_banked_fee_payment_deposits_into_the_chosen_account_once(): void
    {
        $assignment = $this->assignmentWithStudent(20000);
        $account = $this->bankAccount(100000);

        $payment = app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
            'payment_date' => now()->toDateString(),
        ]);

        // Exactly one deposit, into the chosen account.
        $rows = BankTransaction::where('source_type', 'FeePayment')
            ->where('source_id', $payment->payment_id)
            ->where('status', '!=', 'voided')
            ->get();

        $this->assertCount(1, $rows);
        $this->assertSame('deposit', $rows[0]->transaction_type);
        $this->assertEqualsWithDelta(5000.0, (float) $rows[0]->amount, 0.001);
        $this->assertEqualsWithDelta(105000.0, (float) $account->fresh()->current_balance, 0.001);
    }

    /**
     * The collect form offers a "Received Into Account" selector for non-cash
     * methods. This proves the choice survives the controller — i.e. the field
     * is validated and forwarded, not silently dropped.
     */
    public function test_http_payment_honours_the_selected_bank_destination(): void
    {
        $assignment = $this->assignmentWithStudent(20000);
        $studentId = $assignment->student_id;

        // Two active accounts: the payment must land in the SECOND one, not
        // the fallback "first active account".
        $first = $this->bankAccount(100000);
        $chosen = $this->bankAccount(50000);
        $this->assertNotSame($first->account_id, $chosen->account_id);

        $response = $this->actingAs($this->collector())
            ->post(route('fee-management.store-payment', $studentId), [
                'student_fee_assignment_id' => $assignment->id,
                'amount' => 5000,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'bank_transfer',
                'bank_account_id' => $chosen->account_id,
                'skip_print' => 1,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertEqualsWithDelta(55000.0, (float) $chosen->fresh()->current_balance, 0.001);
        $this->assertEqualsWithDelta(100000.0, (float) $first->fresh()->current_balance, 0.001);

        $this->assertDatabaseHas('bank_transactions', [
            'account_id' => $chosen->account_id,
            'transaction_type' => 'deposit',
        ]);
    }

    public function test_cash_fee_payment_never_touches_the_bank(): void
    {
        $assignment = $this->assignmentWithStudent(20000);
        $account = $this->bankAccount(100000);

        app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
        ]);

        $this->assertSame(0, BankTransaction::where('source_type', 'FeePayment')->count());
        $this->assertEqualsWithDelta(100000.0, (float) $account->fresh()->current_balance, 0.001);
    }

    public function test_replaying_the_posting_does_not_duplicate_the_deposit(): void
    {
        $assignment = $this->assignmentWithStudent(20000);
        $account = $this->bankAccount(0);

        $payment = app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_method' => 'online',
            'bank_account_id' => $account->account_id,
            'payment_date' => now()->toDateString(),
        ]);

        // Re-run the private posting path through a fresh service — the
        // findFor guard must refuse a second deposit.
        $service = new \ReflectionMethod(FinanceService::class, 'postFeePaymentToBank');
        $service->setAccessible(true);
        $service->invoke(app(FinanceService::class), $payment);

        $this->assertSame(1, BankTransaction::where('source_type', 'FeePayment')->count());
        $this->assertEqualsWithDelta(5000.0, (float) $account->fresh()->current_balance, 0.001);
    }

    public function test_reversing_the_payment_undoes_the_bank_deposit(): void
    {
        $assignment = $this->assignmentWithStudent(20000);
        $account = $this->bankAccount(100000);

        $payment = app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
            'payment_date' => now()->toDateString(),
        ]);

        app(LedgerService::class)->reversePayment($payment->fresh(), 'Wrong window', $this->collector()->id);

        $this->assertEqualsWithDelta(100000.0, (float) $account->fresh()->current_balance, 0.001);
        // The original deposit row is voided, not deleted.
        $this->assertSame(
            1,
            BankTransaction::where('source_type', 'FeePayment')->where('status', 'voided')->count()
        );
    }

    public function test_payment_without_any_active_account_still_records_the_fee(): void
    {
        $assignment = $this->assignmentWithStudent(20000);
        // No bank accounts at all.

        $payment = app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->toDateString(),
        ]);

        // The fee payment is still valid and settles the student.
        $this->assertNotNull($payment->receipt_number);
        $this->assertEqualsWithDelta(5000.0, (float) $assignment->fresh()->paid_amount, 0.001);
        $this->assertSame(0, BankTransaction::where('source_type', 'FeePayment')->count());
    }

    public function test_historical_payments_are_not_backfilled_by_any_get_request(): void
    {
        // A payment created directly (simulating a historical row with no
        // bank link) must stay unposted — only new payments via the service
        // post to the bank.
        $assignment = $this->assignmentWithStudent(20000);
        $account = $this->bankAccount(100000);

        FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_date' => '2020-01-01',
            'payment_method' => 'bank_transfer',
            'receipt_number' => 'RCP-HIST-' . random_int(100000, 999999),
        ]);
        $assignment->update(['paid_amount' => 5000]);

        // Browsing the reports (GET) must not write bank rows.
        $this->actingAs($this->collector())
            ->get(route('financial-reports.balance-sheet'))
            ->assertOk();

        $this->assertSame(0, BankTransaction::where('source_type', 'FeePayment')->count());
    }

    public function test_reconciliation_command_reports_unposted_history(): void
    {
        $assignment = $this->assignmentWithStudent(20000);

        // One historical payment (no bank row), one new payment (posted).
        FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 5000,
            'payment_date' => '2020-01-01',
            'payment_method' => 'bank_transfer',
            'receipt_number' => 'RCP-HIST2-' . random_int(100000, 999999),
        ]);

        $account = $this->bankAccount(0);
        app(FinanceService::class)->recordPayment([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 7000,
            'payment_method' => 'online',
            'bank_account_id' => $account->account_id,
            'payment_date' => now()->toDateString(),
        ]);

        $this->artisan('fee:bank-reconciliation')
            ->expectsOutputToContain('Not posted:')
            ->assertSuccessful();
    }
}
