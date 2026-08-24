<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFeeCategoryRequest;
use App\Http\Requests\UpdateFeeCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\FeeCategoryRepository;
use App\Models\FeeCategory;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class FeeCategoryController extends AppBaseController
{
    /** @var FeeCategoryRepository $feeCategoryRepository*/
    private $feeCategoryRepository;

    public function __construct(FeeCategoryRepository $feeCategoryRepo)
    {
        $this->feeCategoryRepository = $feeCategoryRepo;
        $this->middleware('can:fees.view')->only(['index', 'show']);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the FeeCategory.
     */
    public function index(Request $request)
    {
        $feeCategories = $this->feeCategoryRepository->paginate(10);

        return view('fee_categories.index')
            ->with('feeCategories', $feeCategories);
    }

    /**
     * Show the form for creating a new FeeCategory.
     */
    public function create()
    {
        return view('fee_categories.create');
    }

    /**
     * Store a newly created FeeCategory in storage.
     */
    public function store(CreateFeeCategoryRequest $request)
    {
        $input = $request->all();

        $feeCategory = $this->feeCategoryRepository->create($input);

        AuditTrail::log('Fee Category', 'CREATE', $feeCategory->category_id, null, $feeCategory->toArray());

        Flash::success('Fee Category saved successfully.');

        return redirect(route('feeCategories.index'));
    }

    /**
     * Display the specified FeeCategory.
     */
    public function show($id)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        return view('fee_categories.show')->with('feeCategory', $feeCategory);
    }

    /**
     * Show the form for editing the specified FeeCategory.
     */
    public function edit($id)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        return view('fee_categories.edit')->with('feeCategory', $feeCategory);
    }

    /**
     * Update the specified FeeCategory in storage.
     */
    public function update($id, UpdateFeeCategoryRequest $request)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        $oldData = $feeCategory->toArray();
        $feeCategory = $this->feeCategoryRepository->update($request->all(), $id);

        AuditTrail::log('Fee Category', 'UPDATE', $feeCategory->category_id, $oldData, $feeCategory->toArray());

        Flash::success('Fee Category updated successfully.');

        return redirect(route('feeCategories.index'));
    }

    /**
     * Generate auto code for fee category.
     */
    public function generateAutoCode(Request $request)
    {
        $name = $request->input('name');
        
        if (empty($name)) {
            return response()->json(['error' => 'Name is required to generate code'], 400);
        }

        // Generate base code from name (uppercase, no spaces, max 8 chars)
        $baseCode = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 8));
        
        // Check if base code exists, if so, append number
        $code = $baseCode;
        $counter = 1;
        
        while (FeeCategory::where('code', $code)->exists()) {
            $code = $baseCode . $counter;
            $counter++;
        }

        return response()->json(['code' => $code]);
    }

    /**
     * Remove the specified FeeCategory from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $feeCategory = $this->feeCategoryRepository->find($id);

        if (empty($feeCategory)) {
            Flash::error('Fee Category not found');

            return redirect(route('feeCategories.index'));
        }

        $oldData = $feeCategory->toArray();
        $this->feeCategoryRepository->delete($id);

        AuditTrail::log('Fee Category', 'DELETE', $id, $oldData, null);

        Flash::success('Fee Category deleted successfully.');

        return redirect(route('feeCategories.index'));
    }
}
