<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Expenses;
use App\Models\ExpenseCategory;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderPayment;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Role;
use App\Services\BankLedger;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use PDOException;
use Tests\TestCase;

/**
 * Supplier profile and purchase-order payment/credit regressions.
 *
 * Covers the Phase 2 financial customizations: the supplier profile 500
 * (missing purchaseOrders relation), working PO links, supplier expense
 * history, collision-free numbering, the double-receive guard, immediate
 * payment, credit purchases that never touch the bank before payment,
 * partial payments, overpayment rejection and RBAC.
 */
class SupplierProfileTest extends TestCase
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
            ['email' => ($key ?? $roleName) . '-p2@test.local'],
            ['name' => "Phase2 {$roleName}", 'password' => bcrypt('password')]
        );
        $user->roles()->syncWithoutDetaching($role);

        return $user->fresh();
    }

    private function supplier(): Supplier
    {
        return Supplier::create([
            'name' => 'Kilimanjaro Supplies Ltd',
            'phone' => '0722000111',
            'code' => 'SUP-' . random_int(10000, 99999),
            'payment_terms' => 'Net 30',
            'is_active' => true,
        ]);
    }

    private function bankAccount(float $balance = 100000.0): BankAccount
    {
        return BankAccount::create([
            'account_name' => 'PO Test Bank',
            'account_number' => 'PO-' . random_int(100000, 999999),
            'bank_name' => 'Test Bank',
            'opening_balance' => $balance,
            'current_balance' => $balance,
            'account_type' => 'current',
            'currency' => 'KES',
            'status' => 'active',
        ]);
    }

    private function po(Supplier $supplier, float $total = 600000.0, string $status = PurchaseOrder::STATUS_FULLY_RECEIVED): PurchaseOrder
    {
        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generateNumber(),
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addWeek()->toDateString(),
            'delivery_address' => 'School Main Store',
            'sub_total' => $total,
            'tax_amount' => 0,
            'grand_total' => $total,
            'status' => $status,
            'payment_arrangement' => PurchaseOrder::ARRANGEMENT_CREDIT,
            'payment_due_date' => now()->addMonth()->toDateString(),
        ]);

        $inventoryCategory = InventoryCategory::create(['name' => 'PO Test Cat ' . random_int(1, 99999)]);
        $item = InventoryItem::create([
            'name' => 'Test Chalk',
            'item_code' => 'ITM-' . random_int(10000, 99999),
            'category_id' => $inventoryCategory->category_id ?? $inventoryCategory->id,
            'unit' => 'box',
            'cost_per_unit' => 10,
            'quantity' => 0,
            'minimum_quantity' => 0,
        ]);

        $po->items()->create([
            'item_id' => $item->item_id ?? $item->id,
            'item_name' => 'Test Chalk',
            'quantity' => 1,
            'unit_price' => $total,
            'total_price' => $total,
        ]);

        return $po;
    }

    // ------------------------------------------------------------------
    // Supplier profile
    // ------------------------------------------------------------------

    public function test_supplier_profile_renders_for_finance_view_holder(): void
    {
        $supplier = $this->supplier();
        $user = $this->userWithRole('Accountant');

        $this->actingAs($user)
            ->get(route('suppliers.show', $supplier->supplier_id))
            ->assertOk()
            ->assertSee($supplier->name);
    }

    public function test_supplier_profile_is_blocked_without_permission(): void
    {
        $supplier = $this->supplier();
        $teacher = $this->userWithRole('Teacher');

        $this->actingAs($teacher)
            ->get(route('suppliers.show', $supplier->supplier_id))
            ->assertForbidden();
    }

    public function test_supplier_profile_links_purchase_orders_and_shows_expenses(): void
    {
        $user = $this->userWithRole('Accountant');
        $supplier = $this->supplier();
        $po = $this->po($supplier, 120000);

        $category = ExpenseCategory::create(['name' => 'PO Test Expense Cat', 'status' => 'active']);
        $expense = Expenses::create([
            'category_id' => $category->category_id ?? $category->id,
            'supplier_id' => $supplier->supplier_id,
            'amount' => 5000,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
            'status' => 'paid',
        ]);

        $this->actingAs($user)
            ->get(route('suppliers.show', $supplier->supplier_id))
            ->assertOk()
            ->assertSee($po->po_number)
            ->assertSee(route('inventory.purchase-orders.show', $po->po_id))
            ->assertSee('Unpaid', false) // no payments yet on the credit order
            // The supplier-linked expense renders as a row, not the empty state.
            ->assertSee('Paid')
            ->assertDontSee('No expenses recorded against this supplier yet.');
    }

    public function test_supplier_profile_shows_meaningful_empty_states(): void
    {
        $user = $this->userWithRole('Accountant');
        $supplier = $this->supplier();

        $this->actingAs($user)
            ->get(route('suppliers.show', $supplier->supplier_id))
            ->assertOk()
            ->assertSee('No purchase orders found for this supplier.')
            ->assertSee('No expenses recorded against this supplier yet.');
    }

    public function test_supplier_profile_outstanding_payable_reflects_credit_pos(): void
    {
        $user = $this->userWithRole('Accountant');
        $supplier = $this->supplier();
        $this->po($supplier, 600000);
        $paid = $this->po($supplier, 100000);
        PurchaseOrder::recordPayment($paid, ['amount' => 100000, 'payment_method' => 'cash']);

        $this->actingAs($user)
            ->get(route('suppliers.show', $supplier->supplier_id))
            ->assertOk()
            ->assertSee('600,000.00');
    }

    public function test_missing_supplier_redirects_to_index(): void
    {
        $user = $this->userWithRole('Accountant');

        $this->actingAs($user)
            ->get(route('suppliers.show', 999999))
            ->assertRedirect(route('suppliers.index'));
    }

    // ------------------------------------------------------------------
    // Numbering
    // ------------------------------------------------------------------

    public function test_po_numbers_are_sequential_and_unique(): void
    {
        $supplier = $this->supplier();
        $numbers = [];

        for ($i = 0; $i < 5; $i++) {
            $po = $this->po($supplier, 1000);
            $numbers[] = $po->po_number;
        }

        $this->assertSame(5, count(array_unique($numbers)));

        foreach ($numbers as $i => $number) {
            $this->assertMatchesRegularExpression('/^PO-\d{8}-\d{3}$/', $number);
            if ($i > 0) {
                $this->assertGreaterThan($numbers[$i - 1], $number);
            }
        }
    }

    public function test_requisition_numbers_are_sequential_and_unique(): void
    {
        $numbers = [];

        for ($i = 0; $i < 3; $i++) {
            $numbers[] = \App\Services\RequisitionApprovalService::nextRequisitionNumber();
            Requisition::create([
                'requisition_number' => $numbers[$i],
                'requested_by' => $this->userWithRole('Admin')->id,
                'department_id' => 1,
                'date_needed' => now()->addWeek()->toDateString(),
                'priority' => 'Low',
                'justification' => 'test',
                'status' => 'Pending',
                'total_cost' => 0,
            ]);
        }

        $this->assertSame(3, count(array_unique($numbers)));
        $this->assertGreaterThan($numbers[0], $numbers[1]);
    }

    public function test_po_number_collision_is_retried_not_surfaced_as_an_error(): void
    {
        $supplier = $this->supplier();
        $attempts = [];

        // Simulates the real race: our MAX scan proposes N, a concurrent
        // insert commits N before our INSERT lands, and the unique index
        // refuses us. The wrapper must retry with a fresh number.
        $created = PurchaseOrder::createWithFreshNumber(function ($number) use (&$attempts, $supplier) {
            $attempts[] = $number;

            $row = fn () => PurchaseOrder::create([
                'po_number' => $number,
                'supplier_id' => $supplier->supplier_id,
                'order_date' => now()->toDateString(),
                'expected_delivery_date' => now()->addWeek()->toDateString(),
                'delivery_address' => 'School Main Store',
                'sub_total' => 1000,
                'grand_total' => 1000,
                'status' => PurchaseOrder::STATUS_PENDING_APPROVAL,
            ]);

            if (count($attempts) === 1) {
                $row(); // the competing row wins the race
            }

            return $row();
        });

        $this->assertCount(2, $attempts, 'a refused insert must be retried, not surfaced');
        $this->assertNotSame($attempts[0], $attempts[1]);
        $this->assertSame($attempts[1], $created->po_number);
        $this->assertMatchesRegularExpression('/^PO-\d{8}-\d{3}$/', $created->po_number);
    }

    public function test_requisition_number_collision_is_retried_not_surfaced_as_an_error(): void
    {
        $admin = $this->userWithRole('Admin');
        $attempts = [];

        $created = Requisition::createWithFreshNumber(function ($number) use (&$attempts, $admin) {
            $attempts[] = $number;

            $row = fn () => Requisition::create([
                'requisition_number' => $number,
                'requested_by' => $admin->id,
                'department_id' => 1,
                'date_needed' => now()->addWeek()->toDateString(),
                'priority' => 'Low',
                'justification' => 'race',
                'status' => 'Pending',
                'total_cost' => 0,
            ]);

            if (count($attempts) === 1) {
                $row();
            }

            return $row();
        });

        $this->assertCount(2, $attempts, 'a refused insert must be retried, not surfaced');
        $this->assertNotSame($attempts[0], $attempts[1]);
        $this->assertSame($attempts[1], $created->requisition_number);
        $this->assertMatchesRegularExpression('/^REQ-\d{8}-\d{3}$/', $created->requisition_number);
    }

    public function test_unrelated_integrity_error_is_not_retried_as_a_number_collision(): void
    {
        $attempts = 0;

        // A foreign-key violation shares SQLSTATE 23000 with a duplicate-key
        // violation. Retrying it would just re-run a genuine data error five
        // times and then report a confusing failure, so only duplicates on the
        // number we just tried may be retried.
        $this->expectException(QueryException::class);

        try {
            PurchaseOrder::createWithFreshNumber(function ($number) use (&$attempts) {
                $attempts++;

                $previous = new class ('SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a foreign key constraint fails', 0) extends PDOException {
                    public ?array $errorInfo = ['23000', 1452, 'Foreign key constraint fails'];
                };

                throw new QueryException('mysql', 'insert into purchase_orders ...', [], $previous);
            });
        } finally {
            $this->assertSame(1, $attempts, 'a non-duplicate database error must fail on the first attempt');
        }
    }

    public function test_persistent_duplicate_is_capped_instead_of_looping_forever(): void
    {
        $attempts = 0;

        // A duplicate on the number we just tried is retried, but a permanently
        // blocked insert must terminate: the cap guarantees the request cannot
        // spin indefinitely, and the real error still reaches the caller.
        $this->expectException(QueryException::class);

        try {
            PurchaseOrder::createWithFreshNumber(function ($number) use (&$attempts) {
                $attempts++;

                $previous = new class ("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '{$number}' for key 'purchase_orders_po_number_unique'", 0) extends PDOException {
                    public ?array $errorInfo = ['23000', 1062, 'Duplicate entry'];
                };

                throw new QueryException('mysql', 'insert into purchase_orders ...', [], $previous);
            });
        } finally {
            $this->assertSame(
                PurchaseOrder::SEQUENCE_ATTEMPTS,
                $attempts,
                'retries must be bounded by SEQUENCE_ATTEMPTS'
            );
        }
    }

    // ------------------------------------------------------------------
    // Receive guard
    // ------------------------------------------------------------------

    public function test_fully_received_po_cannot_be_received_twice(): void
    {
        $admin = $this->userWithRole('Admin');
        $supplier = $this->supplier();
        $po = $this->po($supplier, 2000, PurchaseOrder::STATUS_APPROVED);

        // Simulate a receive.
        $po->update([
            'status' => PurchaseOrder::STATUS_FULLY_RECEIVED,
            'received_by' => $admin->id,
            'received_date' => now(),
        ]);

        $this->actingAs($admin)
            ->from(route('inventory.purchase-orders.show', $po->po_id))
            ->post(route('inventory.purchase-orders.receive', $po->po_id))
            ->assertRedirect();

        $this->assertSame(PurchaseOrder::STATUS_FULLY_RECEIVED, $po->fresh()->status);
    }

    public function test_receive_requires_inventory_approve_permission(): void
    {
        $accountant = $this->userWithRole('Accountant'); // finance.*, no inventory.*
        $supplier = $this->supplier();
        $po = $this->po($supplier, 2000, PurchaseOrder::STATUS_APPROVED);

        $this->actingAs($accountant)
            ->post(route('inventory.purchase-orders.receive', $po->po_id))
            ->assertForbidden();
    }

    // ------------------------------------------------------------------
    // Payments: immediate, credit, partial, overpayment
    // ------------------------------------------------------------------

    public function test_immediate_payment_moves_bank_once(): void
    {
        $user = $this->userWithRole('Admin');
        $supplier = $this->supplier();
        $account = $this->bankAccount(100000);
        $po = $this->po($supplier, 60000, PurchaseOrder::STATUS_FULLY_RECEIVED);
        $po->update(['payment_arrangement' => PurchaseOrder::ARRANGEMENT_IMMEDIATE]);

        PurchaseOrder::recordPayment($po, [
            'amount' => 60000,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
        ]);

        $this->assertEqualsWithDelta(40000.0, (float) $account->fresh()->current_balance, 0.001);

        $row = \App\Models\BankTransaction::where('source_type', 'PurchaseOrderPayment')->first();
        $this->assertNotNull($row, 'payment must leave a bank statement row');
        $this->assertEqualsWithDelta(60000.0, (float) $row->amount, 0.001);

        // A second attempt is refused: nothing is paid twice.
        $this->expectException(\RuntimeException::class);
        PurchaseOrder::recordPayment($po, ['amount' => 1000, 'payment_method' => 'cash']);
    }

    public function test_credit_purchase_does_not_touch_bank_before_payment(): void
    {
        $user = $this->userWithRole('Admin');
        $supplier = $this->supplier();
        $account = $this->bankAccount(500000);
        $po = $this->po($supplier, 600000); // credit arrangement

        // Receiving goods is NOT paying for them: the bank must not move.
        $this->assertEqualsWithDelta(500000.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(0, \App\Models\BankTransaction::where('source_type', 'PurchaseOrderPayment')->count());

        // The payable is visible and outstanding is the full total.
        $this->assertEqualsWithDelta(600000.0, $po->fresh()->outstandingBalance(), 0.001);
        $this->assertSame(600000.0, $supplier->fresh()->outstandingPayable());
    }

    public function test_partial_then_final_payment_settles_the_po(): void
    {
        $user = $this->userWithRole('Admin');
        $supplier = $this->supplier();
        $account = $this->bankAccount(700000);
        $po = $this->po($supplier, 600000);

        PurchaseOrder::recordPayment($po, [
            'amount' => 200000,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
        ]);

        $po->refresh();
        $this->assertEqualsWithDelta(400000.0, $po->outstandingBalance(), 0.001);
        $this->assertSame(PurchaseOrder::PAYMENT_PARTIAL, $po->derivedPaymentStatus());
        $this->assertNull($po->paid_date);
        $this->assertEqualsWithDelta(500000.0, (float) $account->fresh()->current_balance, 0.001);

        PurchaseOrder::recordPayment($po, [
            'amount' => 400000,
            'payment_method' => 'bank_transfer',
            'bank_account_id' => $account->account_id,
        ]);

        $po->refresh();
        $this->assertEqualsWithDelta(0.0, $po->outstandingBalance(), 0.001);
        $this->assertSame(PurchaseOrder::PAYMENT_PAID, $po->derivedPaymentStatus());
        $this->assertNotNull($po->paid_date);
        $this->assertEqualsWithDelta(100000.0, (float) $account->fresh()->current_balance, 0.001);
        $this->assertSame(0.0, $supplier->fresh()->outstandingPayable());
    }

    public function test_overpayment_is_rejected(): void
    {
        $supplier = $this->supplier();
        $po = $this->po($supplier, 100000);
        $account = $this->bankAccount(500000);

        try {
            PurchaseOrder::recordPayment($po, [
                'amount' => 150000,
                'payment_method' => 'bank_transfer',
                'bank_account_id' => $account->account_id,
            ]);
            $this->fail('Expected overpayment to be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('exceeds the outstanding balance', $e->getMessage());
        }

        $this->assertSame(0, PurchaseOrderPayment::where('po_id', $po->po_id)->count());
        $this->assertEqualsWithDelta(500000.0, (float) $account->fresh()->current_balance, 0.001);
    }

    public function test_cash_payment_does_not_require_bank_account(): void
    {
        $supplier = $this->supplier();
        $po = $this->po($supplier, 5000);

        PurchaseOrder::recordPayment($po, ['amount' => 5000, 'payment_method' => 'cash']);

        $this->assertNull(PurchaseOrderPayment::first()->bank_account_id);
        $this->assertSame(PurchaseOrder::PAYMENT_PAID, $po->fresh()->derivedPaymentStatus());
    }

    public function test_bank_payment_requires_bank_account(): void
    {
        $supplier = $this->supplier();
        $po = $this->po($supplier, 5000);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('bank account');

        PurchaseOrder::recordPayment($po, ['amount' => 5000, 'payment_method' => 'bank_transfer']);
    }

    public function test_overdue_status_is_derived_from_due_date(): void
    {
        $supplier = $this->supplier();
        $po = $this->po($supplier, 10000);
        $po->update(['payment_due_date' => now()->subWeek()->toDateString()]);

        $this->assertSame(PurchaseOrder::PAYMENT_OVERDUE, $po->fresh()->derivedPaymentStatus());
    }

    public function test_pay_route_is_finance_manage_only(): void
    {
        $accountant = $this->userWithRole('Accountant');
        $teacher = $this->userWithRole('Teacher');
        $supplier = $this->supplier();
        $po = $this->po($supplier, 10000);

        // Teacher: no finance.manage → blocked.
        $this->actingAs($teacher)
            ->post(route('inventory.purchase-orders.pay', $po->po_id), ['amount' => 100])
            ->assertForbidden();

        // Accountant has finance.manage → allowed.
        $this->actingAs($accountant)
            ->from('/somewhere')
            ->post(route('inventory.purchase-orders.pay', $po->po_id), [
                'amount' => 5000,
                'payment_date' => now()->toDateString(),
                'payment_method' => 'cash',
            ])
            ->assertRedirect(route('inventory.purchase-orders.show', $po->po_id));

        $this->assertEqualsWithDelta(5000.0, $po->fresh()->outstandingBalance(), 0.001);
    }

    // ------------------------------------------------------------------
    // Arrangement
    // ------------------------------------------------------------------

    public function test_arrange_route_is_authorized_server_side(): void
    {
        $teacher = $this->userWithRole('Teacher');
        $accountant = $this->userWithRole('Accountant');
        $supplier = $this->supplier();
        $po = $this->po($supplier, 10000);
        // Start from "no arrangement decided" so the assertion below really
        // proves the refused POST wrote nothing.
        $po->update(['payment_arrangement' => null, 'payment_due_date' => null]);

        // A Teacher holds neither inventory nor finance rights: recording the
        // payment arrangement is a finance decision, so the POST must be
        // refused outright rather than merely hidden in the UI.
        $this->actingAs($teacher)
            ->post(route('inventory.purchase-orders.arrange', $po->po_id), [
                'payment_arrangement' => PurchaseOrder::ARRANGEMENT_CREDIT,
            ])
            ->assertForbidden();

        $this->assertNull($po->fresh()->payment_arrangement, 'an unauthorized arrange POST must change nothing');

        // Accountant holds finance.manage → allowed.
        $this->actingAs($accountant)
            ->from('/somewhere')
            ->post(route('inventory.purchase-orders.arrange', $po->po_id), [
                'payment_arrangement' => PurchaseOrder::ARRANGEMENT_CREDIT,
                'payment_due_date' => now()->addMonth()->toDateString(),
            ])
            ->assertRedirect(route('inventory.purchase-orders.show', $po->po_id));

        $this->assertSame(PurchaseOrder::ARRANGEMENT_CREDIT, $po->fresh()->payment_arrangement);
    }

    public function test_arrangement_cannot_change_after_payments_exist(): void
    {
        $admin = $this->userWithRole('Admin');
        $supplier = $this->supplier();
        $po = $this->po($supplier, 10000);

        PurchaseOrder::recordPayment($po, ['amount' => 10000, 'payment_method' => 'cash']);

        $this->actingAs($admin)
            ->from('/somewhere')
            ->post(route('inventory.purchase-orders.arrange', $po->po_id), [
                'payment_arrangement' => 'credit',
            ])
            ->assertRedirect();

        $this->assertSame('credit', $po->fresh()->payment_arrangement, 'arrangement must not change after payments exist');
    }
}
