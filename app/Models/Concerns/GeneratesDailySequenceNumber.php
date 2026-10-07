<?php

namespace App\Models\Concerns;

use Illuminate\Database\QueryException;

/**
 * Collision-free human-readable document numbers (PO-20260929-001,
 * REQ-20260929-001).
 *
 * The number is derived from a MAX+1 scan of today's rows, so it is
 * sequential rather than random — the audit replaced rand(), which could hand
 * two documents raised in the same second the same number.
 *
 * A MAX+1 scan is still a read-then-write, so two clerks saving at the same
 * instant can compute the same candidate. The UNIQUE index on the column is
 * the actual guarantee; this trait exists so a collision is *retried* with a
 * fresh number rather than surfacing as a failed save to the user.
 */
trait GeneratesDailySequenceNumber
{
    /**
     * How many candidates to try before giving up and letting the unique
     * index raise. Five is far beyond any realistic contention.
     */
    public const SEQUENCE_ATTEMPTS = 5;

    /**
     * The column holding the number, e.g. 'po_number'.
     */
    abstract protected static function sequenceColumn(): string;

    /**
     * The literal prefix before the date, e.g. 'PO-'.
     */
    abstract protected static function sequencePrefix(): string;

    /**
     * The next unused number for today: PREFIX-YYYYMMDD-NNN.
     *
     * Named distinctly from generateNumber() so the model keeps its own
     * public entry point (a class method would otherwise shadow this one).
     */
    public static function nextSequenceNumber(): string
    {
        $prefix = static::sequencePrefix() . date('Ymd') . '-';
        $column = static::sequenceColumn();

        $max = (int) static::query()
            ->where($column, 'like', $prefix . '%')
            ->max(\Illuminate\Support\Facades\DB::raw(
                'CAST(SUBSTRING(' . $column . ', ' . (strlen($prefix) + 1) . ') AS UNSIGNED)'
            ));

        return $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Run $create, retrying with a freshly generated number if the database
     * refused a duplicate.
     *
     * @param  callable(): mixed  $create  must insert one row per attempt
     * @return mixed the created row
     *
     * @throws QueryException when the attempts are exhausted, or on any other
     *                        database error
     */
    public static function createWithFreshNumber(callable $create)
    {
        for ($attempt = 1; ; $attempt++) {
            $number = static::nextSequenceNumber();

            try {
                return $create($number);
            } catch (QueryException $e) {
                if ($attempt >= static::SEQUENCE_ATTEMPTS || ! static::isSequenceCollision($e, $number)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Did this failure come from the unique index on the number column?
     *
     * Deliberately narrow. SQLSTATE 23000 covers *every* integrity
     * violation — foreign keys, NOT NULL, check constraints — so keying off
     * the SQLSTATE alone would silently retry a genuine data error five
     * times and then report a confusing failure. Two conditions must hold:
     *
     *  1. the driver reported a duplicate/unique violation (MySQL 1062,
     *     PostgreSQL 23505, SQL Server 2601/2627, or equivalent wording); and
     *  2. the violation is about the number we just tried, rather than some
     *     other unique index.
     *
     * Every one of those drivers quotes the offending value in the message
     * ("Duplicate entry 'PO-20260929-001' for key ..."), which is the only
     * driver-agnostic way to satisfy the second condition.
     */
    protected static function isSequenceCollision(QueryException $e, string $number): bool
    {
        $driverCode = (int) ($e->errorInfo[1] ?? 0);
        $message = strtolower($e->getMessage());

        $duplicateCodes = [1062, 23505, 2601, 2627];
        $isDuplicate = in_array($driverCode, $duplicateCodes, true)
            || str_contains($message, 'duplicate')
            || str_contains($message, 'unique constraint')
            || str_contains($message, 'already exists');

        if (! $isDuplicate) {
            return false;
        }

        return str_contains($message, strtolower($number))
            || str_contains($message, strtolower(static::sequenceColumn()));
    }
}
