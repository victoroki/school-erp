<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Expenses;
use App\Models\FeePayment;
use App\Models\FinancialYear;
use App\Models\Income;
use Illuminate\Support\Collection;

/**
 * The finance module's single source for money figures (audit I-3 / F-3).
 *
 * Decided conventions (audit E-9):
 *   - Cash basis   (cash-flow report, dashboard metrics): expenses = PAID only.
 *   - Accrual basis (Profit & Loss, budget vs actual):     expenses = paid OR
 *     approved, because an approved-but-unpaid expense is a committed cost.
 *   - Income is identical everywhere: active Income rows + non-reversed fee
 *     payments.
 *
 * Every screen that adds money should ask this service rather than re-deriving
 * its own status lists, so identical numbers never disagree across screens.
 */
class FinancialMetrics
{
    public const BASIS_CASH = 'cash';
    public const BASIS_COMMITTED = 'committed';

    public static function statusesFor(string $basis): array
    {
        return $basis === self::BASIS_COMMITTED ? ['paid', 'approved'] : ['paid'];
    }

    public static function incomeBetween($start, $end): Collection
    {
        return Income::whereBetween('income_date', [$start, $end])
            ->where('status', 'active')
            ->get();
    }

    public static function feeIncomeBetween($start, $end): Collection
    {
        return FeePayment::notReversed()
            ->whereBetween('payment_date', [$start, $end])
            ->get();
    }

    public static function incomeTotalBetween($start, $end): float
    {
        return (float) self::incomeBetween($start, $end)->sum('amount')
             + (float) self::feeIncomeBetween($start, $end)->sum('amount');
    }

    /**
     * Non-fee income only (Income rows that are active). Used wherever the
     * UI keeps "other income" and "fee income" as separate figures.
     */
    public static function incomeBaseTotalBetween($start, $end): float
    {
        return (float) self::incomeBetween($start, $end)->sum('amount');
    }

    public static function feeIncomeTotalBetween($start, $end): float
    {
        return (float) self::feeIncomeBetween($start, $end)->sum('amount');
    }

    /**
     * Income for an income budget line. Fee payments only count when the
     * budget opts in via budgets.include_fees — otherwise a "Fees" income
     * budget would always read short (audit F-2).
     */
    public static function incomeForCategory(int $categoryId, $start, $end, bool $includeFees = false): float
    {
        $total = (float) Income::where('category_id', $categoryId)
            ->whereBetween('income_date', [$start, $end])
            ->where('status', 'active')
            ->sum('amount');

        return $includeFees
            ? $total + self::feeIncomeTotalBetween($start, $end)
            : $total;
    }

    public static function expensesBetween($start, $end, string $basis = self::BASIS_CASH): Collection
    {
        return Expenses::whereBetween('expense_date', [$start, $end])
            ->whereIn('status', self::statusesFor($basis))
            ->get();
    }

    public static function expensesTotalBetween($start, $end, string $basis = self::BASIS_CASH): float
    {
        return (float) self::expensesBetween($start, $end, $basis)->sum('amount');
    }

    public static function categorySpendTotal(int $categoryId, $start, $end, string $basis = self::BASIS_CASH): float
    {
        return (float) Expenses::where('category_id', $categoryId)
            ->whereBetween('expense_date', [$start, $end])
            ->whereIn('status', self::statusesFor($basis))
            ->sum('amount');
    }

    /**
     * Per-category expense rows shaped for the P&L statement view
     * (->category, ->total).
     */
    public static function expenseBreakdownByCategory($start, $end, string $basis = self::BASIS_COMMITTED): Collection
    {
        return Expenses::with('category')
            ->whereBetween('expense_date', [$start, $end])
            ->whereIn('status', self::statusesFor($basis))
            ->get()
            ->groupBy('category_id')
            ->map(function (Collection $items) {
                return (object) [
                    'category' => $items->first()->category,
                    'total' => round($items->sum('amount'), 2),
                ];
            })
            ->values();
    }

    /**
     * Spend vs budget for the open financial year, used by the dashboard's
     * budget status card. Expense budgets compare against cash paid this
     * year; income budgets against income received (fees only when the
     * budget opts in).
     */
    public static function budgetUtilization(?FinancialYear $year): array
    {
        if (!$year) {
            return ['used' => 0.0, 'budget' => 0.0, 'percent' => 0.0, 'count' => 0];
        }

        $budgets = Budget::where('financial_year_id', $year->id)->get();

        $used = 0.0;
        $budget = 0.0;

        foreach ($budgets as $b) {
            $budget += (float) $b->amount;
            if ($b->category_type === 'expense') {
                $used += self::categorySpendTotal((int) $b->category_id, $year->start_date, $year->end_date, self::BASIS_CASH);
            } else {
                $used += self::incomeForCategory((int) $b->category_id, $year->start_date, $year->end_date, (bool) $b->include_fees);
            }
        }

        return [
            'used' => round($used, 2),
            'budget' => round($budget, 2),
            'percent' => $budget > 0 ? round(($used / $budget) * 100, 1) : 0.0,
            'count' => $budgets->count(),
        ];
    }
}