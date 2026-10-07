<?php

namespace App\Http\Controllers;

use App\Models\StudentNotice;
use App\Models\Student;
use Illuminate\Http\Request;
use Flash;
use Auth;

class StudentNoticeController extends Controller
{
    public function __construct()
    {
        // `create` and `edit` render the write form, so they belong behind the
        // MANAGE permission. They used to sit behind `.view`, which handed a
        // Parent or Student — both of whom hold student-notices.view so they
        // can read the notices addressed to them — an open "create notice" page
        // whose POST was then correctly refused. The form and the ability it
        // submits have to agree.
        $this->middleware('can:student-notices.view')->only(['index', 'show']);
        $this->middleware('can:student-notices.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $notices = StudentNotice::with(['student', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('student-notices.index', compact('notices'));
    }

    public function create()
    {
        $students = Student::orderBy('first_name')->get()
            ->mapWithKeys(fn($s) => [$s->student_id => "$s->first_name $s->last_name ($s->admission_no)"])
            ->toArray();

        return view('student-notices.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id'  => 'required|exists:students,student_id',
            'title'       => 'required|string|max:255',
            'body'        => 'nullable|string',
            'notice_type' => 'nullable|string|in:general,behavior,academic,medical,attendance,other',
        ]);

        StudentNotice::create([
            'student_id'  => $request->student_id,
            'created_by'  => Auth::id(),
            'title'       => $request->title,
            'body'        => $request->body,
            'notice_type' => $request->notice_type ?? 'general',
        ]);

        Flash::success('Notice sent successfully.');

        return redirect()->route('student-notices.index');
    }

    public function show($id)
    {
        $notice = StudentNotice::with(['student', 'creator'])->findOrFail($id);

        return view('student-notices.show', compact('notice'));
    }

    public function update(Request $request, $id)
    {
        $notice = StudentNotice::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'body'        => 'nullable|string',
            'notice_type' => 'nullable|string|in:general,behavior,academic,medical,attendance,other',
        ]);

        $notice->update($request->only(['title', 'body', 'notice_type']));

        Flash::success('Notice updated.');

        return redirect()->route('student-notices.index');
    }

    public function destroy($id)
    {
        StudentNotice::findOrFail($id)->delete();

        Flash::success('Notice deleted.');

        return redirect()->route('student-notices.index');
    }
}