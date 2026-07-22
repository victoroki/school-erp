<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AppBaseController;
use App\Services\InventoryService;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\Supplier;
use App\Models\InventoryTransaction;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facade;

class InventoryController extends AppBaseController
{
    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
        $this->middleware('can:inventory.view')->only(['dashboard', 'show']);
        $this->middleware('can:inventory.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display inventory dashboard
     */
    public function dashboard()
    {
        $totalValue = InventoryItem::sum(DB::raw('quantity * cost_per_unit'));
        $itemsInStock = InventoryItem::sum('quantity');
        $lowStockItems = $this->inventoryService->getLowStockAlerts();
        $outOfStockItems = $this->inventoryService->getOutOfStockItems();
        $recentTransactions = InventoryTransaction::with(['item', 'handledBy'])->latest()->limit(10)->get();

        return view('inventory.dashboard', compact(
            'totalValue',
            'itemsInStock',
            'lowStockItems',
            'outOfStockItems',
            'recentTransactions'
        ));
    }

    /**
     * Show add stock form
     */
    public function showAddStockForm()
    {
        $items = InventoryItem::with('category')->get();
        $suppliers = Supplier::all();
        
        return view('inventory.add_stock', compact('items', 'suppliers'));
    }

    /**
     * Process add stock
     */
    public function addStock(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,item_id',
            'quantity' => 'required|integer|min:1',
            'transaction_date' => 'required|date',
            'remarks' => 'nullable|string|max:255',
            'supplier_id' => 'nullable|exists:suppliers,supplier_id'
        ]);

        $validated['handled_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $transaction = $this->inventoryService->addStock($validated);
            
            DB::commit();
            
            flash()->success('Stock added successfully.');
            return redirect()->route('inventory.dashboard');
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Error adding stock: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show issue stock form
     */
    public function showIssueStockForm()
    {
        $items = InventoryItem::where('quantity', '>', 0)->with('category')->get();
        $staff = []; // You can fetch staff/users who can receive items
        
        return view('inventory.issue_stock', compact('items', 'staff'));
    }

    /**
     * Process issue stock
     */
    public function issueStock(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,item_id',
            'quantity' => 'required|integer|min:1',
            'issued_to' => 'required|integer', // Could be staff/user ID
            'transaction_date' => 'required|date',
            'remarks' => 'nullable|string|max:255'
        ]);

        $validated['handled_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $transaction = $this->inventoryService->issueStock($validated);
            
            DB::commit();
            
            flash()->success('Stock issued successfully.');
            return redirect()->route('inventory.dashboard');
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Error issuing stock: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show stock adjustment form
     */
    public function showAdjustStockForm()
    {
        $items = InventoryItem::with('category')->get();
        
        return view('inventory.adjust_stock', compact('items'));
    }

    /**
     * Process stock adjustment
     */
    public function adjustStock(Request $request)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:inventory_items,item_id',
            'actual_quantity' => 'required|integer|min:0',
            'transaction_date' => 'required|date',
            'remarks' => 'nullable|string|max:255'
        ]);

        $validated['handled_by'] = auth()->id();

        DB::beginTransaction();
        try {
            $transaction = $this->inventoryService->adjustStock($validated);
            
            DB::commit();
            
            flash()->success('Stock adjusted successfully.');
            return redirect()->route('inventory.dashboard');
        } catch (\Exception $e) {
            DB::rollback();
            flash()->error('Error adjusting stock: ' . $e->getMessage());
            return back()->withInput();
        }
    }

    /**
     * Show stock movement history
     */
    public function stockMovementHistory()
    {
        $transactions = InventoryTransaction::with(['item', 'handledBy', 'issuedTo'])
            ->orderBy('transaction_date', 'desc')
            ->paginate(20);

        return view('inventory.stock_movement_history', compact('transactions'));
    }
}
