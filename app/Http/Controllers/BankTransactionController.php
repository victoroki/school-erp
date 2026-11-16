<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Models\AuditTrail;
use App\Services\BankLedger;
use Illuminate\Http\Request;
use Flash;
use DB;

class BankTransactionController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show']);
        $this->middleware('can:finance.manage')->only(['create', 'store']);
    }

    public function index(Request $request)
    {
        $query = BankTransaction::with(['bankAccount', 'sourceAccount', 'targetAccount'])
            ->where('status', '!=', 'voided');

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('transaction_type')) {
            $query->when(
                $request->transaction_type === 'transfer',
                // A transfer now writes a withdrawal row on the source and a
                // deposit row on the target (see BankLedger::recordTransfer),
                // so match on the pair of account links rather than a type
                // that only legacy rows still carry.
                fn ($q) => $q->whereNotNull('source_account_id')->whereNotNull('target_account_id'),
                fn ($q) => $q->where('transaction_type', $request->transaction_type)
            );
        }

        $transactions = $query
            ->latest('transaction_date')
            ->latest('transaction_id')
            ->paginate(20)
            ->withQueryString();

        // Totals for the summary cards, over the same filtered set as the table.
        $summary = BankTransaction::query()
            ->where('status', '!=', 'voided')
            ->when($request->filled('account_id'), fn ($q) => $q->where('account_id', $request->account_id))
            ->when(
                $request->filled('transaction_type') && $request->transaction_type === 'transfer',
                fn ($q) => $q->whereNotNull('source_account_id')->whereNotNull('target_account_id')
            )
            ->when(
                $request->filled('transaction_type') && $request->transaction_type !== 'transfer',
                fn ($q) => $q->where('transaction_type', $request->transaction_type)
            )
            ->selectRaw("SUM(CASE WHEN transaction_type = 'deposit' THEN amount ELSE 0 END) as total_in")
            ->selectRaw("SUM(CASE WHEN transaction_type = 'withdrawal' THEN amount ELSE 0 END) as total_out")
            ->selectRaw('COUNT(*) as tx_count')
            ->first();

        $bankAccounts = BankAccount::orderBy('account_name')->pluck('account_name', 'account_id');
        $totalBalance = BankAccount::where('status', 'active')->sum('current_balance');

        return view('bank_transactions.index', compact(
            'transactions', 'bankAccounts', 'summary', 'totalBalance'
        ));
    }

    public function create()
    {
        // Balance in the label so the bursar can see the position while picking an account.
        $bankAccounts = BankAccount::where('status', 'active')
            ->orderBy('account_name')
            ->get()
            ->mapWithKeys(fn ($account) => [
                $account->account_id => $account->account_name . ' — ' . \App\Support\Money::format($account->current_balance),
            ]);

        return view('bank_transactions.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:bank_accounts,account_id',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer',
            'transaction_date' => 'required|date',
            'target_account_id' => 'required_if:transaction_type,transfer|nullable|exists:bank_accounts,account_id|different:account_id',
        ]);

        $account = BankAccount::findOrFail($request->account_id);
        $date = $request->transaction_date;
        $amount = (float) $request->amount;
        $description = $request->description;
        $reference = $request->reference_number;
        $userId = auth()->id();

        // BankLedger is the single place that moves a balance and writes the
        // matching statement row, so hand-recorded entries behave exactly like
        // expense payments and banked income. It also refuses to overdraw the
        // account — catch that and report it as a flash, not a 500.
        try {
            $transaction = match ($request->transaction_type) {
                'deposit' => BankLedger::recordDeposit($account, $amount, $date, $description, $reference, $userId),
                'withdrawal' => BankLedger::recordWithdrawal($account, $amount, $date, $description, $reference, $userId),
                'transfer' => BankLedger::recordTransfer(
                    $account,
                    BankAccount::findOrFail($request->target_account_id),
                    $amount, $date, $description, $reference, $userId
                ),
            };
        } catch (\App\Exceptions\InsufficientFundsException $e) {
            Flash::error($e->getMessage());
            return redirect()->back()->withInput();
        }

        AuditTrail::log('Bank Transaction', 'CREATE', $transaction->transaction_id, null, $transaction->toArray());

        Flash::success('Bank transaction recorded successfully.');
        return redirect(route('bank-transactions.index'));
    }
}
