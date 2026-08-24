<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateIncomeCategoryRequest;
use App\Http\Requests\UpdateIncomeCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\IncomeCategoryRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class IncomeCategoryController extends AppBaseController
{
    /** @var IncomeCategoryRepository $incomeCategoryRepository*/
    private $incomeCategoryRepository;

    public function __construct(IncomeCategoryRepository $incomeCategoryRepo)
    {
        $this->incomeCategoryRepository = $incomeCategoryRepo;
        $this->middleware('can:finance.view')->only(['index', 'show']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the IncomeCategory.
     */
    public function index(Request $request)
    {
        $incomeCategories = $this->incomeCategoryRepository->paginate(10);

        return view('income_categories.index')
            ->with('incomeCategories', $incomeCategories);
    }

    /**
     * Show the form for creating a new IncomeCategory.
     */
    public function create()
    {
        return view('income_categories.create');
    }

    /**
     * Store a newly created IncomeCategory in storage.
     */
    public function store(CreateIncomeCategoryRequest $request)
    {
        $input = $request->all();

        $incomeCategory = $this->incomeCategoryRepository->create($input);

        AuditTrail::log('Income Category', 'CREATE', $incomeCategory->category_id, null, $incomeCategory->toArray());

        Flash::success('Income Category saved successfully.');

        return redirect(route('incomeCategories.index'));
    }

    /**
     * Display the specified IncomeCategory.
     */
    public function show($id)
    {
        $incomeCategory = $this->incomeCategoryRepository->find($id);

        if (empty($incomeCategory)) {
            Flash::error('Income Category not found');

            return redirect(route('incomeCategories.index'));
        }

        return view('income_categories.show')->with('incomeCategory', $incomeCategory);
    }

    /**
     * Show the form for editing the specified IncomeCategory.
     */
    public function edit($id)
    {
        $incomeCategory = $this->incomeCategoryRepository->find($id);

        if (empty($incomeCategory)) {
            Flash::error('Income Category not found');

            return redirect(route('incomeCategories.index'));
        }

        return view('income_categories.edit')->with('incomeCategory', $incomeCategory);
    }

    /**
     * Update the specified IncomeCategory in storage.
     */
    public function update($id, UpdateIncomeCategoryRequest $request)
    {
        $incomeCategory = $this->incomeCategoryRepository->find($id);

        if (empty($incomeCategory)) {
            Flash::error('Income Category not found');

            return redirect(route('incomeCategories.index'));
        }

        $oldData = $incomeCategory->toArray();
        $incomeCategory = $this->incomeCategoryRepository->update($request->all(), $id);

        AuditTrail::log('Income Category', 'UPDATE', $incomeCategory->category_id, $oldData, $incomeCategory->toArray());

        Flash::success('Income Category updated successfully.');

        return redirect(route('incomeCategories.index'));
    }

    /**
     * Remove the specified IncomeCategory from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $incomeCategory = $this->incomeCategoryRepository->find($id);

        if (empty($incomeCategory)) {
            Flash::error('Income Category not found');

            return redirect(route('incomeCategories.index'));
        }

        $oldData = $incomeCategory->toArray();
        $this->incomeCategoryRepository->delete($id);

        AuditTrail::log('Income Category', 'DELETE', $id, $oldData, null);

        Flash::success('Income Category deleted successfully.');

        return redirect(route('incomeCategories.index'));
    }
}
