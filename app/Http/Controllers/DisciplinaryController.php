<?php

namespace App\Http\Controllers;

use App\Models\DisciplinaryRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Flash;
use Auth;

class DisciplinaryController extends Controller
{
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

        DisciplinaryRecord::create($data);

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

        $record->update($request->only(['action_taken', 'status']));

        Flash::success('Record updated successfully.');

        return redirect()->back();
    }
}
