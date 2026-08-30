<?php

namespace App\Http\Controllers;

use App\Models\Term;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
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
        $currentYear = AcademicYear::where('is_current', true)->first();
        $selectedYearId = $request->get('academic_year_id') ?: ($currentYear?->academic_year_id);

        $query = Term::with('academicYear')->orderBy('display_order')->orderBy('start_date');

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $terms = $query->paginate(20);
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');

        return view('fee_management.terms.index', compact('terms', 'academicYears', 'selectedYearId'));
    }

    public function create()
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id');

        return view('fee_management.terms.create', compact('academicYears'));
    }

    public function store(CreateTermRequest $request)
    {
        $input = $request->all();

        $term = Term::create($input);

        AuditTrail::log('Term', 'CREATE', $term->id, null, $term->toArray());

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
        $oldData = $term->toArray();
        $term->update($request->all());

        AuditTrail::log('Term', 'UPDATE', $term->id, $oldData, $term->toArray());

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

        $oldData = $term->toArray();
        $term->delete();

        AuditTrail::log('Term', 'DELETE', $id, $oldData, null);

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

        AuditTrail::log('Term', 'ACTIVATE', $term->id, ['status' => 'completed'], ['status' => 'active']);

        Flash::success('Term activated successfully.');

        return redirect()->back();
    }
}
