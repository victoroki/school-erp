<?php

namespace App\Http\Controllers;

use App\Models\BankTransaction;
use App\Models\BankAccount;
use App\Models\AuditTrail;
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
        $query = BankTransaction::with(['bankAccount', 'sourceAccount', 'targetAccount']);

        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        $transactions = $query->latest('transaction_date')->paginate(20);
        $bankAccounts = BankAccount::pluck('account_name', 'account_id');

        return view('bank_transactions.index', compact('transactions', 'bankAccounts'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::where('status', 'active')->pluck('account_name', 'account_id');
        return view('bank_transactions.create', compact('bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required',
            'amount' => 'required|numeric|min:0.01',
            'transaction_type' => 'required|in:deposit,withdrawal,transfer',
            'transaction_date' => 'required|date',
        ]);

        $input = $request->all();
        $input['created_by'] = auth()->id();

        $transaction = DB::transaction(function () use ($input, $request) {
            $transaction = BankTransaction::create($input);
            $account = BankAccount::find($input['account_id']);

            if ($input['transaction_type'] == 'deposit') {
                $account->increment('current_balance', $input['amount']);
            } elseif ($input['transaction_type'] == 'withdrawal') {
                $account->decrement('current_balance', $input['amount']);
            } elseif ($input['transaction_type'] == 'transfer') {
                if ($request->filled('target_account_id')) {
                    $account->decrement('current_balance', $input['amount']);
                    $targetAccount = BankAccount::find($request->target_account_id);
                    $targetAccount->increment('current_balance', $input['amount']);
                }
            }

            return $transaction;
        });

        AuditTrail::log('Bank Transaction', 'CREATE', $transaction->transaction_id, null, $transaction->toArray());

        Flash::success('Bank transaction recorded successfully.');
        return redirect(route('bank-transactions.index'));
    }
}
