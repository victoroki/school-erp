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
use Illuminate\Http\Request;
use Flash;

class ExpensesController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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

        $expenses = $query->latest('expense_date')->paginate(15);
        $categories = ExpenseCategory::pluck('name', 'category_id');

        return view('expenses.index', compact('expenses', 'categories'));
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

        return view('expenses.create', compact('categories', 'bankAccounts', 'suppliers', 'staff'));
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

        Flash::success('Expense request submitted and is pending approval.');

        return redirect(route('expenses.index'));
    }

    public function show($id)
    {
        $expenses = Expenses::with(['category', 'bankAccount', 'supplier', 'requestedBy', 'approvedBy', 'createdBy'])->find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');
            return redirect(route('expenses.index'));
        }

        return view('expenses.show', compact('expenses'));
    }

    public function approve(Request $request, $id)
    {
        $expense = Expenses::find($id);
        if (empty($expense)) {
            Flash::error('Expense not found');
            return redirect()->back();
        }

        $expense->update([
            'status' => 'approved',
            'approved_by' => auth()->id()
        ]);

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

        if (!$expense->bank_account_id && $expense->payment_method !== 'cash') {
            Flash::error('Please assign a bank account before marking as paid.');
            return redirect()->back();
        }

        $expense->update([
            'status' => 'paid',
            'payment_date' => now()
        ]);

        // Deduct from bank account
        if ($expense->bank_account_id) {
            $bankAccount = BankAccount::find($expense->bank_account_id);
            if ($bankAccount) {
                $bankAccount->decrement('current_balance', $expense->amount);
            }
        }

        Flash::success('Expense marked as paid and bank balance updated.');
        return redirect()->back();
    }

    public function destroy($id)
    {
        $expenses = Expenses::find($id);

        if (empty($expenses)) {
            Flash::error('Expenses not found');
            return redirect(route('expenses.index'));
        }

        // If it was already paid, reverse the bank balance update
        if ($expenses->status === 'paid' && $expenses->bank_account_id) {
            $bankAccount = BankAccount::find($expenses->bank_account_id);
            if ($bankAccount) {
                $bankAccount->increment('current_balance', $expenses->amount);
            }
        }

        $expenses->delete();

        Flash::success('Expenses deleted successfully.');

        return redirect(route('expenses.index'));
    }
}
