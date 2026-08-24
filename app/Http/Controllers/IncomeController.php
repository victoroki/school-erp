<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeCategory;
use App\Models\BankAccount;
use App\Models\AuditTrail;
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

        $incomes = $query->latest('income_date')->paginate(15);
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

        AuditTrail::log('Finance', 'Recorded Income', $income->income_id, null, $income->toArray());

        // Update bank account balance if applicable
        if ($income->bank_account_id) {
            $bankAccount = BankAccount::find($income->bank_account_id);
            if ($bankAccount) {
                $bankAccount->increment('current_balance', $income->amount);
            }
        }

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

    public function destroy($id)
    {
        $income = Income::find($id);

        if (empty($income)) {
            Flash::error('Income record not found');
            return redirect(route('income.index'));
        }

        // Reverse bank account balance
        if ($income->bank_account_id) {
            $bankAccount = BankAccount::find($income->bank_account_id);
            if ($bankAccount) {
                $bankAccount->decrement('current_balance', $income->amount);
            }
        }

        $oldData = $income->toArray();
        $income->delete();

        AuditTrail::log('Finance', 'DELETE', $income->income_id, $oldData, null);

        Flash::success('Income record deleted successfully.');

        return redirect(route('income.index'));
    }
}
