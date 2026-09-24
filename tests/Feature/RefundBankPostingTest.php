<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\LedgerEntry;
use App\Models\Refund;
use App\Models\Role;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Completing a refund must move real money: the payout decrements the
 * account it was paid from and leaves a matching withdrawal on
 * bank_transactions, in the same commit as the student-ledger credit.
 *
 * Regression: complete() only posted the student-ledger entry, so a refund
 * paid by "Bank Transfer" or "Cash" never appeared on the bank statement and
 * the account balance still showed the money as available.
 */
class RefundBankPostingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);
    }

    private function accountant(): User
    {
        $user = User::factory()->create(['name' => 'Refund Accountant']);
        $user->roles()->sync(Role::where('role_name', 'Accountant')->pluck('role_id'));

        return $user->load('roles.permissions');
    }

    private function student(): Student
    {
        return Student::create([
            'admission_no' => 'ADM-' . uniqid(),
            'first_name' => 'Refund',
            'last_name' => 'Test',
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'is_active' => true,
            'status' => 'active',
        ]);
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
     * Drive a refund through requested -> approved -> completed.
     */
    private function completeRefund(User $user, Student $student, float $amount, BankAccount $account): Refund
    {
        $refund = Refund::create([
            'student_id' => $student->student_id,
            'amount' => $amount,
            'reason' => 'Overpayment on closed term',
            'status' => 'requested',
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        $this->actingAs($user)
            ->post(route('fees.refunds.approve', $refund->id), ['approval_notes' => 'ok'])
            ->assertRedirect();

        $this->actingAs($user)
            ->post(route('fees.refunds.complete', $refund->id), [
                'refund_method' => 'bank',
                'bank_account_id' => $account->account_id,
                'refund_reference' => 'TRX-001',
            ])
            ->assertRedirect();

        return $refund->fresh();
    }

    public function test_completing_a_refund_draws_the_payout_from_the_selected_account(): void
    {
        $user = $this->accountant();
        $student = $this->student();
        $account = $this->account(50000);

        $refund = $this->completeRefund($user, $student, 5000, $account);

        // Refund is completed and linked to the paying account.
        $this->assertSame('completed', $refund->status);
        $this->assertSame($account->account_id, (int) $refund->bank_account_id);

        // The account balance dropped by the refund amount.
        $this->assertDatabaseHas('bank_accounts', [
            'account_id' => $account->account_id,
            'current_balance' => 45000.00,
        ]);

        // The withdrawal is on the bank statement with the canonical
        // description BankLedger::findFor() can reverse later.
        $transaction = BankTransaction::where('account_id', $account->account_id)
            ->where('transaction_type', 'withdrawal')
            ->where('amount', 5000.00)
            ->first();

        $this->assertNotNull($transaction, 'Refund withdrawal missing from bank_transactions');
        $this->assertStringStartsWith('Refund #' . $refund->id, $transaction->description);
        $this->assertSame('TRX-001', $transaction->reference_number);
        $this->assertSame('unreconciled', $transaction->status);

        // The student ledger still receives the credit.
        $this->assertNotNull($refund->ledger_entry_id);
        $this->assertDatabaseHas('ledger_entries', [
            'id' => $refund->ledger_entry_id,
            'entry_type' => 'refund',
            'credit' => 5000.00,
            'debit' => 0.00,
        ]);
    }

    public function test_completion_is_rejected_when_the_account_covers_the_payout(): void
    {
        $user = $this->accountant();
        $student = $this->student();
        $account = $this->account(1000);

        $refund = Refund::create([
            'student_id' => $student->student_id,
            'amount' => 5000,
            'reason' => 'Exceeds available funds',
            'status' => 'requested',
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        $this->actingAs($user)->post(route('fees.refunds.approve', $refund->id))->assertRedirect();

        $this->actingAs($user)
            ->post(route('fees.refunds.complete', $refund->id), [
                'refund_method' => 'bank',
                'bank_account_id' => $account->account_id,
            ])
            ->assertRedirect()
            ->assertSessionHas('flash_notification');

        // Flash messages accumulate, so assert a danger flash exists anywhere
        // in the stack rather than at a fixed index.
        $levels = collect(session('flash_notification'))->pluck('level')->all();
        $this->assertContains('danger', $levels);

        // Nothing moved: refund still approved, balance intact, no transaction.
        $this->assertSame('approved', $refund->fresh()->status);
        $this->assertSame(1000.00, (float) $account->fresh()->current_balance);
        $this->assertSame(0, BankTransaction::where('account_id', $account->account_id)->count());
    }

    public function test_completion_requires_a_paying_account(): void
    {
        $user = $this->accountant();
        $student = $this->student();

        $refund = Refund::create([
            'student_id' => $student->student_id,
            'amount' => 1000,
            'reason' => 'No account selected',
            'status' => 'requested',
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        $this->actingAs($user)->post(route('fees.refunds.approve', $refund->id))->assertRedirect();

        $this->actingAs($user)
            ->post(route('fees.refunds.complete', $refund->id), ['refund_method' => 'cash'])
            ->assertSessionHasErrors('bank_account_id');

        $this->assertSame('approved', $refund->fresh()->status);
    }

    public function test_finance_metrics_include_completed_refunds(): void
    {
        $user = $this->accountant();
        $student = $this->student();
        $account = $this->account(50000);

        // Only the completed refund counts — requested money has not left yet.
        Refund::create([
            'student_id' => $student->student_id,
            'amount' => 700,
            'reason' => 'Still pending',
            'status' => 'requested',
            'requested_by' => $user->id,
            'requested_at' => now(),
        ]);

        $this->completeRefund($user, $student, 5000, $account);

        $metrics = app(\App\Services\FinanceService::class)->getMetrics();

        $this->assertSame(5000.0, $metrics['total_refunded']);
    }
}
