<?php

namespace App\Http\Controllers;

use App\Models\MedicalIncident;
use App\Models\Student;
use Illuminate\Http\Request;
use Flash;
use Auth;

class MedicalIncidentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.view')->only(['index', 'show']);
        $this->middleware('can:students.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $medicalIncidents = MedicalIncident::with(['student', 'marker'])
            ->orderBy('incident_date', 'desc')
            ->paginate(15);

        return view('medical_incidents.index', compact('medicalIncidents'));
    }

    public function create()
    {
        $students = Student::orderBy('first_name')->get()
            ->mapWithKeys(fn($s) => [$s->student_id => "$s->first_name $s->last_name ($s->admission_no)"])
            ->toArray();
            
        return view('medical_incidents.create', compact('students'));
    }

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
