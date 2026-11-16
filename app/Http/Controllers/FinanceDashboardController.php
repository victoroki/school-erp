<?php

namespace App\Http\Controllers;

use App\Models\Expenses;
use App\Models\Income;
use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\FinancialYear;
use App\Services\FinancialMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinanceDashboardController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view');
    }

    public function index()
    {
        // Income Metrics — cash received this month (active Income + non-reversed fees)
        $thisMonthStart = Carbon::now()->startOfMonth()->toDateString();
        $thisMonthEnd = Carbon::now()->endOfMonth()->toDateString();

        $totalIncomeThisMonth = FinancialMetrics::incomeBaseTotalBetween($thisMonthStart, $thisMonthEnd);
        $totalFeesThisMonth = FinancialMetrics::feeIncomeTotalBetween($thisMonthStart, $thisMonthEnd);
        $combinedIncomeThisMonth = $totalIncomeThisMonth + $totalFeesThisMonth;

        $lastMonthStart = Carbon::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth()->toDateString();

        $totalIncomeLastMonth = FinancialMetrics::incomeTotalBetween($lastMonthStart, $lastMonthEnd);

        $incomeChange = $totalIncomeLastMonth > 0 ? (($combinedIncomeThisMonth - $totalIncomeLastMonth) / $totalIncomeLastMonth) * 100 : 0;

        // Expense Metrics — cash basis: only paid expenses moved money, so an
        // approved-but-unpaid expense must not overstate what the month cost.
        $totalExpensesThisMonth = FinancialMetrics::expensesTotalBetween(
            Carbon::now()->startOfMonth()->toDateString(),
            Carbon::now()->endOfMonth()->toDateString(),
            FinancialMetrics::BASIS_CASH
        );

        $totalExpensesLastMonth = FinancialMetrics::expensesTotalBetween(
            Carbon::now()->subMonth()->startOfMonth()->toDateString(),
            Carbon::now()->subMonth()->endOfMonth()->toDateString(),
            FinancialMetrics::BASIS_CASH
        );
        
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

        $openYear = FinancialYear::where('status', 'open')->first();
        $budgetUtilization = FinancialMetrics::budgetUtilization($openYear);
        
        // Recent Transactions
        $recentIncome = Income::with('category')->latest('income_date')->limit(10)->get();
        $recentExpenses = Expenses::with('category')->latest('expense_date')->limit(10)->get();

        // Data for charts (Income vs Expenses last 6 months)
        $chartData = $this->getChartData();

        return view('finance.dashboard', compact(
            'totalIncomeThisMonth', 'combinedIncomeThisMonth', 'incomeChange',
            'totalExpensesThisMonth', 'totalExpensesLastMonth', 'expenseChange',
            'netCashFlow', 'cashFlowPercentage',
            'totalBankBalance', 'lowBalanceAccounts',
            'pendingApprovalsCount', 'pendingApprovalsAmount',
            'recentIncome', 'recentExpenses',
            'chartData', 'budgetUtilization'
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

            $monthStart = $date->copy()->startOfMonth()->toDateString();
            $monthEnd = $date->copy()->endOfMonth()->toDateString();

            $monthIncome = FinancialMetrics::incomeTotalBetween($monthStart, $monthEnd);
            $monthExpense = FinancialMetrics::expensesTotalBetween($monthStart, $monthEnd, FinancialMetrics::BASIS_CASH);

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
