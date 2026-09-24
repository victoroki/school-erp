<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\PettyCashLog;
use Illuminate\Http\Request;
use Flash;

class PettyCashController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'destroy']);
    }

    /**
     * Petty cash ledger — all entries, running balance, and balance summary card.
     */
    public function index(Request $request)
    {
        $query = PettyCashLog::with('recordedBy')->orderByDesc('date')->orderByDesc('id');

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->to);
        }

        $entries = $query->paginate(20)->withQueryString();

        // Running balance: credits minus debits over ALL records (not just the
        // filtered page) so the summary card is always the true current balance.
        $balance = (float) PettyCashLog::selectRaw(
            "COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE -amount END), 0) as balance"
        )->value('balance');

        $totalCredits = (float) PettyCashLog::where('type', 'credit')->sum('amount');
        $totalDebits  = (float) PettyCashLog::where('type', 'debit')->sum('amount');

        return view('petty_cash.index', compact('entries', 'balance', 'totalCredits', 'totalDebits'));
    }

    /**
     * Log a new petty cash entry (top-up or disbursement).
     */
    public function create()
    {
        return view('petty_cash.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'type'        => 'required|in:credit,debit',
            'amount'      => 'required|numeric|min:0.01',
            'description' => 'required|string|max:255',
            'reference'   => 'nullable|string|max:100',
        ]);

        $entry = PettyCashLog::create([
            'date'        => $request->date,
            'type'        => $request->type,
            'amount'      => $request->amount,
            'description' => $request->description,
            'reference'   => $request->reference,
            'recorded_by' => auth()->id(),
        ]);

        AuditTrail::log('PettyCash', 'CREATE', $entry->id, null, $entry->toArray());

        Flash::success('Petty cash entry recorded successfully.');
        return redirect()->route('petty-cash.index');
    }

    public function destroy($id)
    {
        $entry = PettyCashLog::findOrFail($id);
        $oldData = $entry->toArray();
        $entry->delete();

        AuditTrail::log('PettyCash', 'DELETE', $id, $oldData, null);

        Flash::success('Entry deleted.');
        return redirect()->route('petty-cash.index');
    }
}
