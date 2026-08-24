<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssessmentType;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class AssessmentTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $assessmentTypes = AssessmentType::paginate(10);
        return view('assessment_types.index', compact('assessmentTypes'));
    }

    public function create()
    {
        return view('assessment_types.create');
    }

    public function store(Request $request)
    {
        $request->validate(AssessmentType::$rules);
        $assessmentType = AssessmentType::create($request->all());
        AuditTrail::log('Assessment Type', 'CREATE', $assessmentType->id, null, $assessmentType->toArray());
        Flash::success('Assessment Type saved successfully.');
        return redirect(route('assessment-types.index'));
    }

    public function edit(string $id)
    {
        $assessmentType = AssessmentType::findOrFail($id);
        return view('assessment_types.edit', compact('assessmentType'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate(AssessmentType::$rules);
        $assessmentType = AssessmentType::findOrFail($id);
        $oldData = $assessmentType->toArray();
        $assessmentType->update($request->all());
        AuditTrail::log('Assessment Type', 'UPDATE', $assessmentType->id, $oldData, $assessmentType->toArray());
        Flash::success('Assessment Type updated successfully.');
        return redirect(route('assessment-types.index'));
    }

    public function destroy(string $id)
    {
        $assessmentType = AssessmentType::findOrFail($id);
        $oldData = $assessmentType->toArray();
        $assessmentType->delete();
        AuditTrail::log('Assessment Type', 'DELETE', $id, $oldData, null);
        Flash::success('Assessment Type deleted successfully.');
        return redirect(route('assessment-types.index'));
    }
}
