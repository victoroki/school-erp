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

    /**
     * Spell an amount in words (Kenyan Shillings) for a receipt's legal line.
     *
     * Lives here rather than on one controller because both the per-payment
     * receipt and the sponsor/bursary bulk receipt print it; duplicating it
     * would let the two receipts word the same amount differently.
     */
    public static function inWords(mixed $amount): string
    {
        $amount = (float) $amount;
        $shillings = (int) floor($amount);
        $cents = (int) round(($amount - $shillings) * 100);

        if ($cents === 100) {
            $shillings++;
            $cents = 0;
        }

        $words = self::numberToWords($shillings) . ' shilling' . ($shillings === 1 ? '' : 's');

        if ($cents > 0) {
            $words .= ' and ' . self::numberToWords($cents) . ' cent' . ($cents === 1 ? '' : 's');
        }

        return ucfirst($words . ' only');
    }

    private static function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'zero';
        }

        $ones = [
            '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine',
            'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen',
            'seventeen', 'eighteen', 'nineteen',
        ];
        $tens = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

        $chunks = [
            ['value' => 1000000000, 'label' => 'billion'],
            ['value' => 1000000, 'label' => 'million'],
            ['value' => 1000, 'label' => 'thousand'],
            ['value' => 100, 'label' => 'hundred'],
        ];

        $parts = [];

        foreach ($chunks as $chunk) {
            if ($number >= $chunk['value']) {
                $count = intdiv($number, $chunk['value']);
                $parts[] = trim(self::numberToWords($count) . ' ' . $chunk['label']);
                $number %= $chunk['value'];
            }
        }

        if ($number >= 20) {
            $part = $tens[intdiv($number, 10)];
            if ($number % 10 > 0) {
                $part .= '-' . $ones[$number % 10];
            }
            $parts[] = $part;
            $number = 0;
        }

        if ($number > 0) {
            $parts[] = $ones[$number];
        }

        return implode(' ', array_filter($parts));
    }
}
