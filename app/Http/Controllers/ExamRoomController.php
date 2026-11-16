<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ExamRoom;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class ExamRoomController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $examRooms = ExamRoom::paginate(10);
        return view('exam_rooms.index', compact('examRooms'));
    }

    public function create()
    {
        return view('exam_rooms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_no' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'name' => 'nullable|string|max:100',
        ]);

        $examRoom = ExamRoom::create($request->except('_token'));
        AuditTrail::log('Exam Room', 'CREATE', $examRoom->id, null, $examRoom->toArray());
        Flash::success('Exam Room saved successfully.');
        return redirect(route('exam-rooms.index'));
    }

    public function edit($id)
    {
        $examRoom = ExamRoom::findOrFail($id);
        return view('exam_rooms.edit', compact('examRoom'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'room_no' => 'required|string|max:50',
            'capacity' => 'required|integer|min:1',
            'name' => 'nullable|string|max:100',
        ]);

        $examRoom = ExamRoom::findOrFail($id);
        $oldData = $examRoom->toArray();
        $examRoom->update($request->except('_token'));
        AuditTrail::log('Exam Room', 'UPDATE', $examRoom->id, $oldData, $examRoom->toArray());
        Flash::success('Exam Room updated successfully.');
        return redirect(route('exam-rooms.index'));
    }

    public function destroy($id)
    {
        $examRoom = ExamRoom::findOrFail($id);
        $oldData = $examRoom->toArray();
        $examRoom->delete();
        AuditTrail::log('Exam Room', 'DELETE', $id, $oldData, null);
        Flash::success('Exam Room deleted successfully.');
        return redirect(route('exam-rooms.index'));
    }
}
