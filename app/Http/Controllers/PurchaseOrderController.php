<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientFundsException;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\InventoryItem;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use DB;

class PurchaseOrderController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:inventory.view')->only(['index', 'show']);
        $this->middleware('can:inventory.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
        // Receiving goods and paying suppliers are control actions, not mere
        // edits: receiving commits stock, payment commits money. Deciding
        // whether an order is paid now or on credit is a finance decision, so
        // it sits behind the same gate as the payment itself.
        $this->middleware('can:inventory.approve')->only(['receive']);
        $this->middleware('can:finance.manage')->only(['pay', 'arrange']);
    }

    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier'])->latest()->paginate(15);
        return view('inventory.purchase_orders.index', compact('purchaseOrders'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', 1)->get();
        $items = InventoryItem::all();
        return view('inventory.purchase_orders.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'required|date|after_or_equal:order_date',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $subTotal = 0;
            foreach ($request->items as $item) {
                $subTotal += $item['quantity'] * $item['unit_price'];
            }

            $tax = $subTotal * 0.16; // Example 16% tax
            $grandTotal = $subTotal + $tax;

            // Retries with a fresh number if the unique index refuses a
            // collision, so a simultaneous save never surfaces as an error.
            $po = PurchaseOrder::createWithFreshNumber(function ($poNumber) use ($request, $subTotal, $tax, $grandTotal) {
                return PurchaseOrder::create([
                    'po_number' => $poNumber,
                    'supplier_id' => $request->supplier_id,
                    'order_date' => $request->order_date,
                    'expected_delivery_date' => $request->expected_delivery_date,
                    'status' => 'Pending_Approval',
                    'sub_total' => $subTotal,
                    'tax_amount' => $tax,
                    'grand_total' => $grandTotal,
                    'delivery_address' => $request->delivery_address ?: 'School Main Store',
                ]);
            });

            foreach ($request->items as $itemData) {
                // Fetch the item to get its name
                $inventoryItem = \App\Models\InventoryItem::find($itemData['item_id']);
                
                $po->items()->create([
                    'item_id' => $itemData['item_id'],
                    'item_name' => $inventoryItem ? $inventoryItem->name : 'Unknown Item',
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemData['quantity'] * $itemData['unit_price'],
                ]);
            }

            AuditTrail::log('Purchase Order', 'CREATE', $po->po_id, null, $po->toArray());

            DB::commit();
            Flash::success('Purchase Order created and awaiting approval.');
            return redirect()->route('inventory.purchase-orders.index');
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error creating purchase order: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function show($id)
    {
        // requisition: shown as the source document when this PO was generated
        // from an approved requisition. payments.bankAccount feeds the payment
        // panel and history on the same page.
        $purchaseOrder = PurchaseOrder::with(['items.item', 'supplier', 'approvedBy', 'receivedBy', 'requisition', 'payments.bankAccount'])->findOrFail($id);
        return view('inventory.purchase_orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);

        // Double-receive guard: a second POST must not re-add stock or
        // overwrite who received it. Cancelled orders can never be received.
        if ($po->status === PurchaseOrder::STATUS_CANCELLED) {
            Flash::error('A cancelled purchase order cannot be received.');
            return redirect()->back();
        }

        if ($po->status === PurchaseOrder::STATUS_FULLY_RECEIVED) {
            Flash::error('This purchase order has already been fully received.');
            return redirect()->back();
        }

        DB::beginTransaction();
        try {
            $oldStatus = $po->status;
            $po->update([
                'status' => 'Fully_Received',
                'received_by' => auth()->id(),
                'received_date' => now(),
            ]);

            // Update inventory quantities
            foreach ($po->items as $poItem) {
                $item = $poItem->item;
                $item->increment('quantity', $poItem->quantity); // FIXED: Use "quantity", not "quantity_ordered"
                
                // Record transaction
                \App\Models\InventoryTransaction::create([
                    'item_id' => $item->item_id,
                    'transaction_type' => 'purchase',
                    'quantity' => $poItem->quantity, // FIXED: Use "quantity", not "quantity_ordered"
                    'balance_after' => $item->refresh()->quantity, // Make sure balance_after reflects increment

                    'transaction_date' => now(),
                    'handled_by' => auth()->id(),
                    'remarks' => 'Received from PO #' . $po->po_number,
                ]);
            }

            AuditTrail::log('Purchase Order', 'RECEIVE', $po->po_id, ['status' => $oldStatus], $po->toArray());

            DB::commit();
            Flash::success('Stock received and inventory updated.');
            return redirect()->route('inventory.purchase-orders.index');
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error receiving stock: ' . $e->getMessage());
            return back();
        }
    }

    /**
     * Pay the supplier for this purchase order (full or partial).
     *
     * The arrangement (immediate vs credit) is captured here on the first
     * payment if the order does not have one yet, so every order that reaches
     * the money step ends up with a coherent record.
     */
    public function pay(Request $request, $id)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:' . implode(',', \App\Models\PurchaseOrderPayment::PAYMENT_METHODS),
            'bank_account_id' => 'nullable|exists:bank_accounts,account_id',
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $po = PurchaseOrder::with('supplier')->findOrFail($id);

        try {
            PurchaseOrder::recordPayment($po, $validated);
        } catch (InsufficientFundsException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        } catch (\RuntimeException $e) {
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        Flash::success(sprintf(
            'Payment of %s recorded for %s. Outstanding: %s.',
            \App\Support\Money::format((float) $validated['amount']),
            $po->po_number,
            \App\Support\Money::format($po->fresh()->outstandingBalance())
        ));

        return redirect()->route('inventory.purchase-orders.show', $po->po_id);
    }

    /**
     * Record the payment arrangement for a purchase order (immediate vs
     * credit). Used by the PO show page and the requisition approval flow.
     */
    public function arrange(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_arrangement' => 'required|in:immediate,credit',
            'payment_due_date' => 'nullable|required_if:payment_arrangement,credit|date|after_or_equal:today',
            'credit_terms' => 'nullable|string|max:50',
        ]);

        $po = PurchaseOrder::findOrFail($id);

        if ($po->paidAmount() > 0) {
            Flash::error('The payment arrangement can no longer be changed once payments exist.');
            return redirect()->back();
        }

        $old = $po->only(['payment_arrangement', 'payment_due_date', 'credit_terms']);

        $po->update([
            'payment_arrangement' => $validated['payment_arrangement'],
            'payment_due_date' => $validated['payment_arrangement'] === 'credit'
                ? ($validated['payment_due_date'] ?? null)
                : null,
            'credit_terms' => $validated['credit_terms'] ?? null,
        ]);

        AuditTrail::log('Purchase Order', 'ARRANGE PAYMENT', $po->po_id, $old, $po->only(['payment_arrangement', 'payment_due_date', 'credit_terms']));

        Flash::success($validated['payment_arrangement'] === 'credit'
            ? 'Order marked as credit. It will appear under supplier payables until paid.'
            : 'Order marked for immediate payment.');

        return redirect()->route('inventory.purchase-orders.show', $po->po_id);
    }
}
