<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryItemRequest;
use App\Http\Requests\UpdateInventoryItemRequest;
use App\Http\Controllers\AppBaseController;
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
    }

    /**
     * Display a listing of the InventoryItem.
     */
    public function index(Request $request)
    {
        $inventoryItems = $this->inventoryItemRepository->paginate(10);

        return view('inventory_items.index')
            ->with('inventoryItems', $inventoryItems);
    }

    /**
     * Show the form for creating a new InventoryItem.
     */
    public function create()
    {
        return view('inventory_items.create');
    }

    /**
     * Store a newly created InventoryItem in storage.
     */
    public function store(CreateInventoryItemRequest $request)
    {
        $input = $request->all();

        $inventoryItem = $this->inventoryItemRepository->create($input);

        Flash::success('Inventory Item saved successfully.');

        return redirect(route('inventoryItems.index'));
    }

    /**
     * Display the specified InventoryItem.
     */
    public function show($id)
    {
        $inventoryItem = $this->inventoryItemRepository->find($id);

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventoryItems.index'));
        }

        return view('inventory_items.show')->with('inventoryItem', $inventoryItem);
    }

    /**
     * Show the form for editing the specified InventoryItem.
     */
    public function edit($id)
    {
        $inventoryItem = $this->inventoryItemRepository->find($id);

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventoryItems.index'));
        }

        return view('inventory_items.edit')->with('inventoryItem', $inventoryItem);
    }

    /**
     * Update the specified InventoryItem in storage.
     */
    public function update($id, UpdateInventoryItemRequest $request)
    {
        $inventoryItem = $this->inventoryItemRepository->find($id);

        if (empty($inventoryItem)) {
            Flash::error('Inventory Item not found');

            return redirect(route('inventoryItems.index'));
        }

        $inventoryItem = $this->inventoryItemRepository->update($request->all(), $id);

        Flash::success('Inventory Item updated successfully.');

        return redirect(route('inventoryItems.index'));
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

            return redirect(route('inventoryItems.index'));
        }

        $this->inventoryItemRepository->delete($id);

        Flash::success('Inventory Item deleted successfully.');

        return redirect(route('inventoryItems.index'));
    }
}
