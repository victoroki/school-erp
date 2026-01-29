<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use Illuminate\Http\Request;
use Flash;

class SupplierController extends AppBaseController
{
    /** @var SupplierRepository $supplierRepository*/
    private $supplierRepository;

    public function __construct(SupplierRepository $supplierRepo)
    {
        $this->supplierRepository = $supplierRepo;
    }

    /**
     * Display a listing of the Supplier.
     */
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status == 'active' ? 1 : 0);
        }

        $suppliers = $query->paginate(12);

        return view('suppliers.index')
            ->with('suppliers', $suppliers);
    }

    /**
     * Show the form for creating a new Supplier.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created Supplier in storage.
     */
    public function store(CreateSupplierRequest $request)
    {
        $input = $request->all();

        if (empty($input['code'])) {
            $input['code'] = 'SUP-' . date('Y') . '-' . rand(100, 999);
        }

        $supplier = $this->supplierRepository->create($input);

        Flash::success('Supplier saved successfully.');

        return redirect(route('suppliers.index'));
    }

    /**
     * Display the specified Supplier.
     */
    public function show($id)
    {
        $supplier = Supplier::with(['inventoryItems', 'purchaseOrders'])->find($id);

        if (empty($supplier)) {
            Flash::error('Supplier not found');

            return redirect(route('suppliers.index'));
        }

        return view('suppliers.show')->with('supplier', $supplier);
    }

    /**
     * Show the form for editing the specified Supplier.
     */
    public function edit($id)
    {
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            Flash::error('Supplier not found');

            return redirect(route('suppliers.index'));
        }

        return view('suppliers.edit')->with('supplier', $supplier);
    }

    /**
     * Update the specified Supplier in storage.
     */
    public function update($id, UpdateSupplierRequest $request)
    {
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            Flash::error('Supplier not found');

            return redirect(route('suppliers.index'));
        }

        $supplier = $this->supplierRepository->update($request->all(), $id);

        Flash::success('Supplier updated successfully.');

        return redirect(route('suppliers.index'));
    }

    /**
     * Remove the specified Supplier from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $supplier = $this->supplierRepository->find($id);

        if (empty($supplier)) {
            Flash::error('Supplier not found');

            return redirect(route('suppliers.index'));
        }

        $this->supplierRepository->delete($id);

        Flash::success('Supplier deleted successfully.');

        return redirect(route('suppliers.index'));
    }
}
