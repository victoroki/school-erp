<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pin the money scale of non-fee core tables at 2 decimals.
 *
 * The original create migrations declared `decimal('amount', 10)` and
 * `decimal('current_balance', 12)` — no scale argument, which MySQL treats
 * as decimal(N,0): whole shillings, cents discarded. The live database was
 * hand-altered to (10,2)/(12,2) outside the migration history, so a fresh
 * install built purely from these files would truncate cents while the
 * running system keeps them. The fee side already converged its own table in
 * 2026_09_21_000001_add_reversal_state_to_fee_payments; this migration does
 * the same for the remaining money tables.
 *
 * Every ALTER only ever widens a column whose scale is below 2, so no existing
 * value can be truncated and already-correct installations are left alone.
 * Nullability and defaults are read from information_schema and copied into
 * the rebuilt definition — a bare ALTER ... MODIFY would silently drop them.
 */
return new class extends Migration
{
    /** table => columns that hold money. */
    private const COLUMNS = [
        'expenses'          => ['amount'],
        'income'            => ['amount'],
        'bank_accounts'     => ['opening_balance', 'current_balance'],
        'bank_transactions' => ['amount'],
        // Renamed from `payroll` by the HR revamp; the legacy money columns
        // never received the two-decimal treatment the revamp's new columns got.
        'payroll_details'   => ['basic_salary', 'allowances', 'overtime', 'gross_salary', 'deductions', 'net_salary'],
        'staff_salary'      => ['basic_salary', 'allowances', 'deductions', 'net_salary'],
    ];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        foreach (self::COLUMNS as $table => $columns) {
            foreach ($columns as $column) {
                $this->widen($table, $column);
            }
        }
    }

    /**
     * Widen one column to DECIMAL(15,2) if it currently holds fewer than two
     * decimal places. Preserves the column's nullability and default.
     */
    private function widen(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $meta = DB::selectOne(
            'SELECT IS_NULLABLE, COLUMN_DEFAULT, NUMERIC_SCALE
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column],
        );

        if ($meta === null || (int) $meta->NUMERIC_SCALE >= 2) {
            return;
        }

        $nullClause = strtoupper($meta->IS_NULLABLE) === 'YES' ? 'NULL' : 'NOT NULL';
        $defaultClause = $meta->COLUMN_DEFAULT === null ? '' : ' DEFAULT ' . $meta->COLUMN_DEFAULT;

        DB::statement(
            "ALTER TABLE `{$table}` MODIFY `{$column}` DECIMAL(15,2) {$nullClause}{$defaultClause}"
        );
    }

    public function down(): void
    {
        // Deliberately a no-op: narrowing a money column back towards scale 0
        // is exactly the bug this migration exists to remove, and there is no
        // smaller representation to restore that would not lose cents.
    }
};