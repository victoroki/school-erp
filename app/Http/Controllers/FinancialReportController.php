<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Expenses;
use App\Models\FeePayment;
use App\Models\FinancialYear;
use App\Models\PettyCashLog;
use App\Models\Refund;
use App\Models\SchoolClass;
use App\Models\StudentFeeAssignment;
use App\Services\FinancialMetrics;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view');
        // The cash-flow report used to require 'finance.export', a permission
        // that is not defined anywhere in the RBAC seeders — so the report was
        // a permanent 403 for every role including Owner and Super Admin.
        // Viewing it needs the same permission as every other financial report
        // (index, P&L) until a real export permission is introduced.
        $this->middleware('can:finance.view')->only(['cashflow']);
    }

    public function index()
    {
        return view('financial_reports.index');
    }

    public function cashflow(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        // A cash-flow statement counts cash that actually moved: paid expenses
        // only (the old query used 'paid','approved','approved' — a duplicate
        // literal that also counted approved-but-unpaid expenses as money out).
        $income = FinancialMetrics::incomeBetween($startDate, $endDate)->load('category');
        $fees = FinancialMetrics::feeIncomeBetween($startDate, $endDate);
        $expenses = FinancialMetrics::expensesBetween($startDate, $endDate, FinancialMetrics::BASIS_CASH)->load('category');

        return view('financial_reports.cashflow', compact('income', 'fees', 'expenses', 'startDate', 'endDate'));
    }

    public function cashflowPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $income = FinancialMetrics::incomeBetween($startDate, $endDate)->load('category');
        $fees = FinancialMetrics::feeIncomeBetween($startDate, $endDate);
        $expenses = FinancialMetrics::expensesBetween($startDate, $endDate, FinancialMetrics::BASIS_CASH)->load('category');

        $pdf = Pdf::loadView(
            'financial_reports.exports.cashflow_pdf',
            compact('income', 'fees', 'expenses', 'startDate', 'endDate')
        );

        return $pdf->download('cashflow-statement.pdf');
    }

    public function pAndL(Request $request)
    {
        $activeYear = FinancialYear::where('status', 'open')->first() ?: FinancialYear::latest()->first();

        $startDate = $request->get('start_date', $activeYear ? $activeYear->start_date->toDateString() : Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', $activeYear ? $activeYear->end_date->toDateString() : Carbon::now()->endOfYear()->toDateString());

        $totalIncome = FinancialMetrics::incomeTotalBetween($startDate, $endDate);

        // P&L uses the accrual convention: committed costs include approved
        // but unpaid expenses, so the statement shows what the period owes,
        // not just what already left the bank.
        $expenseBreakdown = FinancialMetrics::expenseBreakdownByCategory(
            $startDate,
            $endDate,
            FinancialMetrics::BASIS_COMMITTED
        );

        $totalExpenses = $expenseBreakdown->sum('total');

        return view('financial_reports.p_and_l', compact('totalIncome', 'expenseBreakdown', 'totalExpenses', 'startDate', 'endDate'));
    }

    public function pAndLPdf(Request $request)
    {
        $activeYear = FinancialYear::where('status', 'open')->first() ?: FinancialYear::latest()->first();

        $startDate = $request->get('start_date', $activeYear ? $activeYear->start_date->toDateString() : Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', $activeYear ? $activeYear->end_date->toDateString() : Carbon::now()->endOfYear()->toDateString());

        $totalIncome = FinancialMetrics::incomeTotalBetween($startDate, $endDate);

        $expenseBreakdown = FinancialMetrics::expenseBreakdownByCategory(
            $startDate,
            $endDate,
            FinancialMetrics::BASIS_COMMITTED
        );

        $totalExpenses = $expenseBreakdown->sum('total');

        $pdf = Pdf::loadView(
            'financial_reports.exports.p_and_l_pdf',
            compact('totalIncome', 'expenseBreakdown', 'totalExpenses', 'startDate', 'endDate')
        );

        return $pdf->download('profit-loss-statement.pdf');
    }

    public function balanceSheet(Request $request)
    {
        // Default: start of current financial year to today
        $activeYear = FinancialYear::where('status', 'open')->first() ?: FinancialYear::latest()->first();

        $startDate = $request->get('start_date', $activeYear
            ? $activeYear->start_date->toDateString()
            : Carbon::now()->startOfYear()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());

        // ── ASSETS ──────────────────────────────────────────────────────────

        // 1. Bank account balances (current snapshot — not date-filtered)
        $bankAccounts = BankAccount::where('status', 'active')
            ->select('account_name', 'bank_name', 'current_balance')
            ->get();
        $totalBankBalance = (float) $bankAccounts->sum('current_balance');

        // 2. Student fee receivables: what students still owe
        //    outstanding = SUM(final_amount) - SUM(paid_amount) for active assignments
        $receivablesRow = StudentFeeAssignment::where('status', 'active')
            ->selectRaw('COALESCE(SUM(final_amount), 0) as total_billed, COALESCE(SUM(paid_amount), 0) as total_paid')
            ->first();

        $totalBilled = (float) ($receivablesRow->total_billed ?? 0);
        $totalPaidFees = (float) ($receivablesRow->total_paid ?? 0);
        $feeReceivables = max(0, $totalBilled - $totalPaidFees);

        // 3. Petty cash on hand: credit (top-ups) minus debit (disbursements)
        $pettyCash = (float) PettyCashLog::selectRaw(
            "COALESCE(SUM(CASE WHEN type='credit' THEN amount ELSE -amount END), 0) as balance"
        )->value('balance');
        $pettyCash = max(0, $pettyCash);

        $totalAssets = $totalBankBalance + $feeReceivables + $pettyCash;

        // ── LIABILITIES ──────────────────────────────────────────────────────

        // 1. Pending / approved expenses not yet paid out
        $pendingExpenses = (float) Expenses::whereIn('status', ['pending', 'approved'])
            ->sum('amount');

        // 2. Approved refunds not yet completed (money owed back to students)
        $pendingRefunds = (float) Refund::where('status', 'approved')
            ->sum('amount');

        $totalLiabilities = $pendingExpenses + $pendingRefunds;

        // ── EQUITY ───────────────────────────────────────────────────────────
        $netAssets = $totalAssets - $totalLiabilities;

        return view('financial_reports.balance_sheet', compact(
            'bankAccounts',
            'totalBankBalance',
            'feeReceivables',
            'totalBilled',
            'totalPaidFees',
            'pettyCash',
            'totalAssets',
            'pendingExpenses',
            'pendingRefunds',
            'totalLiabilities',
            'netAssets',
            'startDate',
            'endDate'
        ));
    }

    public function feeCollectionTrends(Request $request)
    {
        // Build last 12 months of data
        $months = collect();
        for ($i = 11; $i >= 0; $i--) {
            $months->push(Carbon::now()->startOfMonth()->subMonths($i));
        }

        $monthlyData = $months->map(function (Carbon $monthStart) {
            $monthEnd = $monthStart->copy()->endOfMonth();

            // Fees collected this month (non-reversed payments)
            $collected = (float) FeePayment::notReversed()
                ->whereBetween('payment_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
                ->sum('amount');

            // Expected: sum of final_amount for assignments whose assigned_date
            // falls in or before this month (i.e., charges that were due by month end)
            $expected = (float) StudentFeeAssignment::where('status', 'active')
                ->whereDate('assigned_date', '<=', $monthEnd->toDateString())
                ->sum('final_amount');

            $outstanding = max(0, $expected - $collected);
            $rate = $expected > 0 ? round(($collected / $expected) * 100, 1) : 0;

            return [
                'month'       => $monthStart->format('M Y'),
                'month_key'   => $monthStart->format('Y-m'),
                'expected'    => $expected,
                'collected'   => $collected,
                'outstanding' => $outstanding,
                'rate'        => $rate,
            ];
        });

        // Top paying classes — join fee_payments → student_fee_assignments
        //   → students → student_class_enrollments → class_sections → classes
        $topClasses = DB::table('fee_payments as fp')
            ->join('student_fee_assignments as sfa', 'sfa.id', '=', 'fp.student_fee_assignment_id')
            ->join('students as s', 's.student_id', '=', 'sfa.student_id')
            ->join('student_class_enrollments as sce', function ($join) {
                $join->on('sce.student_id', '=', 's.student_id')
                     ->where('sce.is_current', '=', 1);
            })
            ->join('class_sections as cs', 'cs.class_section_id', '=', 'sce.class_section_id')
            ->join('classes as c', 'c.class_id', '=', 'cs.class_id')
            ->whereNull('fp.reversed_at')
            ->groupBy('c.class_id', 'c.name')
            ->selectRaw('c.class_id, c.name as class_name, COALESCE(SUM(fp.amount), 0) as total_collected')
            ->orderByDesc('total_collected')
            ->limit(5)
            ->get();

        // Summary stats
        $totalCollected = $monthlyData->sum('collected');
        $totalExpected  = $monthlyData->last()['expected'] ?? 0; // current month expected
        $overallRate    = $totalExpected > 0
            ? round(($monthlyData->last()['collected'] / $totalExpected) * 100, 1)
            : 0;

        // Chart data
        $chartLabels   = $monthlyData->pluck('month')->toJson();
        $chartCollected = $monthlyData->pluck('collected')->toJson();
        $chartExpected  = $monthlyData->pluck('expected')->toJson();

        return view('financial_reports.fee_collection_trends', compact(
            'monthlyData',
            'topClasses',
            'totalCollected',
            'totalExpected',
            'overallRate',
            'chartLabels',
            'chartCollected',
            'chartExpected'
        ));
    }
}
