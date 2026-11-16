<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\BankTransaction;
use App\Models\BankReconciliation;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;

class BankReconciliationController extends Controller
{
    public function __construct()
    {
        // Reconciliation is a financial control, not just a screen: marking
        // transactions reconciled attests they match the bank statement. It
        // used to sit under finance.view alone, so anyone who could *see*
        // money could sign off on it. Viewing stays with finance.view;
        // attesting requires finance.approve.
        $this->middleware('can:finance.view')->only(['index', 'show']);
        $this->middleware('can:finance.approve')->only(['update']);
    }

    public function index()
    {
        $bankAccounts = BankAccount::all();
        return view('bank_reconciliations.index', compact('bankAccounts'));
    }

    public function show($id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        
        $transactions = BankTransaction::where('account_id', $id)
            ->whereNotIn('status', ['reconciled', 'voided'])
            ->orderBy('transaction_date', 'desc')
            ->get();
            
        return view('bank_reconciliations.show', compact('bankAccount', 'transactions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
            'statement_balance' => 'required|numeric|min:0',
        ]);

        $bankAccount = BankAccount::findOrFail($id);

        $count = count($request->transaction_ids);

        $systemBalance = (float) $bankAccount->current_balance;
        $statementBalance = (float) $request->statement_balance;
        $variance = round($systemBalance - $statementBalance, 2);

        // One commit for the row updates and the reconciliation record: a
        // signed-off statement must always have its variance on file, so the
        // difference between the system book and the bank statement is
        // auditable instead of silently disappearing.
        DB::transaction(function () use ($request, $bankAccount, $count, $systemBalance, $statementBalance, $variance) {
            BankTransaction::whereIn('transaction_id', $request->transaction_ids)
                ->update(['status' => 'reconciled']);

            BankReconciliation::create([
                'bank_account_id' => $bankAccount->account_id,
                'statement_date' => now()->toDateString(),
                'statement_balance' => $statementBalance,
                'system_balance' => $systemBalance,
                'status' => 'completed',
                'reconciled_by' => auth()->id(),
            ]);

            AuditTrail::log('Bank Reconciliation', 'RECONCILE', $bankAccount->account_id, null, [
                'account_id' => $bankAccount->account_id,
                'account_name' => $bankAccount->account_name,
                'transaction_ids' => $request->transaction_ids,
                'transactions_reconciled' => $count,
                'system_balance' => $systemBalance,
                'statement_balance' => $statementBalance,
                'variance' => $variance,
            ]);
        });

        if ($variance != 0) {
            return redirect()->route('bank-reconciliations.index')->with(
                'warning',
                $count . ' transactions reconciled for ' . $bankAccount->account_name .
                ' with an unreconciled difference of KES ' . number_format(abs($variance), 2) . '.'
            );
        }

        return redirect()->route('bank-reconciliations.index')
            ->with('success', $count . ' transactions successfully reconciled for ' . $bankAccount->account_name . '.');
    }
}
