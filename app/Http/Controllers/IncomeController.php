<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\BankAccount;
use App\Models\AuditTrail;
use App\Services\BankLedger;
use Illuminate\Http\Request;
use Flash;

class IncomeController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = Income::with(['category', 'bankAccount']);

        // Filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('income_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $incomes = $query->latest('income_date')->paginate(15)->withQueryString();
        $categories = IncomeCategory::pluck('name', 'category_id');

        return view('income.index', compact('incomes', 'categories'));
    }

    public function create()
    {
        $categories = IncomeCategory::pluck('name', 'category_id');
        $bankAccounts = BankAccount::where('status', 'active')->get()->pluck('account_name', 'account_id');
        
        return view('income.create', compact('categories', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate(Income::$rules);

        $input = $request->all();
        $input['received_by'] = auth()->id();
        $input['status'] = 'active';

        // Handle attachment
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('income_attachments', 'public');
            $input['attachment'] = $path;
        }

        $income = Income::create($input);

        // Banked income must appear on the bank statement too — BankLedger
        // increments the balance AND writes the matching deposit row.
        if ($income->bank_account_id) {
            $bankAccount = BankAccount::find($income->bank_account_id);
            if ($bankAccount) {
                BankLedger::recordDeposit(
                    $bankAccount,
                    (float) $income->amount,
                    $income->income_date instanceof \Carbon\CarbonInterface ? $income->income_date->toDateString() : (string) $income->income_date,
                    BankLedger::describe('Income', $income->income_id, $income->description ?: ($income->payer_name ?: 'Income')),
                    $income->reference_number,
                    auth()->id(),
                    'unreconciled',
                    'Income',
                    $income->income_id
                );
            }
        }

        AuditTrail::log('Finance', 'Recorded Income', $income->income_id, null, $income->toArray());

        Flash::success('Income recorded successfully.');

        return redirect(route('income.index'));
    }

    public function show($id)
    {
        $income = Income::with(['category', 'bankAccount', 'receivedBy'])->find($id);

        if (empty($income)) {
            Flash::error('Income record not found');
            return redirect(route('income.index'));
        }

        return view('income.show', compact('income'));
    }

    public function edit($id)
    {
        $income = Income::find($id);

        if (empty($income)) {
            Flash::error('Income record not found');
            return redirect(route('income.index'));
        }

        $categories = IncomeCategory::pluck('name', 'category_id');
        $bankAccounts = BankAccount::where('status', 'active')->get()->pluck('account_name', 'account_id');

        return view('income.edit', compact('income', 'categories', 'bankAccounts'));
    }

    public function update($id, Request $request)
    {
        $income = Income::find($id);

        if (empty($income)) {
            Flash::error('Income record not found');
            return redirect(route('income.index'));
        }

        $request->validate(Income::$rules);

        $input = $request->except(['received_by', 'status']);
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('income_attachments', 'public');
            $input['attachment'] = $path;
        }

        $oldData = $income->toArray();

        // The bank ledger must follow the edited state: whatever was banked
        // before is voided, whatever is banked now is recorded. Both halves
        // happen inside the same commit as the income-row update, so a saved
        // income can never disagree with the statement it produced.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($income, $input) {
                $wasBanked = (bool) $income->bank_account_id;
                $newBankId = $input['bank_account_id'] ?? null;
                $newAmount = (float) $input['amount'];

                if ($wasBanked) {
                    $transaction = BankLedger::findFor('Income', $income->income_id);
                    if ($transaction) {
                        BankLedger::reverse($transaction);
                    } else {
                        // Ledger entry predates the BankLedger service — undo the balance only.
                        $legacyAccount = BankAccount::find($income->bank_account_id);
                        if ($legacyAccount) {
                            $legacyAccount->decrement('current_balance', $income->amount);
                        }
                    }
                }

                // An edit that switches a banked income to cash (or drops the
                // account) must also clear the stale bank_account_id, otherwise
                // the row still claims to be banked while the balance was reversed.
                if (!$newBankId) {
                    $input['bank_account_id'] = null;
                }

                $income->update($input);

                if ($newBankId) {
                    $bankAccount = BankAccount::find($newBankId);
                    if ($bankAccount) {
                        BankLedger::recordDeposit(
                            $bankAccount,
                            $newAmount,
                            $income->income_date instanceof \Carbon\CarbonInterface ? $income->income_date->toDateString() : (string) $income->income_date,
                            BankLedger::describe('Income', $income->income_id, $income->description ?: ($income->payer_name ?: 'Income')),
                            $income->reference_number,
                            auth()->id(),
                            'unreconciled',
                            'Income',
                            $income->income_id
                        );
                    }
                }
            });
        } catch (\Throwable $e) {
            Flash::error('Could not update income: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        AuditTrail::log('Finance', 'UPDATED Income', $income->income_id, $oldData, $income->fresh()->toArray());

        Flash::success('Income updated successfully.');
        return redirect(route('income.show', $income->income_id));
    }

    public function destroy($id)
    {
        $income = Income::find($id);

        if (empty($income)) {
            Flash::error('Income record not found');
            return redirect(route('income.index'));
        }

        // Reverse the ledger entry (balance + statement row) written at store time.
        if ($income->bank_account_id) {
            $transaction = BankLedger::findFor('Income', $income->income_id);
            if ($transaction) {
                BankLedger::reverse($transaction);
            } else {
                // Ledger entry predates the BankLedger service — undo the balance only.
                $bankAccount = BankAccount::find($income->bank_account_id);
                if ($bankAccount) {
                    $bankAccount->decrement('current_balance', $income->amount);
                }
            }
        }

        $oldData = $income->toArray();
        $income->delete();

        AuditTrail::log('Finance', 'DELETE', $income->income_id, $oldData, null);

        Flash::success('Income record deleted successfully.');

        return redirect(route('income.index'));
    }
}
