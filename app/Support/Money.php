<?php

namespace App\Support;

/**
 * One place that decides how a money amount is written.
 *
 * Two problems made this necessary:
 *
 * 1. The views used two currencies for the same shilling — 106 occurrences of
 *    "KSh" and 125 of "KES". No single screen mixed them, so it read as correct
 *    on every individual page, but moving between two fee screens changed the
 *    symbol. The backend had already settled the question: app/ uses "KES" 15
 *    times against "KSh" 4, and Student::getBalanceFeeAttribute() and
 *    SendFeeReminders both write 'KES '. KES is also the ISO 4217 code.
 *
 * 2. Six fee screens printed money with number_format($amount, 0), rounding
 *    away cents. Every amount column in this schema is DECIMAL(12,2), so the
 *    cents exist and the display was discarding them — on an arrears total, a
 *    refund figure and a revenue report.
 */
class Money
{
    /**
     * The currency symbol this application displays. Kept as a constant so a
     * future multi-currency school changes one line rather than 200 views.
     */
    public const SYMBOL = 'KES';

    /**
     * Money for display: symbol, thousands separators and two decimals.
     */
    public static function format(mixed $amount): string
    {
        return self::SYMBOL . ' ' . self::number($amount);
    }

    /**
     * Just the number, two decimals and thousands separators, no symbol.
     */
    public static function number(mixed $amount): string
    {
        return number_format((float) $amount, 2);
    }

    /**
     * Symbol-free with the given precision, for figures that are legitimately
     * whole — a count, a percentage, a year.
     */
    public static function whole(mixed $amount): string
    {
        return number_format((float) $amount, 0);
    }
}
