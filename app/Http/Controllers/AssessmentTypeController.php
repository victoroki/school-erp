<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AssessmentType;
use Illuminate\Http\Request;
use Flash;

class AssessmentTypeController extends Controller
{
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
        AssessmentType::create($request->all());
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
        $assessmentType->update($request->all());
        Flash::success('Assessment Type updated successfully.');
        return redirect(route('assessment-types.index'));
    }

    public function destroy(string $id)
    {
        $assessmentType = AssessmentType::findOrFail($id);
        $assessmentType->delete();
        Flash::success('Assessment Type deleted successfully.');
        return redirect(route('assessment-types.index'));
    }
}
