<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Models\BankTransaction;

class BankReconciliationController extends Controller
{
    public function index()
    {
        $bankAccounts = BankAccount::all();
        return view('bank_reconciliations.index', compact('bankAccounts'));
    }

    public function show($id)
    {
        $bankAccount = BankAccount::findOrFail($id);
        
        $transactions = BankTransaction::where('account_id', $id)
            ->where('status', '!=', 'reconciled')
            ->orderBy('transaction_date', 'desc')
            ->get();
            
        return view('bank_reconciliations.show', compact('bankAccount', 'transactions'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'transaction_ids' => 'required|array',
        ]);

        $bankAccount = BankAccount::findOrFail($id);

        // Mark transactions as reconciled
        BankTransaction::whereIn('transaction_id', $request->transaction_ids)
            ->update(['status' => 'reconciled']);

        return redirect()->route('bank-reconciliations.index')
            ->with('success', count($request->transaction_ids) . ' transactions successfully reconciled for ' . $bankAccount->account_name . '.');
    }
}
