<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class FinancialYearController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show', 'audit']);
        $this->middleware('can:finance.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $financialYears = FinancialYear::latest()->paginate(10);
        return view('financial_years.index', compact('financialYears'));
    }

    public function create()
    {
        return view('financial_years.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $financialYear = FinancialYear::create($request->all());

        AuditTrail::log('Financial Year', 'CREATE', $financialYear->id, null, $financialYear->toArray());

        Flash::success('Financial Year created successfully.');
        return redirect(route('financial-years.index'));
    }

    public function edit($id)
    {
        $financialYear = FinancialYear::findOrFail($id);
        return view('financial_years.edit', compact('financialYear'));
    }

    public function update(Request $request, $id)
    {
        $financialYear = FinancialYear::findOrFail($id);

        // Update takes the same shape as create: the old code wrote
        // $request->all() unchecked, so a crafted request could inject
        // arbitrary columns (including created_at) into the row.
        $request->validate([
            'name' => 'required|string|max:100',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:open,closed',
        ]);

        $oldData = $financialYear->toArray();
        $financialYear->update($request->only(['name', 'start_date', 'end_date', 'status']));

        AuditTrail::log('Financial Year', 'UPDATE', $financialYear->id, $oldData, $financialYear->toArray());

        Flash::success('Financial Year updated successfully.');
        return redirect(route('financial-years.index'));
    }

    public function audit($id)
    {
        $financialYear = FinancialYear::findOrFail($id);

        $logs = AuditTrail::query()
            ->where('module', 'Financial Year')
            ->where('record_id', $id)
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return view('financial_years.audit', compact('financialYear', 'logs'));
    }
}
