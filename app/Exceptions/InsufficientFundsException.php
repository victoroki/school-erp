<?php

namespace App\Exceptions;

use App\Models\BankAccount;
use App\Support\Money;

/**
 * A withdrawal or transfer was refused because it would push the account below
 * its balance (or its configured minimum balance).
 *
 * Thrown by BankLedger — the single point that moves money — so every money-out
 * path (expense payments, manual withdrawals, transfers) inherits the same
 * protection instead of each controller remembering to check for itself.
 * Controllers catch it and flash the message; it rolls back the surrounding
 * transaction like any other domain failure.
 */
class InsufficientFundsException extends \RuntimeException
{
    public function __construct(
        public readonly BankAccount $account,
        public readonly float $amount,
    ) {
        parent::__construct(sprintf(
            'Insufficient funds in %s: balance %s, paying %s would drop it below the required minimum of %s.',
            $account->account_name,
            Money::format($account->current_balance),
            Money::format($amount),
            Money::format($account->minimum_balance ?? 0),
        ));
    }
}
