<?php

namespace App\Http\Controllers;

use App\Models\CbcStrand;
use App\Models\CbcLearningArea;
use Illuminate\Http\Request;
use Flash;

class StrandController extends Controller
{
    public function index()
    {
        $strands = CbcStrand::with('learningArea')->paginate(15);
        return view('cbc.strands.index', compact('strands'));
    }

    public function create()
    {
        $learningAreas = CbcLearningArea::where('status', true)->pluck('name', 'id');
        return view('cbc.strands.create', compact('learningAreas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'learning_area_id' => 'required|exists:cbc_learning_areas,id',
            'name' => 'required|string|max:200',
        ]);

        CbcStrand::create($request->all());
        Flash::success('Strand saved successfully.');
        return redirect(route('strands.index'));
    }

    public function edit($id)
    {
        $strand = CbcStrand::findOrFail($id);
        $learningAreas = CbcLearningArea::where('status', true)->pluck('name', 'id');
        return view('cbc.strands.edit', compact('strand', 'learningAreas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'learning_area_id' => 'required|exists:cbc_learning_areas,id',
            'name' => 'required|string|max:200',
        ]);

        $strand = CbcStrand::findOrFail($id);
        $strand->update($request->all());
        Flash::success('Strand updated successfully.');
        return redirect(route('strands.index'));
    }

    public function destroy($id)
    {
        $strand = CbcStrand::findOrFail($id);
        $strand->delete();
        Flash::success('Strand deleted successfully.');
        return redirect(route('strands.index'));
    }
}
