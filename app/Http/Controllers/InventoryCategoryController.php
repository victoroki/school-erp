<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryCategoryRequest;
use App\Http\Requests\UpdateInventoryCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\InventoryCategoryRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class InventoryCategoryController extends AppBaseController
{
    /** @var InventoryCategoryRepository $inventoryCategoryRepository*/
    private $inventoryCategoryRepository;

    public function __construct(InventoryCategoryRepository $inventoryCategoryRepo)
    {
        $this->inventoryCategoryRepository = $inventoryCategoryRepo;
        $this->middleware('can:inventory.view')->only(['index', 'show']);
        $this->middleware('can:inventory.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the InventoryCategory.
     */
    public function index(Request $request)
    {
        $query = \App\Models\InventoryCategory::query();

        if ($request->has('type')) {
            $query->where('category_type', $request->type);
        }

        $inventoryCategories = $query->withCount('inventoryItems')
            ->with(['inventoryItems' => function($q) {
                $q->select('item_id', 'category_id', 'quantity', 'cost_per_unit');
            }])
            ->paginate(12);

        return view('inventory_categories.index')
            ->with('inventoryCategories', $inventoryCategories);
    }

    /**
     * Show the form for creating a new InventoryCategory.
     */
    public function create()
    {
        return view('inventory_categories.create');
    }

    /**
     * Store a newly created InventoryCategory in storage.
     */
    public function store(CreateInventoryCategoryRequest $request)
    {
        $input = $request->all();

        $inventoryCategory = $this->inventoryCategoryRepository->create($input);

        AuditTrail::log('Inventory Category', 'CREATE', $inventoryCategory->category_id, null, $inventoryCategory->toArray());

        Flash::success('Inventory Category saved successfully.');

        return redirect(route('inventory-categories.index'));
    }

    /**
     * Display the specified InventoryCategory.
     */
    public function show($id)
    {
        $inventoryCategory = \App\Models\InventoryCategory::with(['inventoryItems' => function($q) {
            $q->with('supplier');
        }])->find($id);

        if (empty($inventoryCategory)) {
            Flash::error('Inventory Category not found');

            return redirect(route('inventory-categories.index'));
        }

        return view('inventory_categories.show')->with('inventoryCategory', $inventoryCategory);
    }

    /**
     * Show the form for editing the specified InventoryCategory.
     */
    public function edit($id)
    {
        $inventoryCategory = $this->inventoryCategoryRepository->find($id);

        if (empty($inventoryCategory)) {
            Flash::error('Inventory Category not found');

            return redirect(route('inventory-categories.index'));
        }

        return view('inventory_categories.edit')->with('inventoryCategory', $inventoryCategory);
    }

    /**
     * Update the specified InventoryCategory in storage.
     */
    public function update($id, UpdateInventoryCategoryRequest $request)
    {
        $inventoryCategory = $this->inventoryCategoryRepository->find($id);

        if (empty($inventoryCategory)) {
            Flash::error('Inventory Category not found');

            return redirect(route('inventory-categories.index'));
        }

        $oldData = $inventoryCategory->toArray();
        $inventoryCategory = $this->inventoryCategoryRepository->update($request->all(), $id);

        AuditTrail::log('Inventory Category', 'UPDATE', $inventoryCategory->category_id, $oldData, $inventoryCategory->toArray());

        Flash::success('Inventory Category updated successfully.');

        return redirect(route('inventory-categories.index'));
    }

    /**
     * Remove the specified InventoryCategory from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $inventoryCategory = $this->inventoryCategoryRepository->find($id);

        if (empty($inventoryCategory)) {
            Flash::error('Inventory Category not found');

            return redirect(route('inventory-categories.index'));
        }

        $oldData = $inventoryCategory->toArray();
        $this->inventoryCategoryRepository->delete($id);

        AuditTrail::log('Inventory Category', 'DELETE', $id, $oldData, null);

        Flash::success('Inventory Category deleted successfully.');

        return redirect(route('inventory-categories.index'));
    }
}
