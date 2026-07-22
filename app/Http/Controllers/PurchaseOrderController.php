<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Flash;
use DB;

class PurchaseOrderController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:inventory.view')->only(['index', 'show']);
        $this->middleware('can:inventory.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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

            $po = PurchaseOrder::create([
                'po_number' => 'PO-' . date('Ymd') . '-' . rand(100, 999),
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => 'Pending_Approval',
                'sub_total' => $subTotal,
                'tax_amount' => $tax,
                'grand_total' => $grandTotal,
                'delivery_address' => $request->delivery_address ?: 'School Main Store',
            ]);

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
        $purchaseOrder = PurchaseOrder::with(['items.item', 'supplier', 'approvedBy', 'receivedBy'])->findOrFail($id);
        return view('inventory.purchase_orders.show', compact('purchaseOrder'));
    }

    public function receive(Request $request, $id)
    {
        $po = PurchaseOrder::findOrFail($id);
        
        DB::beginTransaction();
        try {
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

            DB::commit();
            Flash::success('Stock received and inventory updated.');
            return redirect()->route('inventory.purchase-orders.index');
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error receiving stock: ' . $e->getMessage());
            return back();
        }
    }
}
