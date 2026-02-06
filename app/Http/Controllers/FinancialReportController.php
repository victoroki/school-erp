<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use App\Models\Income;
use App\Models\FeePayment;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinancialReportController extends AppBaseController
{
    public function index()
    {
        return view('financial_reports.index');
    }

    public function cashflow(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $income = Income::with('category')
            ->whereBetween('income_date', [$startDate, $endDate])
            ->where('status', 'active')
            ->get();
            
        $fees = FeePayment::whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        $expenses = Expenses::with('category')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'approved', 'approved'])
            ->get();

        return view('financial_reports.cashflow', compact('income', 'fees', 'expenses', 'startDate', 'endDate'));
    }

    public function pAndL(Request $request)
    {
        $activeYear = FinancialYear::where('status', 'open')->first() ?: FinancialYear::latest()->first();
        
        $startDate = $request->get('start_date', $activeYear ? $activeYear->start_date->toDateString() : Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', $activeYear ? $activeYear->end_date->toDateString() : Carbon::now()->endOfYear()->toDateString());

        $totalIncome = Income::whereBetween('income_date', [$startDate, $endDate])->where('status', 'active')->sum('amount')
                     + FeePayment::whereBetween('payment_date', [$startDate, $endDate])->sum('amount');
        
        $expenseBreakdown = Expenses::with('category')
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->whereIn('status', ['paid', 'approved'])
            ->select('category_id', \DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->get();

        $totalExpenses = $expenseBreakdown->sum('total');

        return view('financial_reports.p_and_l', compact('totalIncome', 'expenseBreakdown', 'totalExpenses', 'startDate', 'endDate'));
    }
}
