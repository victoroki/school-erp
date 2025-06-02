<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpenseCategoriesRequest;
use App\Http\Requests\UpdateExpenseCategoriesRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ExpenseCategoriesRepository;
use Illuminate\Http\Request;
use Flash;

class ExpenseCategoriesController extends AppBaseController
{
    /** @var ExpenseCategoriesRepository $expenseCategoriesRepository*/
    private $expenseCategoriesRepository;

    public function __construct(ExpenseCategoriesRepository $expenseCategoriesRepo)
    {
        $this->expenseCategoriesRepository = $expenseCategoriesRepo;
    }

    /**
     * Display a listing of the ExpenseCategories.
     */
    public function index(Request $request)
    {
        $expenseCategories = $this->expenseCategoriesRepository->paginate(10);

        return view('expense_categories.index')
            ->with('expenseCategories', $expenseCategories);
    }

    /**
     * Show the form for creating a new ExpenseCategories.
     */
    public function create()
    {
        return view('expense_categories.create');
    }

    /**
     * Store a newly created ExpenseCategories in storage.
     */
    public function store(CreateExpenseCategoriesRequest $request)
    {
        $input = $request->all();

        $expenseCategories = $this->expenseCategoriesRepository->create($input);

        Flash::success('Expense Categories saved successfully.');

        return redirect(route('expenseCategories.index'));
    }

    /**
     * Display the specified ExpenseCategories.
     */
    public function show($id)
    {
        $expenseCategories = $this->expenseCategoriesRepository->find($id);

        if (empty($expenseCategories)) {
            Flash::error('Expense Categories not found');

            return redirect(route('expenseCategories.index'));
        }

        return view('expense_categories.show')->with('expenseCategories', $expenseCategories);
    }

    /**
     * Show the form for editing the specified ExpenseCategories.
     */
    public function edit($id)
    {
        $expenseCategories = $this->expenseCategoriesRepository->find($id);

        if (empty($expenseCategories)) {
            Flash::error('Expense Categories not found');

            return redirect(route('expenseCategories.index'));
        }

        return view('expense_categories.edit')->with('expenseCategories', $expenseCategories);
    }

    /**
     * Update the specified ExpenseCategories in storage.
     */
    public function update($id, UpdateExpenseCategoriesRequest $request)
    {
        $expenseCategories = $this->expenseCategoriesRepository->find($id);

        if (empty($expenseCategories)) {
            Flash::error('Expense Categories not found');

            return redirect(route('expenseCategories.index'));
        }

        $expenseCategories = $this->expenseCategoriesRepository->update($request->all(), $id);

        Flash::success('Expense Categories updated successfully.');

        return redirect(route('expenseCategories.index'));
    }

    /**
     * Remove the specified ExpenseCategories from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $expenseCategories = $this->expenseCategoriesRepository->find($id);

        if (empty($expenseCategories)) {
            Flash::error('Expense Categories not found');

            return redirect(route('expenseCategories.index'));
        }

        $this->expenseCategoriesRepository->delete($id);

        Flash::success('Expense Categories deleted successfully.');

        return redirect(route('expenseCategories.index'));
    }
}
