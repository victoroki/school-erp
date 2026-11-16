<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ExpenseCategoryRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class ExpenseCategoryController extends AppBaseController
{
    /** @var ExpenseCategoryRepository $expenseCategoryRepository*/
    private $expenseCategoryRepository;

    public function __construct(ExpenseCategoryRepository $expenseCategoryRepo)
    {
        $this->expenseCategoryRepository = $expenseCategoryRepo;
        $this->middleware('can:finance.view')->only(['index', 'show']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the ExpenseCategory.
     */
    public function index(Request $request)
    {
        $expenseCategories = $this->expenseCategoryRepository->paginate(10);

        return view('expense_categories.index')
            ->with('expenseCategories', $expenseCategories);
    }

    /**
     * Show the form for creating a new ExpenseCategory.
     */
    public function create()
    {
        return view('expense_categories.create');
    }

    /**
     * Store a newly created ExpenseCategory in storage.
     */
    public function store(CreateExpenseCategoryRequest $request)
    {
        $input = $request->all();

        $expenseCategory = $this->expenseCategoryRepository->create($input);

        AuditTrail::log('Expense Category', 'CREATE', $expenseCategory->category_id, null, $expenseCategory->toArray());

        Flash::success('Expense Category saved successfully.');

        return redirect(route('expenseCategories.index'));
    }

    /**
     * Display the specified ExpenseCategory.
     */
    public function show($id)
    {
        $expenseCategory = $this->expenseCategoryRepository->find($id);

        if (empty($expenseCategory)) {
            Flash::error('Expense Category not found');

            return redirect(route('expenseCategories.index'));
        }

        return view('expense_categories.show')->with('expenseCategory', $expenseCategory);
    }

    /**
     * Show the form for editing the specified ExpenseCategory.
     */
    public function edit($id)
    {
        $expenseCategory = $this->expenseCategoryRepository->find($id);

        if (empty($expenseCategory)) {
            Flash::error('Expense Category not found');

            return redirect(route('expenseCategories.index'));
        }

        return view('expense_categories.edit')->with('expenseCategory', $expenseCategory);
    }

    /**
     * Update the specified ExpenseCategory in storage.
     */
    public function update($id, UpdateExpenseCategoryRequest $request)
    {
        $expenseCategory = $this->expenseCategoryRepository->find($id);

        if (empty($expenseCategory)) {
            Flash::error('Expense Category not found');

            return redirect(route('expenseCategories.index'));
        }

        $oldData = $expenseCategory->toArray();
        $expenseCategory = $this->expenseCategoryRepository->update($request->all(), $id);

        AuditTrail::log('Expense Category', 'UPDATE', $expenseCategory->category_id, $oldData, $expenseCategory->toArray());

        Flash::success('Expense Category updated successfully.');

        return redirect(route('expenseCategories.index'));
    }

    /**
     * Remove the specified ExpenseCategory from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $expenseCategory = $this->expenseCategoryRepository->find($id);

        if (empty($expenseCategory)) {
            Flash::error('Expense Category not found');

            return redirect(route('expenseCategories.index'));
        }

        $oldData = $expenseCategory->toArray();
        $this->expenseCategoryRepository->delete($id);

        AuditTrail::log('Expense Category', 'DELETE', $id, $oldData, null);

        Flash::success('Expense Category deleted successfully.');

        return redirect(route('expenseCategories.index'));
    }
}
