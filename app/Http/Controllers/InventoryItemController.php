<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Supplier;
use App\Repositories\InventoryItemRepository;
use Illuminate\Http\Request;
use Flash;

class InventoryItemController extends AppBaseController
{
    /** @var InventoryItemRepository $inventoryItemRepository*/
    private $inventoryItemRepository;

    public function __construct(InventoryItemRepository $inventoryItemRepo)
    {
        $this->inventoryItemRepository = $inventoryItemRepo;
        $this->middleware('can:inventory.view')->only(['index', 'show']);
        $this->middleware('can:inventory.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    private function getdropdownData(){
        return [
            'categories' => InventoryCategory::pluck('name', 'category_id'),
            'suppliers' => Supplier::pluck('name', 'supplier_id')
        ];
    }

    /**
     * Display a listing of the InventoryItem.
     */
    public function index(Request $request)
    {
        $query = InventoryItem::with(['category', 'supplier']);

        // Advanced Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('item_code', 'like', "%$search%")
                  ->orWhere('asset_tag', 'like', "%$search%");
            });
        }

        // Filtering
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->whereHas('category', function($q) use ($request) {
                $q->where('category_type', $request->type);
            });
        }

        if ($request->has('status')) {
            if ($request->status == 'low_stock') {
                $query->whereColumn('quantity', '<=', 'minimum_quantity')->where('quantity', '>', 0);
            } elseif ($request->status == 'out_of_stock') {
                $query->where('quantity', '<=', 0);
            }
        }

        // Summary Stats (for cards)
        $stats = [
            'total_value' => InventoryItem::all()->sum(fn($i) => $i->quantity * $i->cost_per_unit),
            'items_count' => InventoryItem::sum('quantity'),
            'low_stock'   => InventoryItem::whereColumn('quantity', '<=', 'minimum_quantity')->where('quantity', '>', 0)->count(),
            'out_of_stock' => InventoryItem::where('quantity', '<=', 0)->count(),
        ];

        $inventoryItems = $query->paginate(15);
        $dropdownData = $this->getdropdownData();

        return view('inventory_items.index')
            ->with('inventoryItems', $inventoryItems)
            ->with('categories', $dropdownData['categories'])
            ->with('stats', $stats);
    }

    /**
     * Show the form for creating a new InventoryItem.
     */
    public function create()
    {
        $dropdownData = $this->getdropdownData();
        $categories_objects = InventoryCategory::all(); // To handle dynamic fields via JS if needed
        return view('inventory_items.create', array_merge($dropdownData, ['categories_objects' => $categories_objects]));
    }

    /**
     * Store a newly created InventoryItem in storage.
     */
    public function store(CreateInventoryItemRequest $request)
    {
        $input = $request->all();

        // Auto-generate item code if missing
        if (empty($input['item_code'])) {
            $typePrefix = 'ITEM';
            if (!empty($input['category_id'])) {
                $cat = InventoryCategory::find($input['category_id']);
                if ($cat && $cat->code) {
                    $typePrefix = strtoupper($cat->code);
                }
            }
            $input['item_code'] = $typePrefix . '-' . date('Y') . '-' . strtoupper(substr(uniqid(), -4));
        }

        // Auto-generate asset tag if it's an asset and missing
        if (!empty($input['category_id'])) {
            $cat = InventoryCategory::find($input['category_id']);
            if ($cat && $cat->category_type == 'asset' && empty($input['asset_tag'])) {
                $input['asset_tag'] = 'TAG-' . $input['item_code'];
            }
        }

        $inventoryItem = $this->inventoryItemRepository->create($input);

        Flash::success('Inventory Item saved successfully.');

        return redirect(route('inventory-items.index'));
    }

    /**
     * Display the specified InventoryItem.
     */
    public function show($id)
    {
        $inventoryItem = InventoryItem::with(['category', 'supplier', 'transactions.user'])->find($id);

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventory-items.index'));
        }

        return view('inventory_items.show')->with('inventoryItem', $inventoryItem);
    }

    /**
     * Show the form for editing the specified InventoryItem.
     */
    public function edit($id)
    {
        $inventoryItem = $this->inventoryItemRepository->find($id);
        $dropdownData = $this->getdropdownData();

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventory-items.index'));
        }

        $categories_objects = InventoryCategory::all();

        return view('inventory_items.edit', array_merge(
            ['inventoryItem' => $inventoryItem, 'categories_objects' => $categories_objects],
            $dropdownData
        ));
    }

    /**
     * Update the specified InventoryItem in storage.
     */
    public function update($id, UpdateInventoryItemRequest $request)
    {
        $inventoryItem = $this->inventoryItemRepository->find($id);

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventory-items.index'));
        }

        $inventoryItem = $this->inventoryItemRepository->update($request->all(), $id);

        Flash::success('Inventory Item updated successfully.');

        return redirect(route('inventory-items.index'));
    }

    /**
     * Remove the specified InventoryItem from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $inventoryItem = $this->inventoryItemRepository->find($id);

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventory-items.index'));
        }

        $this->inventoryItemRepository->delete($id);

        Flash::success('Inventory Item deleted successfully.');

        return redirect(route('inventory-items.index'));
    }
}
