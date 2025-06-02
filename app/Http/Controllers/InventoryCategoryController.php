<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryCategoryRequest;
use App\Http\Requests\UpdateInventoryCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\InventoryCategoryRepository;
use Illuminate\Http\Request;
use Flash;

class InventoryCategoryController extends AppBaseController
{
    /** @var InventoryCategoryRepository $inventoryCategoryRepository*/
    private $inventoryCategoryRepository;

    public function __construct(InventoryCategoryRepository $inventoryCategoryRepo)
    {
        $this->inventoryCategoryRepository = $inventoryCategoryRepo;
    }

    /**
     * Display a listing of the InventoryCategory.
     */
    public function index(Request $request)
    {
        $inventoryCategories = $this->inventoryCategoryRepository->paginate(10);

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

        Flash::success('Inventory Category saved successfully.');

        return redirect(route('inventoryCategories.index'));
    }

    /**
     * Display the specified InventoryCategory.
     */
    public function show($id)
    {
        $inventoryCategory = $this->inventoryCategoryRepository->find($id);

        if (empty($inventoryCategory)) {
            Flash::error('Inventory Category not found');

            return redirect(route('inventoryCategories.index'));
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

            return redirect(route('inventoryCategories.index'));
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

            return redirect(route('inventoryCategories.index'));
        }

        $inventoryCategory = $this->inventoryCategoryRepository->update($request->all(), $id);

        Flash::success('Inventory Category updated successfully.');

        return redirect(route('inventoryCategories.index'));
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

            return redirect(route('inventoryCategories.index'));
        }

        $this->inventoryCategoryRepository->delete($id);

        Flash::success('Inventory Category deleted successfully.');

        return redirect(route('inventoryCategories.index'));
    }
}
