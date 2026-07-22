<?php

namespace App\Http\Controllers;

use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Flash;

class FinancialYearController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:finance.view')->only(['index', 'show']);
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

        FinancialYear::create($request->all());

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
        $financialYear->update($request->all());

        Flash::success('Financial Year updated successfully.');
        return redirect(route('financial-years.index'));
    }
}
