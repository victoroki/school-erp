<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExpensesRequest;
use App\Http\Requests\UpdateExpensesRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Expenses;
use App\Models\ExpenseCategory;
use App\Models\BankAccount;
use App\Models\Supplier;
use App\Models\Staff;
use App\Models\AuditTrail;
use App\Services\BankLedger;
use Illuminate\Http\Request;
use Flash;

class ExpensesController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show', 'pending']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'markAsPaid']);
        $this->middleware('can:finance.approve')->only(['approve']);
    }

    public function index(Request $request)
    {
        $query = Expenses::with(['category', 'bankAccount', 'requestedBy', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $expenses = $query->latest('expense_date')->paginate(15)->withQueryString();
        $categories = ExpenseCategory::pluck('name', 'category_id');
        $bankAccounts = BankAccount::where('status', 'active')->pluck('account_name', 'account_id');

        return view('expenses.index', compact('expenses', 'categories', 'bankAccounts'));
    }

    public function pending()
    {
        $expenses = Expenses::with(['category', 'requestedBy'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);
            
        return view('expenses.pending', compact('expenses'));
    }

    public function create()
    {
        $categories = ExpenseCategory::pluck('name', 'category_id');
        $bankAccounts = BankAccount::where('status', 'active')->get()->pluck('account_name', 'account_id');
        $suppliers = Supplier::pluck('name', 'supplier_id');
        $staff = Staff::select('staff_id', 'first_name', 'last_name')->get()->mapWithKeys(function ($item) {
            return [$item->staff_id => $item->first_name . ' ' . $item->last_name];
        });
        $requestedByDefault = Staff::where('user_id', auth()->id())->value('staff_id');

        return view('expenses.create', compact('categories', 'bankAccounts', 'suppliers', 'staff', 'requestedByDefault'));
    }

    public function store(CreateExpensesRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = auth()->id();
        
        // Default status is pending for normal users, maybe approved for admins
        $input['status'] = 'pending';

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expense_attachments', 'public');
            $input['attachment'] = $path;
        }

        $expenses = Expenses::create($input);

        AuditTrail::log('Expense', 'CREATE', $expenses->expense_id, null, $expenses->toArray());

        Flash::success('Expense request submitted and is pending approval.');

        return redirect(route('expenses.index'));
    }

    public function edit($id)
    {
        $expenses = Expenses::find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');
            return redirect(route('expenses.index'));
        }

        $categories = ExpenseCategory::pluck('name', 'category_id');
        $bankAccounts = BankAccount::where('status', 'active')->pluck('account_name', 'account_id');
        $suppliers = Supplier::pluck('name', 'supplier_id');
        $staff = Staff::select('staff_id', 'first_name', 'last_name')->get()->mapWithKeys(function ($item) {
            return [$item->staff_id => $item->first_name . ' ' . $item->last_name];
        });

        return view('expenses.edit', compact('expenses', 'categories', 'bankAccounts', 'suppliers', 'staff'));
    }

    public function update($id, UpdateExpensesRequest $request)
    {
        $expenses = Expenses::find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');
            return redirect(route('expenses.index'));
        }

        $input = $request->all();

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('expense_attachments', 'public');
            $input['attachment'] = $path;
        }

        $oldData = $expenses->toArray();
        $expenses->update($input);

        AuditTrail::log('Expense', 'UPDATE', $expenses->expense_id, $oldData, $expenses->toArray());

        Flash::success('Expense updated successfully.');

        return redirect(route('expenses.index'));
    }

    public function show($id)
    {
        $expenses = Expenses::with(['category', 'bankAccount', 'supplier', 'requestedBy', 'approvedBy', 'createdBy'])->find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');
            return redirect(route('expenses.index'));
        }

        $bankAccounts = BankAccount::where('status', 'active')->pluck('account_name', 'account_id');

        return view('expenses.show', compact('expenses', 'bankAccounts'));
    }

    public function approve(Request $request, $id)
    {
        $expense = Expenses::find($id);
        if (empty($expense)) {
            Flash::error('Expense not found');
            return redirect()->back();
        }

        // Approval is the control gate: only a pending expense can pass it.
        // Before this check a paid or rejected expense could be "approved"
        // again from a crafted URL, rewriting who authorised it.
        if ($expense->status !== 'pending') {
            Flash::error('Only pending expenses can be approved. This one is already ' . $expense->status . '.');
            return redirect()->back();
        }

        // Segregation of duties: the person who raised or recorded an expense
        // may not be the one who authorises it. Protected roles (Owner, Super
        // Admin) bypass — they are the school's system administrators, and in
        // a one-person finance office they are the last resort.
        $user = auth()->user();
        $isInitiator = $expense->created_by === auth()->id() || $expense->requested_by === auth()->id();
        if ($isInitiator && ! $user->canBypassProtection()) {
            Flash::error('You cannot approve an expense you recorded or requested. Ask another approver to review it.');
            return redirect()->back();
        }

        $oldStatus = $expense->status;
        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id()
        ]);

        AuditTrail::log('Expense', 'APPROVE', $expense->expense_id, ['status' => $oldStatus], $expense->toArray());

        Flash::success('Expense approved successfully.');
        return redirect()->back();
    }

    public function markAsPaid(Request $request, $id)
    {
        $expense = Expenses::find($id);
        if (empty($expense)) {
            Flash::error('Expense not found');
            return redirect()->back();
        }

        // Guard against a double submit deducting the account twice.
        if ($expense->status === 'paid') {
            Flash::error('This expense has already been paid.');
            return redirect()->back();
        }

        // Payment is the step after approval. Without this, a crafted POST
        // could pay a pending or rejected expense and skip the approver
        // entirely — the same hole the approve gate exists to close.
        if ($expense->status !== 'approved') {
            Flash::error('Only approved expenses can be marked as paid. This one is ' . $expense->status . '.');
            return redirect()->back();
        }

        if ($request->filled('bank_account_id')) {
            $this->validate($request, ['bank_account_id' => 'required|exists:bank_accounts,account_id']);
            $expense->bank_account_id = $request->bank_account_id;
        }

        if (!$expense->bank_account_id && $expense->payment_method !== 'cash') {
            Flash::error('Please choose a bank account before marking as paid. Use the selector on this row and try again.');
            return redirect()->back();
        }

        $oldStatus = $expense->status;

        // One commit for the status change and the ledger movement: if either
        // fails, neither happens — a "paid" expense can never lack its
        // statement row, and a payment can never be recorded for an expense
        // that is not marked paid.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($expense, $oldStatus) {
                $expense->update([
                    'status' => 'paid',
                    'payment_date' => now()
                ]);

                if ($expense->bank_account_id) {
                    $bankAccount = BankAccount::find($expense->bank_account_id);
                    if ($bankAccount) {
                        BankLedger::recordWithdrawal(
                            $bankAccount,
                            (float) $expense->amount,
                            now()->toDateString(),
                            BankLedger::describe('Expense', $expense->expense_id, $expense->description ?: ($expense->category->name ?? 'Expense')),
                            $expense->reference_number,
                            auth()->id(),
                            'unreconciled',
                            'Expense',
                            $expense->expense_id
                        );
                    }
                }

                AuditTrail::log('Expense', 'MARK PAID', $expense->expense_id, ['status' => $oldStatus], $expense->toArray());
            });
        } catch (\App\Exceptions\InsufficientFundsException $e) {
            // The transaction rolled back, so the expense is still approved
            // and the balance untouched — report the shortfall and stop.
            Flash::error($e->getMessage());
            return redirect()->back();
        }

        Flash::success('Expense marked as paid and recorded in the bank ledger.');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $expenses = Expenses::find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');
            return redirect(route('expenses.index'));
        }

        // If it was already paid, reverse its ledger entry (balance + statement row).
        if ($expenses->status === 'paid' && $expenses->bank_account_id) {
            $transaction = BankLedger::findFor('Expense', $expenses->expense_id);
            if ($transaction) {
                BankLedger::reverse($transaction);
            } else {
                // Ledger entry predates the BankLedger service — undo the balance only.
                $bankAccount = BankAccount::find($expenses->bank_account_id);
                if ($bankAccount) {
                    $bankAccount->increment('current_balance', $expenses->amount);
                }
            }
        }

        $oldData = $expenses->toArray();
        $expenses->delete();

        AuditTrail::log('Expense', 'DELETE', $id, $oldData, null);

        Flash::success('Expenses deleted successfully.');

        return redirect(route('expenses.index'));
    }
}
