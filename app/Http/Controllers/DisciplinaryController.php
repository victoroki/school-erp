<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryRecord;
use App\Models\Student;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use Auth;

class DisciplinaryController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:discipline.view')->only(['index', 'show']);
        $this->middleware('can:discipline.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $disciplinaryRecords = DisciplinaryRecord::with(['student', 'reporter'])
            ->orderBy('incident_date', 'desc')
            ->paginate(15);

        return view('disciplinary_records.index', compact('disciplinaryRecords'));
    }

    public function create()
    {
        $students = Student::orderBy('first_name')->get()
            ->mapWithKeys(fn($s) => [$s->student_id => "$s->first_name $s->last_name ($s->admission_no)"])
            ->toArray();
            
        return view('disciplinary_records.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,student_id',
            'incident_date' => 'required|date',
            'incident_type' => 'required|string',
            'description' => 'required|string',
            'action_taken' => 'nullable|string',
            'status' => 'required|in:open,investigating,closed'
        ]);

        $data = $request->all();
        $data['reported_by'] = Auth::id();

        $record = DisciplinaryRecord::create($data);

        AuditTrail::log('Disciplinary', 'CREATE', $record->disciplinary_record_id, null, $record->toArray());

        Flash::success('Disciplinary action logged successfully.');

        return redirect()->back();
    }

    public function update(Request $request, $id)
    {
        $record = DisciplinaryRecord::findOrFail($id);
        
        $request->validate([
            'action_taken' => 'nullable|string',
            'status' => 'required|in:open,investigating,closed'
        ]);

        $oldData = $record->toArray();
        $record->update($request->only(['action_taken', 'status']));

        AuditTrail::log('Disciplinary', 'UPDATE', $record->disciplinary_record_id, $oldData, $record->toArray());

        Flash::success('Record updated successfully.');

        return redirect()->back();
    }
}
