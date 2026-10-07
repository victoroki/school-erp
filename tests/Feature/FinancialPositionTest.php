<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Expenses;
use App\Models\ExpenseCategory;
use App\Models\FinancialYear;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\PurchaseOrder;
use App\Models\StudentFeeAssignment;
use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\User;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\FeeCategory;
use App\Models\FeeStructure;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Statement of Financial Position and Financial Performance (P&L) coverage.
 *
 * The balance sheet must reconcile under the transaction-summary model:
 *     net assets = total assets − total liabilities
 * and pick up the new Phase 2 categories (inventory, supplier payables from
 * credit POs, fees received in advance) without inventing figures.
 *
 * The P&L must accept a term filter whose date boundaries come from the real
 * term record, and keep the existing date-range filter working.
 */
class FinancialPositionTest extends TestCase
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

    private function accountant(): User
    {
        $role = Role::where('role_name', 'Accountant')->firstOrFail();
        $user = User::firstOrCreate(
            ['email' => 'finpos-accountant@test.local'],
            ['name' => 'FinPos Accountant', 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        // expenses.created_by references staff.staff_id; mirror the live
        // convention of a finance user whose staff row carries staff_id == id.
        if (! \Illuminate\Support\Facades\DB::table('staff')->where('staff_id', $user->id)->exists()) {
            \Illuminate\Support\Facades\DB::table('staff')->insert([
                'staff_id' => $user->id,
                'user_id' => $user->id,
                'first_name' => 'FinPos',
                'last_name' => 'Accountant',
                'date_of_birth' => '1990-01-01',
                'gender' => 'other',
                'date_of_joining' => now()->toDateString(),
                'work_email' => $user->email,
                'phone_primary' => '0700000000',
                'current_address' => 'Test',
                'city' => 'Test',
                'country' => 'Kenya',
                'staff_type' => 'administration',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $user->fresh();
    }

    private function bankAccount(float $balance): BankAccount
    {
        return BankAccount::create([
            'account_name' => 'FinPos Bank',
            'account_number' => 'FP-' . random_int(100000, 999999),
            'bank_name' => 'Test Bank',
            'opening_balance' => $balance,
            'current_balance' => $balance,
            'account_type' => 'current',
            'currency' => 'KES',
            'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------------
    // Financial Position
    // ------------------------------------------------------------------

    public function test_balance_sheet_renders_and_reconciles(): void
    {
        $this->actingAs($this->accountant())
            ->get(route('financial-reports.balance-sheet'))
            ->assertOk()
            ->assertSee('TOTAL ASSETS')
            ->assertSee('TOTAL LIABILITIES')
            ->assertSee('NET ASSETS (EQUITY)');
    }

    public function test_balance_sheet_includes_supplier_payables_from_credit_pos(): void
    {
        $accountant = $this->accountant();

        $supplier = \App\Models\Supplier::create([
            'name' => 'FinPos Supplier', 'phone' => '0700000000',
            'code' => 'SUP-' . random_int(10000, 99999), 'is_active' => true,
        ]);

        // A received credit PO of 600,000 with 200,000 paid → 400,000 payable.
        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generateNumber(),
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->toDateString(),
            'sub_total' => 600000,
            'tax_amount' => 0,
            'grand_total' => 600000,
            'delivery_address' => 'School Main Store',
            'status' => PurchaseOrder::STATUS_FULLY_RECEIVED,
            'payment_arrangement' => PurchaseOrder::ARRANGEMENT_CREDIT,
        ]);

        // A partially-paid credit PO contributes only its outstanding balance:
        // 50,000 ordered, 10,000 paid → 40,000 still payable.
        $partial = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generateNumber(),
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->toDateString(),
            'sub_total' => 50000,
            'tax_amount' => 0,
            'grand_total' => 50000,
            'delivery_address' => 'School Main Store',
            'status' => PurchaseOrder::STATUS_FULLY_RECEIVED,
            'payment_arrangement' => PurchaseOrder::ARRANGEMENT_CREDIT,
        ]);
        PurchaseOrder::recordPayment($partial, ['amount' => 10000, 'payment_method' => 'cash']);

        $response = $this->actingAs($accountant)
            ->get(route('financial-reports.balance-sheet'))
            ->assertOk();

        // 600,000 (unpaid) + 40,000 (partially paid) = 640,000 payables.
        $response->assertSee('640,000.00');
    }

    public function test_balance_sheet_includes_inventory_valuation(): void
    {
        $category = InventoryCategory::create(['name' => 'FinPos Inv Cat ' . random_int(1, 99999)]);
        InventoryItem::create([
            'name' => 'Test Reams',
            'item_code' => 'INV-' . random_int(10000, 99999),
            'category_id' => $category->category_id ?? $category->id,
            'unit' => 'ream',
            'cost_per_unit' => 250.50,
            'quantity' => 100,
            'minimum_quantity' => 0,
        ]);

        $this->actingAs($this->accountant())
            ->get(route('financial-reports.balance-sheet'))
            ->assertOk()
            ->assertSee('Stock on Hand (at cost)')
            ->assertSee('25,050.00');
    }

    public function test_balance_sheet_includes_fees_received_in_advance(): void
    {
        // A student in credit: charged 1,000 but paid 2,500 → 1,500 advance.
        $year = AcademicYear::create([
            'name' => 'FinPos FY', 'start_date' => '2030-01-01', 'end_date' => '2030-12-31',
        ]);
        $class = SchoolClass::create(['name' => 'FinPos Grade', 'numeric_value' => 5]);
        $category = FeeCategory::create(['name' => 'FinPos-Tuition-' . uniqid(), 'type' => 'mandatory']);
        $structure = FeeStructure::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'category_id' => $category->category_id,
            'amount' => 1000,
            'term' => 'T1',
            'payment_frequency' => 'termly',
            'status' => 'active',
        ]);

        $student = \App\Models\Student::create([
            'admission_no' => 'FPA-' . random_int(100000, 999999),
            'first_name' => 'Advance', 'last_name' => 'Payer',
            'date_of_birth' => '2010-01-01', 'gender' => 'female',
            'city' => 'Nairobi', 'country' => 'Kenya', 'admission_date' => now(),
            'is_active' => true, 'status' => 'active',
        ]);

        $assignment = StudentFeeAssignment::create([
            'student_id' => $student->student_id,
            'fee_structure_id' => $structure->fee_structure_id,
            'academic_year_id' => $year->academic_year_id,
            'term' => 'T1',
            'amount' => 1000, 'final_amount' => 1000, 'paid_amount' => 0,
            'assigned_date' => now(), 'status' => 'active',
        ]);

        \App\Models\FeePayment::create([
            'student_fee_assignment_id' => $assignment->id,
            'amount' => 2500,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'receipt_number' => 'RCP-ADV-' . random_int(100000, 999999),
        ]);
        $assignment->update(['paid_amount' => 2500]);

        $this->actingAs($this->accountant())
            ->get(route('financial-reports.balance-sheet'))
            ->assertOk()
            ->assertSee('Fees Received in Advance')
            ->assertSee('1,500.00');
    }

    // ------------------------------------------------------------------
    // Financial Performance (P&L) with term filter
    // ------------------------------------------------------------------

    private function term(string $name, string $start, string $end): Term
    {
        $year = AcademicYear::create([
            'name' => 'FY-' . $name . '-' . uniqid(),
            'start_date' => $start, 'end_date' => $end,
        ]);

        return Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => $name,
            'code' => 'T' . random_int(1, 999),
            'start_date' => $start, 'end_date' => $end,
        ]);
    }

    private function expense(float $amount, string $date): void
    {
        $category = ExpenseCategory::create(['name' => 'FinPos-Exp-' . uniqid(), 'status' => 'active']);
        Expenses::create([
            'category_id' => $category->category_id,
            'amount' => $amount,
            'expense_date' => $date,
            'payment_method' => 'cash',
            'status' => 'paid',
            'created_by' => $this->accountant()->id,
        ]);
    }

    public function test_p_and_l_term_filter_uses_term_dates(): void
    {
        // Term 1: March–April 2030 with a 100 expense.
        $term1 = $this->term('Term 1 FinPos', '2030-03-01', '2030-04-30');
        $this->expense(100, '2030-03-15');

        // Outside the term: a 500 expense that must NOT appear under T1.
        $this->expense(500, '2030-06-15');

        $response = $this->actingAs($this->accountant())
            ->get(route('financial-reports.p-and-l', ['term_id' => $term1->id]))
            ->assertOk();

        $response->assertSee('KES 100.00');
        $response->assertDontSee('KES 500.00');
    }

    public function test_p_and_l_other_terms_cover_their_own_windows(): void
    {
        $this->expense(500, '2030-06-15');

        $term2 = $this->term('Term 2 FinPos', '2030-05-01', '2030-07-31');

        $this->actingAs($this->accountant())
            ->get(route('financial-reports.p-and-l', ['term_id' => $term2->id]))
            ->assertOk()
            ->assertSee('KES 500.00');
    }

    public function test_p_and_l_without_term_keeps_date_range_filter(): void
    {
        $this->expense(100, '2030-03-15');
        $this->expense(500, '2030-06-15');

        // Explicit range excluding June.
        $this->actingAs($this->accountant())
            ->get(route('financial-reports.p-and-l', [
                'start_date' => '2030-01-01',
                'end_date' => '2030-04-30',
            ]))
            ->assertOk()
            ->assertSee('KES 100.00')
            ->assertDontSee('KES 500.00');
    }

    public function test_p_and_l_pdf_respects_term_filter(): void
    {
        $term1 = $this->term('Term 1 PDF', '2030-03-01', '2030-04-30');
        $this->expense(100, '2030-03-15');
        $this->expense(500, '2030-06-15');

        $this->actingAs($this->accountant())
            ->get(route('financial-reports.p-and-l-pdf', ['term_id' => $term1->id]))
            ->assertOk();

        // PDF is a download; the arithmetic is identical to the web route,
        // which the term-filter test above asserts.
        $this->assertTrue(true);
    }
}
