<?php

namespace App\Http\Controllers;

use App\Models\MedicalIncident;
use App\Models\Student;
use Illuminate\Http\Request;
use Flash;
use Auth;

class MedicalIncidentController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'incident_date' => 'required|date',
            'symptoms' => 'required|string',
            'details' => 'nullable|string',
            'treatment_given' => 'nullable|string',
            'notified_parents' => 'boolean'
        ]);

        $data = $request->all();
        $data['marked_by'] = Auth::id();
        $data['notified_parents'] = $request->has('notified_parents');

        MedicalIncident::create($data);

        Flash::success('Medical incident logged successfully.');

        return redirect()->back();
    }
}
