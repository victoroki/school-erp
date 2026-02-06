<?php

namespace App\Http\Controllers;

use App\Models\CbcLearningArea;
use Illuminate\Http\Request;
use Flash;

class LearningAreaController extends Controller
{
    public function index()
    {
        $learningAreas = CbcLearningArea::paginate(10);
        return view('cbc.learning_areas.index', compact('learningAreas'));
    }

    public function create()
    {
        return view('cbc.learning_areas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'level' => 'required',
        ]);

        CbcLearningArea::create($request->all());
        Flash::success('Learning Area saved successfully.');
        return redirect(route('learning-areas.index'));
    }

    public function edit($id)
    {
        $learningArea = CbcLearningArea::findOrFail($id);
        return view('cbc.learning_areas.edit', compact('learningArea'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'level' => 'required',
        ]);

        $learningArea = CbcLearningArea::findOrFail($id);
        $learningArea->update($request->all());
        Flash::success('Learning Area updated successfully.');
        return redirect(route('learning-areas.index'));
    }

    public function destroy($id)
    {
        $learningArea = CbcLearningArea::findOrFail($id);
        $learningArea->delete();
        Flash::success('Learning Area deleted successfully.');
        return redirect(route('learning-areas.index'));
    }
}
