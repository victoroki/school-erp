<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\Requisition;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Requisition → purchase order flow.
 *
 * Approving a requisition must generate a purchase order from its items and
 * link the two records; the approval POST then redirects to that PO. Direct
 * PO creation remains independent (nullable link). Double-submitted approvals
 * must not mint a second order.
 */
class RequisitionToPoTest extends TestCase
{
    use RefreshDatabase;

    private User $approver;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->approver = User::factory()->create();
        $this->approver->assignRole('Super Admin');
    }

    private function makePendingRequisition(int $itemCount = 2): Requisition
    {
        static $seq = 0;
        $seq++;

        $department = Department::first() ?: Department::create([
            'name' => 'ProcReq Dept ' . $seq . '-' . uniqid(),
        ]);

        $requisition = Requisition::create([
            'requisition_number' => 'REQ-RPT-' . str_pad((string) $seq, 3, '0', STR_PAD_LEFT) . '-' . substr(uniqid(), -4),
            'requested_by' => $this->approver->id,
            'department_id' => $department->department_id,
            'date_needed' => now()->addWeek()->toDateString(),
            'priority' => 'Medium',
            'status' => 'Pending',
            'justification' => 'Requisition to PO flow test',
            'total_cost' => 0,
        ]);

        for ($i = 1; $i <= $itemCount; $i++) {
            $item = InventoryItem::create([
                'name' => 'Flow Item ' . $seq . '-' . $i . '-' . substr(uniqid(), -4),
                'item_code' => 'FLW-' . $seq . $i . '-' . substr(uniqid(), -4),
                'unit' => 'pcs',
                'cost_per_unit' => 100 * $i,
                'quantity_in_stock' => 0,
                'reorder_level' => 5,
            ]);

            $requisition->items()->create([
                'item_id' => $item->item_id,
                'item_name' => $item->name,
                'quantity_needed' => 3 * $i,
                'estimated_price' => 100 * $i,
                'purpose' => 'flow test line ' . $i,
                'quantity_fulfilled' => 0,
            ]);
        }

        return $requisition;
    }

    private function approvePayload(Requisition $requisition): array
    {
        $supplier = Supplier::create([
            'name' => 'Flow Supplier ' . uniqid(),
            'contact_person' => 'Test',
            'phone' => '0700000000',
            'email' => uniqid() . '@supplier.test',
            'address' => 'Nairobi',
        ]);

        return [
            'action' => 'approve',
            'supplier_id' => $supplier->supplier_id,
            'reason' => 'Approved for procurement',
        ];
    }

    // ─── The core flow ───────────────────────────────────────────────────

    public function test_approving_a_requisition_generates_a_linked_purchase_order(): void
    {
        $requisition = $this->makePendingRequisition();
        $poCountBefore = PurchaseOrder::count();

        $response = $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $this->approvePayload($requisition));

        // Land on the generated PO — that is the document the approver needs next.
        $po = PurchaseOrder::latest('po_id')->first();

        $response->assertRedirect(route('inventory.purchase-orders.show', $po->po_id));
        $this->assertSame($poCountBefore + 1, PurchaseOrder::count());

        // Linked both ways.
        $this->assertSame($requisition->requisition_id, $po->requisition_id);
        $this->assertSame(1, $requisition->fresh()->purchaseOrders()->count());

        // Requisition side effects.
        $this->assertSame('Approved', $requisition->fresh()->status);
        $this->assertSame($this->approver->id, $requisition->fresh()->approved_by);
        $this->assertNotNull($requisition->fresh()->approved_date);
    }

    public function test_the_generated_po_carries_the_requisition_items_and_totals(): void
    {
        $requisition = $this->makePendingRequisition(3); // 3 lines: qty 3,6,9 @ 100,200,300

        $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $this->approvePayload($requisition));

        $po = PurchaseOrder::latest('po_id')->first();
        $po->load('items');

        $this->assertSame(3, $po->items->count());

        // Line items are copied from the requisition, not retyped:
        // qty 3/6/9 at 100/200/300 per unit.
        $this->assertSame(3, (int) $po->items[0]->quantity);
        $this->assertSame(6, (int) $po->items[1]->quantity);
        $this->assertSame(1200.0, (float) $po->items[1]->total_price); // 6 × 200
        $this->assertSame(2700.0, (float) $po->items[2]->total_price); // 9 × 300

        // Totals follow the same 16% VAT as direct PO creation.
        $this->assertSame(4200.0, (float) $po->sub_total);   // 3·100 + 6·200 + 9·300
        $this->assertSame(672.0, (float) $po->tax_amount);   // 16%
        $this->assertSame(4872.0, (float) $po->grand_total);
    }

    public function test_the_po_show_page_links_back_to_the_source_requisition(): void
    {
        $requisition = $this->makePendingRequisition();

        $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $this->approvePayload($requisition));

        $po = PurchaseOrder::latest('po_id')->first();

        $this->actingAs($this->approver)
            ->get(route('inventory.purchase-orders.show', $po->po_id))
            ->assertOk()
            ->assertSee($requisition->requisition_number);
    }

    public function test_the_requisition_show_page_links_to_the_generated_po(): void
    {
        $requisition = $this->makePendingRequisition();

        $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $this->approvePayload($requisition));

        $po = PurchaseOrder::latest('po_id')->first();

        $this->flushSession();

        $this->actingAs($this->approver)
            ->get(route('inventory.requisitions.show', $requisition->requisition_id))
            ->assertOk()
            ->assertSee($po->po_number);
    }

    // ─── Guards ──────────────────────────────────────────────────────────

    public function test_a_double_submitted_approval_does_not_create_a_second_po(): void
    {
        $requisition = $this->makePendingRequisition();
        $payload = $this->approvePayload($requisition);

        $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $payload);

        $countAfterFirst = PurchaseOrder::count();

        $response = $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $payload);

        $response->assertRedirect(); // bounced back with a warning, not a 500
        $this->assertSame($countAfterFirst, PurchaseOrder::count());
    }

    public function test_approval_requires_a_supplier(): void
    {
        $requisition = $this->makePendingRequisition();

        $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), [
                'action' => 'approve',
                // supplier_id missing
            ])
            ->assertSessionHasErrors('supplier_id');

        // Nothing half-happened: still pending, no PO.
        $this->assertSame('Pending', $requisition->fresh()->status);
        $this->assertSame(0, $requisition->purchaseOrders()->count());
    }

    public function test_rejecting_does_not_generate_a_po(): void
    {
        $requisition = $this->makePendingRequisition();
        $poCountBefore = PurchaseOrder::count();

        $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), [
                'action' => 'reject',
                'reason' => 'Budget exhausted',
            ]);

        $this->assertSame('Rejected', $requisition->fresh()->status);
        $this->assertSame($poCountBefore, PurchaseOrder::count());
        $this->assertStringContainsString('Budget exhausted', (string) $requisition->fresh()->rejected_reason);
    }

    public function test_a_requisition_without_items_cannot_be_approved(): void
    {
        $requisition = $this->makePendingRequisition();
        $requisition->items()->delete();

        $response = $this->actingAs($this->approver)
            ->post(route('inventory.requisitions.approve', $requisition->requisition_id), $this->approvePayload($requisition));

        $response->assertRedirect();
        $this->assertSame('Pending', $requisition->fresh()->status);
    }

    // ─── Direct PO creation stays independent ────────────────────────────

    public function test_a_directly_created_po_has_no_requisition_link(): void
    {
        $supplier = Supplier::first() ?: Supplier::create([
            'name' => 'Direct Supplier ' . uniqid(),
            'phone' => '0711111111',
        ]);
        $item = InventoryItem::first() ?: InventoryItem::create([
            'name' => 'Direct Item ' . uniqid(),
            'item_code' => 'DIR-' . substr(uniqid(), -5),
            'unit' => 'pcs',
            'cost_per_unit' => 50,
            'quantity_in_stock' => 0,
            'reorder_level' => 2,
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-DIRECT-' . rand(100, 999),
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'expected_delivery_date' => now()->addWeek()->toDateString(),
            'delivery_address' => 'School Main Store',
            'status' => 'Pending_Approval',
            'sub_total' => 500,
            'tax_amount' => 80,
            'grand_total' => 580,
        ]);

        $po->items()->create([
            'item_id' => $item->item_id,
            'item_name' => $item->name,
            'quantity' => 10,
            'unit_price' => 50,
            'total_price' => 500,
        ]);

        $this->assertNull($po->fresh()->requisition_id);
        $this->assertNull($po->fresh()->requisition);
    }
}
