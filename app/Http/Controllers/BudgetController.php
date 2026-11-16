<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\FinancialYear;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\AuditTrail;
use App\Http\Requests\CreateBudgetRequest;
use App\Http\Requests\UpdateBudgetRequest;
use App\Services\FinancialMetrics;
use Illuminate\Http\Request;
use Flash;

class BudgetController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show', 'vsActual']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $activeYear = FinancialYear::where('status', 'open')->first();
        $selectedYear = $request->filled('financial_year_id') ? $request->input('financial_year_id') : ($activeYear->id ?? null);

        $budgets = Budget::with('financialYear')
            ->when($selectedYear, function ($q) use ($selectedYear) {
                $q->where('financial_year_id', $selectedYear);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $financialYearOptions = FinancialYear::orderBy('start_date', 'desc')->pluck('name', 'id');

        return view('budgets.index', compact('budgets', 'financialYearOptions', 'selectedYear', 'activeYear'));
    }

    public function create()
    {
        $financialYears = FinancialYear::where('status', 'open')->pluck('name', 'id');
        $expenseCategories = ExpenseCategory::pluck('name', 'category_id');
        $incomeCategories = IncomeCategory::pluck('name', 'category_id');

        return view('budgets.create', compact('financialYears', 'expenseCategories', 'incomeCategories'));
    }

    public function store(CreateBudgetRequest $request)
    {
        $input = $request->validated() + ['created_by' => auth()->id()];
        $input['include_fees'] = $request->boolean('include_fees');

        $budget = Budget::create($input);

        AuditTrail::log('Budget', 'CREATE', $budget->id, null, $budget->toArray());

        Flash::success('Budget entry saved successfully.');
        return redirect(route('budgets.index'));
    }

    public function show($id)
    {
        $budget = Budget::with(['financialYear'])->find($id);

        if (empty($budget)) {
            Flash::error('Budget not found');
            return redirect(route('budgets.index'));
        }

        return view('budgets.show')->with('budget', $budget);
    }

    public function edit($id)
    {
        $budget = Budget::find($id);

        if (empty($budget)) {
            Flash::error('Budget not found');
            return redirect(route('budgets.index'));
        }

        $openYearIds = FinancialYear::where('status', 'open')->pluck('id');
        $financialYears = FinancialYear::orderBy('start_date', 'desc')
            ->pluck('name', 'id')
            ->mapWithKeys(function ($name, $id) use ($openYearIds) {
                return $openYearIds->contains($id)
                    ? [$id => $name]
                    : [$id => $name . ' (closed)'];
            });

        // A budget whose year was closed must still render its stored year,
        // otherwise Form::model silently falls back to the first option and
        // saving reassigns the budget to a different financial year.
        if (!$financialYears->has($budget->financial_year_id)) {
            $storedYear = FinancialYear::find($budget->financial_year_id);
            if ($storedYear) {
                $financialYears->prepend($storedYear->name . ' (closed)', $storedYear->id);
            }
        }

        $expenseCategories = ExpenseCategory::pluck('name', 'category_id');
        $incomeCategories = IncomeCategory::pluck('name', 'category_id');

        return view('budgets.edit', compact('budget', 'financialYears', 'expenseCategories', 'incomeCategories'));
    }

    public function update($id, UpdateBudgetRequest $request)
    {
        $budget = Budget::find($id);

        if (empty($budget)) {
            Flash::error('Budget not found');
            return redirect(route('budgets.index'));
        }

        $oldData = $budget->toArray();
        $budget->update($request->validated() + ['include_fees' => $request->boolean('include_fees')]);

        AuditTrail::log('Budget', 'UPDATE', $budget->id, $oldData, $budget->toArray());

        Flash::success('Budget updated successfully.');
        return redirect(route('budgets.index'));
    }

    public function destroy($id)
    {
        $budget = Budget::find($id);

        if (empty($budget)) {
            Flash::error('Budget not found');
            return redirect(route('budgets.index'));
        }

        $oldData = $budget->toArray();
        $budget->delete();

        AuditTrail::log('Budget', 'DELETE', $id, $oldData, null);

        Flash::success('Budget deleted successfully.');
        return redirect(route('budgets.index'));
    }

    public function vsActual(Request $request)
    {
        $activeYear = FinancialYear::where('status', 'open')->first();
        if (!$activeYear) {
            Flash::warning('Please open a financial year first.');
            return redirect(route('financial-years.index'));
        }

        $budgets = Budget::where('financial_year_id', $activeYear->id)->get();
        
        $comparison = $budgets->map(function($budget) use ($activeYear) {
            if ($budget->category_type == 'expense') {
                // Accrual basis (approved + paid): the actual spend this year
                // is what was committed, not only what has left the bank.
                $actual = FinancialMetrics::categorySpendTotal(
                    (int) $budget->category_id,
                    $activeYear->start_date,
                    $activeYear->end_date,
                    FinancialMetrics::BASIS_COMMITTED
                );
            } else {
                // Income actuals count fee payments only when the budget row
                // opts in — otherwise a "Fees" budget reads short because fee
                // payments carry no income category (audit F-2).
                $actual = FinancialMetrics::incomeForCategory(
                    (int) $budget->category_id,
                    $activeYear->start_date,
                    $activeYear->end_date,
                    (bool) $budget->include_fees
                );
            }

            return (object) [
                'category' => $budget->category ? $budget->category->name : 'Unknown',
                'type' => $budget->category_type,
                'budgeted' => $budget->amount,
                'actual' => $actual,
                'variance' => $budget->amount - $actual,
                'percentage' => $budget->amount > 0 ? ($actual / $budget->amount) * 100 : 0,
                'threshold' => $budget->alert_threshold
            ];
        });

        return view('budgets.vs_actual', compact('comparison', 'activeYear'));
    }
}
