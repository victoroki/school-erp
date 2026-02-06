<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use App\Models\Income;
use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\FeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends AppBaseController
{
    public function index()
    {
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        $lastMonth = Carbon::now()->subMonth()->month;
        $lastMonthYear = Carbon::now()->subMonth()->year;

        // Income Metrics
        $totalIncomeThisMonth = Income::whereMonth('income_date', $thisMonth)
            ->whereYear('income_date', $thisYear)
            ->where('status', 'active')
            ->sum('amount');
        
        // Include Fee Payments as income
        $totalFeesThisMonth = FeePayment::whereMonth('payment_date', $thisMonth)
            ->whereYear('payment_date', $thisYear)
            ->sum('amount');
        
        $combinedIncomeThisMonth = $totalIncomeThisMonth + $totalFeesThisMonth;

        $totalIncomeLastMonth = Income::whereMonth('income_date', $lastMonth)
            ->whereYear('income_date', $lastMonthYear)
            ->where('status', 'active')
            ->sum('amount') + FeePayment::whereMonth('payment_date', $lastMonth)
            ->whereYear('payment_date', $lastMonthYear)
            ->sum('amount');
        
        $incomeChange = $totalIncomeLastMonth > 0 ? (($combinedIncomeThisMonth - $totalIncomeLastMonth) / $totalIncomeLastMonth) * 100 : 0;

        // Expense Metrics
        $totalExpensesThisMonth = Expenses::whereMonth('expense_date', $thisMonth)
            ->whereYear('expense_date', $thisYear)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');
        
        $totalExpensesLastMonth = Expenses::whereMonth('expense_date', $lastMonth)
            ->whereYear('expense_date', $lastMonthYear)
            ->whereIn('status', ['approved', 'paid'])
            ->sum('amount');
        
        $expenseChange = $totalExpensesLastMonth > 0 ? (($totalExpensesThisMonth - $totalExpensesLastMonth) / $totalExpensesLastMonth) * 100 : 0;

        // Net Cash Flow
        $netCashFlow = $combinedIncomeThisMonth - $totalExpensesThisMonth;
        $cashFlowPercentage = $combinedIncomeThisMonth > 0 ? ($netCashFlow / $combinedIncomeThisMonth) * 100 : 0;

        // Bank Balance
        $totalBankBalance = BankAccount::sum('current_balance');
        $lowBalanceAccounts = BankAccount::whereColumn('current_balance', '<', 'minimum_balance')->count();

        // Pending Approvals
        $pendingApprovalsCount = Expenses::where('status', 'pending')->count();
        $pendingApprovalsAmount = Expenses::where('status', 'pending')->sum('amount');

        // Budget Status
        $budgets = Budget::whereHas('financialYear', function($q) {
            $q->where('status', 'open');
        })->get();
        
        // This is simplified. Proper budget vs actual would need complex mapping.
        $budgetUtilization = 0; // Calculated in view or service
        
        // Recent Transactions
        $recentIncome = Income::with('category')->latest('income_date')->limit(10)->get();
        $recentExpenses = Expenses::with('category')->latest('expense_date')->limit(10)->get();

        // Data for charts (Income vs Expenses last 6 months)
        $chartData = $this->getChartData();

        return view('finance.dashboard', compact(
            'combinedIncomeThisMonth', 'incomeChange',
            'totalExpensesThisMonth', 'expenseChange',
            'netCashFlow', 'cashFlowPercentage',
            'totalBankBalance', 'lowBalanceAccounts',
            'pendingApprovalsCount', 'pendingApprovalsAmount',
            'recentIncome', 'recentExpenses',
            'chartData'
        ));
    }

    private function getChartData()
    {
        $months = [];
        $income = [];
        $expenses = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $months[] = $date->format('M');
            
            $monthIncome = Income::whereMonth('income_date', $date->month)
                ->whereYear('income_date', $date->year)
                ->where('status', 'active')
                ->sum('amount') + FeePayment::whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');
            
            $monthExpense = Expenses::whereMonth('expense_date', $date->month)
                ->whereYear('expense_date', $date->year)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('amount');
            
            $income[] = $monthIncome;
            $expenses[] = $monthExpense;
        }

        return [
            'labels' => $months,
            'income' => $income,
            'expenses' => $expenses
        ];
    }
}
