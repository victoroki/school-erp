<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\AcademicYear;
use App\Http\Requests\CreateTermRequest;
use App\Http\Requests\UpdateTermRequest;
use Illuminate\Http\Request;
use Flash;

class TermController extends AppBaseController
{
    public function __construct()
    {
        $this->middleware('can:academics.settings.manage')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = Term::with('academicYear')->orderBy('display_order')->orderBy('start_date');

        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $terms = $query->paginate(20);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');

        return view('fee_management.terms.index', compact('terms', 'academicYears'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');

        return view('fee_management.terms.create', compact('academicYears'));
    }

    public function store(CreateTermRequest $request)
    {
        $input = $request->all();

        Term::create($input);

        Flash::success('Term created successfully.');

        return redirect(route('fees.terms.index'));
    }

    public function show($id)
    {
        $term = Term::with(['academicYear', 'feeStructures.category', 'feeAssignments.student'])->findOrFail($id);

        return view('fee_management.terms.show', compact('term'));
    }

    public function edit($id)
    {
        $term = Term::findOrFail($id);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');

        return view('fee_management.terms.edit', compact('term', 'academicYears'));
    }

    public function update($id, UpdateTermRequest $request)
    {
        $term = Term::findOrFail($id);
        $term->update($request->all());

        Flash::success('Term updated successfully.');

        return redirect(route('fees.terms.index'));
    }

    public function destroy($id)
    {
        $term = Term::findOrFail($id);

        if ($term->feeAssignments()->exists()) {
            Flash::error('Cannot delete term that has fee assignments.');
            return redirect()->back();
        }

        $term->delete();

        Flash::success('Term deleted successfully.');

        return redirect(route('fees.terms.index'));
    }

    public function activate($id)
    {
        $term = Term::findOrFail($id);

        Term::where('academic_year_id', $term->academic_year_id)
            ->where('status', 'active')
            ->update(['status' => 'completed']);

        $term->update(['status' => 'active']);

        Flash::success('Term activated successfully.');

        return redirect()->back();
    }
}
