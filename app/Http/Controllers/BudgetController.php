<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\FinancialYear;
use App\Models\ExpenseCategory;
use App\Models\IncomeCategory;
use App\Models\Expenses;
use App\Models\Income;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Flash;

class BudgetController extends AppBaseController
{
    public function index()
    {
        $financialYears = FinancialYear::orderBy('start_date', 'desc')->get();
        $activeYear = FinancialYear::where('status', 'open')->first();
        
        $budgets = Budget::with('financialYear')
            ->when($activeYear, function($q) use ($activeYear) {
                $q->where('financial_year_id', $activeYear->id);
            })
            ->get();

        return view('budgets.index', compact('budgets', 'financialYears', 'activeYear'));
    }

    public function create()
    {
        $financialYears = FinancialYear::where('status', 'open')->pluck('name', 'id');
        $expenseCategories = ExpenseCategory::pluck('name', 'category_id');
        $incomeCategories = IncomeCategory::pluck('name', 'category_id');

        return view('budgets.create', compact('financialYears', 'expenseCategories', 'incomeCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'financial_year_id' => 'required',
            'category_type' => 'required|in:income,expense',
            'category_id' => 'required',
            'amount' => 'required|numeric|min:0',
        ]);

        Budget::create($request->all() + ['created_by' => auth()->id()]);

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

        $financialYears = FinancialYear::where('status', 'open')->pluck('name', 'id');
        $expenseCategories = ExpenseCategory::pluck('name', 'category_id');
        $incomeCategories = IncomeCategory::pluck('name', 'category_id');

        return view('budgets.edit', compact('budget', 'financialYears', 'expenseCategories', 'incomeCategories'));
    }

    public function update($id, Request $request)
    {
        $budget = Budget::find($id);

        if (empty($budget)) {
            Flash::error('Budget not found');
            return redirect(route('budgets.index'));
        }

        $request->validate([
            'financial_year_id' => 'required',
            'category_type' => 'required|in:income,expense',
            'category_id' => 'required',
            'amount' => 'required|numeric|min:0',
        ]);

        $budget->update($request->all());

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

        $budget->delete();

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
            $actual = 0;
            if ($budget->category_type == 'expense') {
                $actual = Expenses::where('category_id', $budget->category_id)
                    ->whereBetween('expense_date', [$activeYear->start_date, $activeYear->end_date])
                    ->whereIn('status', ['paid', 'approved'])
                    ->sum('amount');
            } else {
                // Simplified: assuming fees are categorized or just total income
                $actual = Income::where('category_id', $budget->category_id)
                    ->whereBetween('income_date', [$activeYear->start_date, $activeYear->end_date])
                    ->where('status', 'active')
                    ->sum('amount');
                
                // If it's a specific "Fees" category, we might add FeePayment sum
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
