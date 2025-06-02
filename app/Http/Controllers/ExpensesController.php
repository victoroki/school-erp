<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpensesRequest;
use App\Http\Requests\UpdateExpensesRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ExpensesRepository;
use Illuminate\Http\Request;
use Flash;

class ExpensesController extends AppBaseController
{
    /** @var ExpensesRepository $expensesRepository*/
    private $expensesRepository;

    public function __construct(ExpensesRepository $expensesRepo)
    {
        $this->expensesRepository = $expensesRepo;
    }

    /**
     * Display a listing of the Expenses.
     */
    public function index(Request $request)
    {
        $expenses = $this->expensesRepository->paginate(10);

        return view('expenses.index')
            ->with('expenses', $expenses);
    }

    /**
     * Show the form for creating a new Expenses.
     */
    public function create()
    {
        return view('expenses.create');
    }

    /**
     * Store a newly created Expenses in storage.
     */
    public function store(CreateExpensesRequest $request)
    {
        $input = $request->all();

        $expenses = $this->expensesRepository->create($input);

        Flash::success('Expenses saved successfully.');

        return redirect(route('expenses.index'));
    }

    /**
     * Display the specified Expenses.
     */
    public function show($id)
    {
        $expenses = $this->expensesRepository->find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');

            return redirect(route('expenses.index'));
        }

        return view('expenses.show')->with('expenses', $expenses);
    }

    /**
     * Show the form for editing the specified Expenses.
     */
    public function edit($id)
    {
        $expenses = $this->expensesRepository->find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');

            return redirect(route('expenses.index'));
        }

        return view('expenses.edit')->with('expenses', $expenses);
    }

    /**
     * Update the specified Expenses in storage.
     */
    public function update($id, UpdateExpensesRequest $request)
    {
        $expenses = $this->expensesRepository->find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');

            return redirect(route('expenses.index'));
        }

        $expenses = $this->expensesRepository->update($request->all(), $id);

        Flash::success('Expenses updated successfully.');

        return redirect(route('expenses.index'));
    }

    /**
     * Remove the specified Expenses from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $expenses = $this->expensesRepository->find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');

            return redirect(route('expenses.index'));
        }

        $this->expensesRepository->delete($id);

        Flash::success('Expenses deleted successfully.');

        return redirect(route('expenses.index'));
    }
}
