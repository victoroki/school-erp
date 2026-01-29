<?php

namespace App\Http\Controllers;

use App\Models\Requisition;
use App\Models\InventoryItem;
use App\Models\Department;
use Illuminate\Http\Request;
use Flash;
use DB;

class RequisitionController extends AppBaseController
{
    public function index()
    {
        $requisitions = Requisition::with(['requestedBy', 'department'])->latest()->paginate(15);
        return view('inventory.requisitions.index', compact('requisitions'));
    }

    public function create()
    {
        $items = InventoryItem::where('quantity', '>', 0)->get();
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
            $requisition = Requisition::create([
                'requisition_number' => 'REQ-' . date('Ymd') . '-' . rand(100, 999),
                'requested_by' => auth()->id(),
                'department_id' => $request->department_id,
                'date_needed' => $request->date_needed,
                'priority' => $request->priority,
                'justification' => $request->justification,
                'status' => 'Pending',
                'total_cost' => 0, // Calculated later
            ]);

            $totalCost = 0;
            foreach ($request->items as $itemData) {
                $item = InventoryItem::find($itemData['item_id']);
                $cost = $item->cost_per_unit * $itemData['quantity'];
                $totalCost += $cost;

                $requisition->items()->create([
                    'item_id' => $itemData['item_id'],
                    'quantity_requested' => $itemData['quantity'],
                    'unit_price' => $item->cost_per_unit,
                    'total_price' => $cost,
                ]);
            }

            $requisition->update(['total_cost' => $totalCost]);

            DB::commit();
            Flash::success('Requisition submitted for approval.');
            return redirect()->route('inventory.requisitions.index');
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error creating requisition: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    public function show($id)
    {
        $requisition = Requisition::with(['items.item', 'requestedBy', 'department', 'approvedBy'])->findOrFail($id);
        return view('inventory.requisitions.show', compact('requisition'));
    }

    public function approve(Request $request, $id)
    {
        $requisition = Requisition::findOrFail($id);
        
        if ($request->action == 'approve') {
            $requisition->update([
                'status' => 'Approved',
                'approved_by' => auth()->id(),
                'approved_date' => now(),
            ]);
            Flash::success('Requisition approved.');
        } else {
            $requisition->update([
                'status' => 'Rejected',
                'rejected_reason' => $request->reason,
            ]);
            Flash::error('Requisition rejected.');
        }

        return redirect()->route('inventory.requisitions.index');
    }
}
