<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\InventoryItem;
use App\Models\Department;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use DB;

class RequisitionController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:inventory.view')->only(['index', 'show']);
        $this->middleware('can:inventory.manage')->only(['create', 'store']);
        $this->middleware('can:inventory.approve')->only(['approve']);
    }

    public function index()
    {
        // For 'My Requisitions', we filter by the logged-in user
        // If you want admins to see everything, you can add a check here
        $query = Requisition::with(['requestedBy', 'department']);
        
        if (!auth()->user()->hasPermission('inventory.manage')) {
            $query->where('requested_by', auth()->id());
        }

        $requisitions = $query->latest()->paginate(15);
        return view('inventory.requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        // Allow requesting all items, even those currently out of stock
        $items = InventoryItem::all();
        $departments = Department::pluck('name', 'department_id');
        return view('inventory.requisitions.create', compact('items', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,department_id',
            'date_needed' => 'required|date|after_or_equal:today',
            'priority' => 'required|in:Low,Medium,High,Urgent',
            'justification' => 'required|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:inventory_items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $requisition = Requisition::createWithFreshNumber(function ($requisitionNumber) use ($request) {
                return Requisition::create([
                    'requisition_number' => $requisitionNumber,
                    'requested_by' => auth()->id(),
                    'department_id' => $request->department_id,
                    'date_needed' => $request->date_needed,
                    'priority' => $request->priority,
                    'justification' => $request->justification,
                    'status' => 'Pending',
                    'total_cost' => 0,
                ]);
            });

            $totalCost = 0;
            foreach ($request->items as $itemData) {
                $item = InventoryItem::find($itemData['item_id']);
                $cost = ($item->cost_per_unit ?? 0) * $itemData['quantity'];
                $totalCost += $cost;

                $requisition->items()->create([
                    'item_id' => $itemData['item_id'],
                    'item_name' => $item->name,
                    'quantity_needed' => $itemData['quantity'],
                    'estimated_price' => $item->cost_per_unit ?? 0,
                    'purpose' => $request->justification, // Default to main justification
                    'quantity_fulfilled' => 0,
                ]);
            }

            $requisition->update(['total_cost' => $totalCost]);

            AuditTrail::log('Requisition', 'CREATE', $requisition->requisition_id, null, $requisition->toArray());

            DB::commit();
            Flash::success('Requisition submitted successfully.');
            return redirect()->route('inventory.requisitions.index');
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error creating requisition: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function show($id)
    {
        // purchaseOrders drives the "Generated Purchase Order" link on the
        // show page once the requisition has been approved.
        $requisition = Requisition::with(['items.item', 'requestedBy', 'department', 'approvedBy', 'purchaseOrders'])->findOrFail($id);
        return view('inventory.requisitions.show', compact('requisition'));
    }

    public function approve(Request $request, $id)
    {
        $requisition = Requisition::findOrFail($id);
        $service = app(\App\Services\RequisitionApprovalService::class);

        if ($request->action == 'approve') {
            // A purchase order needs a supplier, and that choice is the
            // approver's procurement decision — the requisition itself does not
            // name one.
            $request->validate([
                'supplier_id' => 'required|exists:suppliers,supplier_id',
            ]);

            try {
                $purchaseOrder = $service->approveAndCreateOrder(
                    $requisition,
                    (int) $request->supplier_id,
                    $request->reason
                );
            } catch (\RuntimeException $e) {
                Flash::warning($e->getMessage());

                return redirect()->route('inventory.requisitions.show', $requisition->requisition_id);
            }

            Flash::success('Requisition approved — purchase order ' . $purchaseOrder->po_number
                . ' was generated from it and is awaiting PO approval.');

            return redirect()->route('inventory.purchase-orders.show', $purchaseOrder->po_id);
        }

        try {
            $service->reject($requisition, $request->reason);
        } catch (\RuntimeException $e) {
            Flash::warning($e->getMessage());

            return redirect()->route('inventory.requisitions.show', $requisition->requisition_id);
        }

        Flash::error('Requisition rejected.');

        return redirect()->route('inventory.requisitions.index');
    }
}
