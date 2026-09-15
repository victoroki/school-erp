<?php

namespace App\Http\Controllers;

use App\Models\Homework;
use Illuminate\Http\Request;
use Flash;
use Auth;

class HomeworkController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:homework.view')->only(['index', 'show', 'create', 'edit']);
        $this->middleware('can:homework.manage')->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $homework = Homework::with('creator')
            ->orderBy('due_date', 'desc')
            ->paginate(15);

        return view('homework.index', compact('homework'));
    }

    public function create()
    {
        return view('homework.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject'     => 'nullable|string|max:255',
            'class_name'  => 'nullable|string|max:255',
            'due_date'    => 'nullable|date',
        ]);

        Homework::create([
            'created_by'  => Auth::id(),
            'title'       => $request->title,
            'description' => $request->description,
            'subject'     => $request->subject,
            'class_name'  => $request->class_name,
            'due_date'    => $request->due_date,
            'status'      => 'active',
        ]);

        Flash::success('Homework assigned successfully.');

        return redirect()->route('homework.index');
    }

    public function show($id)
    {
        $homework = Homework::with('creator')->findOrFail($id);

        return view('homework.show', compact('homework'));
    }

    public function edit($id)
    {
        $homework = Homework::with('creator')->findOrFail($id);

        return view('homework.edit', compact('homework'));
    }

    public function update(Request $request, $id)
    {
        $homework = Homework::findOrFail($id);

        $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject'     => 'nullable|string|max:255',
            'class_name'  => 'nullable|string|max:255',
            'due_date'    => 'nullable|date',
        ]);

        $homework->update($request->only(['title', 'description', 'subject', 'class_name', 'due_date']));

        Flash::success('Homework updated successfully.');

        return redirect()->route('homework.index');
    }

    public function destroy($id)
    {
        Homework::findOrFail($id)->delete();

        Flash::success('Homework deleted.');

        return redirect()->route('homework.index');
    }
}