<?php

namespace App\Http\Controllers;

use App\Models\CbcStrand;
use App\Models\CbcLearningArea;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class StrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index(Request $request)
    {
        $query = CbcStrand::with(['learningArea', 'subStrands']);

        if ($request->filled('learning_area_id')) {
            $query->where('learning_area_id', $request->learning_area_id);
        }

        $strands = $query->paginate(15)->withQueryString();
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

        $strand = CbcStrand::create($request->all());
        AuditTrail::log('Strand', 'CREATE', $strand->id, null, $strand->toArray());
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
        $oldData = $strand->toArray();
        $strand->update($request->all());
        AuditTrail::log('Strand', 'UPDATE', $strand->id, $oldData, $strand->toArray());
        Flash::success('Strand updated successfully.');
        return redirect(route('strands.index'));
    }

    public function destroy($id)
    {
        $strand = CbcStrand::findOrFail($id);
        $oldData = $strand->toArray();
        $strand->delete();
        AuditTrail::log('Strand', 'DELETE', $id, $oldData, null);
        Flash::success('Strand deleted successfully.');
        return redirect(route('strands.index'));
    }
}
