<?php

namespace Tests\Feature;

use App\Exceptions\InsufficientFundsException;
use App\Models\BankAccount;
use App\Models\BankReconciliation;
use App\Models\BankTransaction;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\Expenses;
use App\Models\FinancialYear;
use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserRole;
use App\Services\BankLedger;
use App\Services\FinancialMetrics;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Finance auth + integrity regression checks.
 *
 * Covers the audit findings repacked in Phase 1-4: the RBAC 403 matrix on
 * financial surfaces, segregation of duties on approval, the status gates on
 * paying expenses, insufficient-funds rollback, the income edit ledger delta,
 * two-row transfers, cash vs committed reporting conventions, budget FY/incl-
 * fees handling, reconciliation variance recording, and schema money scale.
 *
 * Runs against the isolated school_erp_test database (transaction-per-test).
 * Hermetic: seeds RBAC tables when missing, creates fixture users per role,
 * and keeps every seeded money record inside this session's test window
 * (2030-01-01 .. 2030-12-31) so leftovers from earlier suites cannot skew
 * the amount assertions.
 */
class FinanceModuleIntegrityTest extends TestCase
{
    private const TEST_START = '2030-01-01';
    private const TEST_END   = '2030-12-31';

    private const FIXTURE_EMAILS = [
        'Accountant'  => 'finance-test-accountant-a@test.local',
        'Accountant B' => 'finance-test-accountant-b@test.local',
        'Teacher'     => 'finance-test-teacher@test.local',
    ];

    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase hands us a freshly migrated, empty schema; the RBAC
        // tables are populated by the seeders so the role fixtures resolve.
        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }
    }

    private function userWithRole(string $roleName, ?string $identityKey = null): User
    {
        $identityKey ??= $roleName;
        $role = Role::where('role_name', $roleName)->firstOrFail();

        $user = User::firstOrCreate(
            ['email' => self::FIXTURE_EMAILS[$identityKey]],
            ['name' => "Finance Test {$identityKey}", 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        // The schema's expense/income/approval columns reference staff.staff_id,
        // and the controllers store auth()->id() there. This mirrors the live
        // convention where a finance user's staff row carries staff_id == id.
        if (! DB::table('staff')->where('staff_id', $user->id)->exists()) {
            DB::table('staff')->insert([
                'staff_id'         => $user->id,
                'user_id'          => $user->id,
                'first_name'       => 'Finance',
                'last_name'        => 'Test ' . $identityKey,
                'date_of_birth'    => '1990-01-01',
                'gender'           => 'other',
                'date_of_joining'  => now()->toDateString(),
                'work_email'       => $user->email,
                'phone_primary'    => '0700000000',
                'current_address'  => 'Test',
                'city'             => 'Test',
                'country'          => 'Kenya',
                'staff_type'       => 'administration',
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        return $user->load('roles.permissions');
    }

    // --------------------------------------------------------------------
    // Fixtures
    // --------------------------------------------------------------------

    private function openYear(): FinancialYear
    {
        return FinancialYear::create([
            'name'       => 'Integrity FY 2030',
            'start_date' => self::TEST_START,
            'end_date'   => self::TEST_END,
            'status'     => 'open',
        ]);
    }

    private function closedYear(): FinancialYear
    {
        return FinancialYear::create([
            'name'       => 'Integrity FY 2029',
            'start_date' => '2029-01-01',
            'end_date'   => '2029-12-31',
            'status'     => 'closed',
        ]);
    }

    private function expenseCategory(): ExpenseCategory
    {
        return ExpenseCategory::firstOrCreate(['name' => 'Integrity Expense Cat'], ['status' => 'active']);
    }

    private function incomeCategory(): IncomeCategory
    {
        return IncomeCategory::firstOrCreate(['name' => 'Integrity Income Cat'], ['status' => 'active']);
    }

    private function bankAccount(float $balance = 1000.0, float $minimum = 0.0): BankAccount
    {
        return BankAccount::create([
            'account_name'     => 'Integrity Bank',
            'account_number'   => 'INT-' . random_int(100000, 999999),
            'bank_name'        => 'Integrity Bank Ltd',
            'opening_balance'  => $balance,
            'current_balance'  => $balance,
            'account_type'     => 'current',
            'minimum_balance'  => $minimum,
            'currency'         => 'KES',
            'status'           => 'active',
        ]);
    }

    // --------------------------------------------------------------------
    // RBAC matrix
    // --------------------------------------------------------------------

    public function test_teacher_is_blocked_from_every_finance_surface(): void
    {
        $teacher = $this->userWithRole('Teacher');
        $account = $this->bankAccount();
        $expense = Expenses::create([
            'category_id'    => $this->expenseCategory()->category_id,
            'amount'         => 50.00,
            'expense_date'   => self::TEST_START,
            'payment_method' => 'cash',
            'status'         => 'approved',
            'created_by'     => $teacher->id,
        ]);

        $this->actingAs($teacher)->get(route('expenses.pending'))->assertForbidden();
        $this->actingAs($teacher)->get(route('income.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('budgets.vs-actual'))->assertForbidden();
        $this->actingAs($teacher)->get(route('financial-reports.cashflow'))->assertForbidden();
        $this->actingAs($teacher)->get(route('financial-reports.p-and-l'))->assertForbidden();
        $this->actingAs($teacher)->get(route('suppliers.index'))->assertForbidden();
        $this->actingAs($teacher)->get(route('expenses.edit', $expense->expense_id))->assertForbidden();
        $this->actingAs($teacher)->get(route('income.edit', 1))->assertForbidden();
        $this->actingAs($teacher)->post(route('expenses.pay', $expense->expense_id))->assertForbidden();
        $this->actingAs($teacher)->put(route('bank-reconciliations.update', $account->account_id), [
            'transaction_ids'   => [],
            'statement_balance' => 100,
        ])->assertForbidden();
    }

    public function test_accountant_reaches_finance_surfaces(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $this->openYear();

        $this->actingAs($accountant)->get(route('expenses.pending'))->assertOk();
        $this->actingAs($accountant)->get(route('income.index'))->assertOk();
        $this->actingAs($accountant)->get(route('budgets.vs-actual'))->assertOk();
        $this->actingAs($accountant)->get(route('suppliers.index'))->assertOk();
    }

    // --------------------------------------------------------------------
    // Expense approval / payment gates
    // --------------------------------------------------------------------

    public function test_approve_requires_segregation_and_peer_can_approve(): void
    {
        $creator = $this->userWithRole('Accountant');
        $peer    = $this->userWithRole('Accountant', 'Accountant B');

        $expense = Expenses::create([
            'category_id'    => $this->expenseCategory()->category_id,
            'amount'         => 75.00,
            'expense_date'   => self::TEST_START,
            'payment_method' => 'cash',
            'status'         => 'pending',
            'created_by'     => $creator->id,
        ]);

        $this->actingAs($creator)->post(route('expenses.approve', $expense->expense_id));
        $this->assertSame('pending', $expense->fresh()->status, 'initiator must not approve their own expense');

        $this->actingAs($peer)->from('/somewhere')->post(route('expenses.approve', $expense->expense_id));
        $this->assertSame('approved', $expense->fresh()->status);
        $this->assertSame($peer->id, (int) $expense->fresh()->approved_by);
    }

    public function test_unapproved_expense_cannot_be_paid(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $account    = $this->bankAccount();

        $pending = Expenses::create([
            'category_id'     => $this->expenseCategory()->category_id,
            'bank_account_id' => $account->account_id,
            'amount'          => 40.00,
            'expense_date'    => self::TEST_START,
            'payment_method'  => 'bank_transfer',
            'status'          => 'pending',
            'created_by'      => $accountant->id,
        ]);

        $this->actingAs($accountant)->from('/somewhere')->post(route('expenses.pay', $pending->expense_id));
        $this->assertSame('pending', $pending->fresh()->status);
        $this->assertEqualsWithDelta(1000.0, (float) $account->fresh()->current_balance, 0.001);

        $rejected = Expenses::create([
            'category_id'     => $this->expenseCategory()->category_id,
            'bank_account_id' => $account->account_id,
            'amount'          => 40.00,
            'expense_date'    => self::TEST_START,
            'payment_method'  => 'bank_transfer',
            'status'          => 'rejected',
            'created_by'      => $accountant->id,
        ]);

        $this->actingAs($accountant)->from('/somewhere')->post(route('expenses.pay', $rejected->expense_id));
        $this->assertSame('rejected', $rejected->fresh()->status);
        $this->assertEqualsWithDelta(1000.0, (float) $account->fresh()->current_balance, 0.001);
    }

    public function test_expense_cannot_be_paid_twice(): void
    {
        $accountant = $this->userWithRole('Accountant');

        $expense = Expenses::create([
            'category_id'    => $this->expenseCategory()->category_id,
            'amount'         => 60.00,
            'expense_date'   => self::TEST_START,
            'payment_method' => 'cash',
            'status'         => 'approved',
            'created_by'     => $accountant->id,
        ]);

        $response = $this->actingAs($accountant)->from('/somewhere')->post(route('expenses.pay', $expense->expense_id));
        $this->assertSame('paid', $expense->fresh()->status);
        $response->assertSessionHas('flash_notification');

        $this->actingAs($accountant)->from('/somewhere')->post(route('expenses.pay', $expense->expense_id))
            ->assertSessionHas('flash_notification');
    }

    public function test_insufficient_funds_rolls_back_status_and_balance(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $account    = $this->bankAccount(100.0, 50.0);

        $expense = Expenses::create([
            'category_id'     => $this->expenseCategory()->category_id,
            'bank_account_id' => $account->account_id,
            'amount'          => 200.00,
            'expense_date'    => self::TEST_START,
            'payment_method'  => 'bank_transfer',
            'status'          => 'approved',
            'created_by'      => $accountant->id,
        ]);

        $this->actingAs($accountant)->from('/somewhere')->post(route('expenses.pay', $expense->expense_id));

        $this->assertSame('approved', $expense->fresh()->status, 'a failed payment must not change the status');
        $this->assertEqualsWithDelta(100.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(
            0,
            BankTransaction::where('source_type', 'Expense')->where('source_id', $expense->expense_id)->count(),
            'a failed payment must not write a ledger row'
        );
    }

    public function test_approved_bank_payment_writes_balance_and_linked_ledger_row(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $account    = $this->bankAccount(1000.0);

        $expense = Expenses::create([
            'category_id'     => $this->expenseCategory()->category_id,
            'bank_account_id' => $account->account_id,
            'amount'          => 300.00,
            'expense_date'    => self::TEST_START,
            'payment_method'  => 'bank_transfer',
            'status'          => 'approved',
            'created_by'      => $accountant->id,
        ]);

        $this->actingAs($accountant)->from('/somewhere')->post(route('expenses.pay', $expense->expense_id));

        $this->assertSame('paid', $expense->fresh()->status);
        $this->assertEqualsWithDelta(700.0, (float) $account->fresh()->current_balance, 0.001);

        $row = BankTransaction::where('source_type', 'Expense')->where('source_id', $expense->expense_id)->first();
        $this->assertNotNull($row, 'the bank ledger must carry a source-linked row for the expense');
        $this->assertSame('withdrawal', $row->transaction_type);
        $this->assertEqualsWithDelta(300.0, (float) $row->amount, 0.001);
    }

    // --------------------------------------------------------------------
    // Income edit ledger delta
    // --------------------------------------------------------------------

    public function test_income_update_reconciles_bank_ledger(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $category   = $this->incomeCategory();
        $account    = $this->bankAccount(1000.0);

        $this->actingAs($accountant)->post(route('income.store'), [
            'category_id'    => $category->category_id,
            'amount'         => '500.00',
            'income_date'    => '2030-05-15',
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
            'payer_name'     => 'Integrity Payer',
        ]);

        $income = Income::query()->latest('income_id')->first();
        $this->assertEqualsWithDelta(1500.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(
            1,
            BankTransaction::where('source_type', 'Income')
                ->where('source_id', $income->income_id)
                ->where('status', '!=', 'voided')
                ->count(),
            'store must leave exactly one live deposit row'
        );

        // Raise the amount: the old deposit is voided, a new one reflects the edit.
        $this->actingAs($accountant)->put(route('income.update', $income->income_id), [
            'category_id'    => $category->category_id,
            'amount'         => '600.00',
            'income_date'    => '2030-05-15',
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
            'payer_name'     => 'Integrity Payer',
        ]);

        $this->assertSame('600.00', $income->fresh()->amount);
        $this->assertEqualsWithDelta(1600.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(
            1,
            BankTransaction::where('source_type', 'Income')
                ->where('source_id', $income->income_id)
                ->where('status', '!=', 'voided')
                ->count(),
            'only the newest deposit may stay live after an edit'
        );

        // Switch to cash: the deposit is reversed and the balance restored.
        $this->actingAs($accountant)->put(route('income.update', $income->income_id), [
            'category_id'    => $category->category_id,
            'amount'         => '700.00',
            'income_date'    => '2030-05-15',
            'payment_method' => 'cash',
            'payer_name'     => 'Integrity Payer',
        ]);

        $this->assertNull($income->fresh()->bank_account_id);
        $this->assertEqualsWithDelta(1000.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(
            0,
            BankTransaction::where('source_type', 'Income')
                ->where('source_id', $income->income_id)
                ->where('status', '!=', 'voided')
                ->count()
        );
    }

    public function test_income_update_cannot_rewrite_received_by_or_status(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $teacher    = $this->userWithRole('Teacher');
        $category   = $this->incomeCategory();

        $income = Income::create([
            'category_id'    => $category->category_id,
            'amount'         => 250.00,
            'income_date'    => '2030-06-01',
            'payment_method' => 'cash',
            'payer_name'     => 'Immutable Payer',
            'status'         => 'active',
            'received_by'    => $accountant->id,
        ]);

        $this->actingAs($accountant)->put(route('income.update', $income->income_id), [
            'category_id'    => $category->category_id,
            'amount'         => '260.00',
            'income_date'    => '2030-06-01',
            'payment_method' => 'cash',
            'payer_name'     => 'Immutable Payer',
            'status'         => 'void',
            'received_by'    => $teacher->id,
        ]);

        $fresh = $income->fresh();
        $this->assertSame('active', $fresh->status, 'status is owned by the workflow, not the edit form');
        $this->assertSame($accountant->id, (int) $fresh->received_by, 'received_by is owned by the store action');
    }

    // --------------------------------------------------------------------
    // Transfers
    // --------------------------------------------------------------------

    public function test_transfer_writes_two_rows_and_moves_balances(): void
    {
        $from = $this->bankAccount(1000.0);
        $to   = $this->bankAccount(0.0);

        $toRow = BankLedger::recordTransfer($from, $to, 250.0, '2030-07-01', 'Transfer', null, null);

        $this->assertEqualsWithDelta(750.0, (float) $from->fresh()->current_balance, 0.001);
        $this->assertEqualsWithDelta(250.0, (float) $to->fresh()->current_balance, 0.001);

        $rows = BankTransaction::where('reference_number', $toRow->reference_number)->get();
        $this->assertCount(2, $rows, "a transfer must appear on both accounts' statements");
        $this->assertSame(1, $rows->where('transaction_type', 'withdrawal')->where('account_id', $from->account_id)->count());
        $this->assertSame(1, $rows->where('transaction_type', 'deposit')->where('account_id', $to->account_id)->count());
    }

    public function test_transfer_refuses_below_minimum_and_writes_nothing(): void
    {
        $from = $this->bankAccount(100.0, 50.0);
        $to   = $this->bankAccount(0.0);

        $before = BankTransaction::count();

        try {
            BankLedger::recordTransfer($from, $to, 200.0, '2030-07-02', 'Too much');
            $this->fail('Expected InsufficientFundsException was not thrown.');
        } catch (InsufficientFundsException $e) {
            $this->assertTrue(true);
        }

        $this->assertEqualsWithDelta(100.0, (float) $from->fresh()->current_balance, 0.001);
        $this->assertEqualsWithDelta(0.0, (float) $to->fresh()->current_balance, 0.001);
        $this->assertSame($before, BankTransaction::count(), 'a refused transfer must not leave any ledger rows');
    }

    // --------------------------------------------------------------------
    // Reporting conventions
    // --------------------------------------------------------------------

    public function test_cashflow_counts_only_paid_expenses(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $category   = $this->expenseCategory();

        Expenses::create([
            'category_id'    => $category->category_id,
            'amount'         => 100.00,
            'expense_date'   => '2030-03-01',
            'payment_method' => 'cash',
            'status'         => 'paid',
            'created_by'     => $accountant->id,
        ]);
        Expenses::create([
            'category_id'    => $category->category_id,
            'amount'         => 200.00,
            'expense_date'   => '2030-03-02',
            'payment_method' => 'cash',
            'status'         => 'approved',
            'created_by'     => $accountant->id,
        ]);

        $response = $this->actingAs($accountant)->get(route('financial-reports.cashflow', [
            'start_date' => self::TEST_START,
            'end_date'   => self::TEST_END,
        ]));

        $response->assertOk();
        $response->assertSee('KES 100.00');
        $response->assertDontSee('KES 200.00');
    }

    public function test_p_and_l_uses_committed_basis(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $category   = $this->expenseCategory();
        $this->openYear();

        Expenses::create([
            'category_id'    => $category->category_id,
            'amount'         => 100.00,
            'expense_date'   => '2030-03-01',
            'payment_method' => 'cash',
            'status'         => 'paid',
            'created_by'     => $accountant->id,
        ]);
        Expenses::create([
            'category_id'    => $category->category_id,
            'amount'         => 200.00,
            'expense_date'   => '2030-03-02',
            'payment_method' => 'cash',
            'status'         => 'approved',
            'created_by'     => $accountant->id,
        ]);

        $response = $this->actingAs($accountant)->get(route('financial-reports.p-and-l'));

        $response->assertOk();
        $response->assertSee('KES 300.00');
    }

    public function test_financial_metrics_conventions(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $expCat     = $this->expenseCategory();
        $incCat     = $this->incomeCategory();

        $paid = Expenses::create([
            'category_id'    => $expCat->category_id,
            'amount'         => 100.00,
            'expense_date'   => '2030-03-01',
            'payment_method' => 'cash',
            'status'         => 'paid',
            'created_by'     => $accountant->id,
        ]);
        $approved = Expenses::create([
            'category_id'    => $expCat->category_id,
            'amount'         => 200.00,
            'expense_date'   => '2030-03-02',
            'payment_method' => 'cash',
            'status'         => 'approved',
            'created_by'     => $accountant->id,
        ]);

        $this->assertEqualsWithDelta(
            100.0,
            (float) FinancialMetrics::expensesBetween(self::TEST_START, self::TEST_END, FinancialMetrics::BASIS_CASH)->sum('amount'),
            0.001
        );
        $this->assertEqualsWithDelta(
            300.0,
            (float) FinancialMetrics::expensesBetween(self::TEST_START, self::TEST_END, FinancialMetrics::BASIS_COMMITTED)->sum('amount'),
            0.001
        );
        $this->assertEqualsWithDelta(
            100.0,
            (float) FinancialMetrics::categorySpendTotal($expCat->category_id, self::TEST_START, self::TEST_END, FinancialMetrics::BASIS_CASH),
            0.001
        );

        Income::create([
            'category_id'    => $incCat->category_id,
            'amount'         => 500.00,
            'income_date'    => '2030-06-01',
            'payment_method' => 'cash',
            'payer_name'     => 'Conventions Payer',
            'status'         => 'active',
        ]);

        $this->assertEqualsWithDelta(
            500.0,
            (float) FinancialMetrics::incomeForCategory($incCat->category_id, self::TEST_START, self::TEST_END, false),
            0.001
        );
        // include_fees=true adds the (empty here) fee-payment stream without changing income.
        $this->assertEqualsWithDelta(
            500.0,
            (float) FinancialMetrics::incomeForCategory($incCat->category_id, self::TEST_START, self::TEST_END, true),
            0.001
        );
        $this->assertEqualsWithDelta(
            0.0,
            (float) FinancialMetrics::feeIncomeBetween(self::TEST_START, self::TEST_END)->sum('amount'),
            0.001
        );
    }

    // --------------------------------------------------------------------
    // Budgets
    // --------------------------------------------------------------------

    public function test_budget_round_trip_persists_include_fees_flag(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $year       = $this->openYear();
        $category   = $this->incomeCategory();

        $this->actingAs($accountant)->post(route('budgets.store'), [
            'financial_year_id' => $year->id,
            'category_type'     => 'income',
            'category_id'       => $category->category_id,
            'amount'            => '1000.00',
            'include_fees'      => '1',
        ]);

        $budget = Budget::query()->latest('id')->first();
        $this->assertTrue((bool) $budget->include_fees, 'include_fees=1 must persist');

        $this->actingAs($accountant)->put(route('budgets.update', $budget->id), [
            'financial_year_id' => $year->id,
            'category_type'     => 'income',
            'category_id'       => $category->category_id,
            'amount'            => '1200.00',
        ]);

        $this->assertFalse((bool) $budget->fresh()->include_fees, 'an unchecked include_fees must normalise to false');
    }

    public function test_budget_edit_labels_closed_year_and_preserves_stored_year(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $closed     = $this->closedYear();
        $this->openYear();
        $category = $this->expenseCategory();

        $budget = Budget::create([
            'financial_year_id' => $closed->id,
            'category_type'     => 'expense',
            'category_id'       => $category->category_id,
            'amount'            => '500.00',
            'include_fees'      => false,
            'created_by'        => $accountant->id,
        ]);

        $response = $this->actingAs($accountant)->get(route('budgets.edit', $budget->id));
        $response->assertOk();
        $response->assertSee('(closed)');
        $response->assertSee($closed->name);
    }

    public function test_budget_unique_per_financial_year_and_category(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $year       = $this->openYear();
        $category   = $this->incomeCategory();

        Budget::create([
            'financial_year_id' => $year->id,
            'category_type'     => 'income',
            'category_id'       => $category->category_id,
            'amount'            => '500.00',
            'include_fees'      => false,
            'created_by'        => $accountant->id,
        ]);

        try {
            Budget::create([
                'financial_year_id' => $year->id,
                'category_type'     => 'income',
                'category_id'       => $category->category_id,
                'amount'            => '900.00',
                'include_fees'      => false,
                'created_by'        => $accountant->id,
            ]);
            $this->fail('Expected a unique-constraint violation for a duplicate (year, type, category) budget row.');
        } catch (QueryException $e) {
            $this->assertTrue(true);
        }

        // A different category in the same year/type must be allowed.
        $other = IncomeCategory::create(['name' => 'Integrity Other Income', 'status' => 'active']);
        $ok = Budget::create([
            'financial_year_id' => $year->id,
            'category_type'     => 'income',
            'category_id'       => $other->category_id,
            'amount'            => '300.00',
            'include_fees'      => false,
            'created_by'        => $accountant->id,
        ]);
        $this->assertSame($year->id, $ok->financial_year_id);
    }

    // --------------------------------------------------------------------
    // Reconciliation
    // --------------------------------------------------------------------

    public function test_reconciliation_requires_statement_and_records_variance(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $account    = $this->bankAccount(1000.0);

        $tx = BankLedger::recordDeposit($account, 100.0, '2030-01-05', 'Integrity deposit', null, null);

        // Missing statement_balance must be rejected outright.
        $this->actingAs($accountant)
            ->from('/somewhere')
            ->put(route('bank-reconciliations.update', $account->account_id), [
                'transaction_ids' => [$tx->transaction_id],
            ])
            ->assertSessionHasErrors('statement_balance');

        // With a statement balance below the book, the variance must be on the record.
        $this->actingAs($accountant)->put(route('bank-reconciliations.update', $account->account_id), [
            'transaction_ids'   => [$tx->transaction_id],
            'statement_balance' => '1050.00',
        ])->assertRedirect(route('bank-reconciliations.index'));

        $record = BankReconciliation::query()->latest('id')->first();
        $this->assertSame($account->account_id, (int) $record->bank_account_id);
        $this->assertSame('completed', $record->status);
        $this->assertEqualsWithDelta(50.0, (float) $record->system_balance - (float) $record->statement_balance, 0.001);

        $this->assertSame('reconciled', $tx->fresh()->status);
    }

    // --------------------------------------------------------------------
    // Chinese-wall / hardening
    // --------------------------------------------------------------------

    public function test_financial_year_update_whitelists_columns(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $year       = $this->openYear();

        $this->actingAs($accountant)->put(route('financial-years.update', $year->id), [
            'name'       => 'Integrity FY 2031',
            'start_date' => '2031-01-01',
            'end_date'   => '2031-12-31',
            'status'     => 'open',
            'created_at' => '2001-01-01',
        ]);

        $fresh = $year->fresh();
        $this->assertSame('Integrity FY 2031', $fresh->name);
        $this->assertNotSame('2001-01-01', $fresh->created_at->toDateString(), 'created_at must not be writable via update');
    }

    public function test_money_columns_have_two_decimal_scale(): void
    {
        $columns = [
            'expenses'          => ['amount'],
            'income'            => ['amount'],
            'bank_accounts'     => ['opening_balance', 'current_balance'],
            'bank_transactions' => ['amount'],
        ];

        foreach ($columns as $table => $cols) {
            foreach ($cols as $column) {
                $meta = DB::selectOne(
                    'SELECT NUMERIC_SCALE FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                    [$table, $column]
                );
                $this->assertNotNull($meta, "{$table}.{$column} must exist");
                $this->assertGreaterThanOrEqual(
                    2,
                    (int) $meta->NUMERIC_SCALE,
                    "{$table}.{$column} must keep at least 2 decimal places"
                );
            }
        }
    }
}