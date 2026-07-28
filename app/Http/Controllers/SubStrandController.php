<?php

namespace App\Http\Controllers;

use App\Models\CbcSubStrand;
use App\Models\CbcStrand;
use Illuminate\Http\Request;
use Flash;

class SubStrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    public function index()
    {
        $subStrands = CbcSubStrand::with(['strand.learningArea'])->paginate(15);
        return view('cbc.sub_strands.index', compact('subStrands'));
    }

    public function create()
    {
        $strands = CbcStrand::with('learningArea')->get()->mapWithKeys(function ($item) {
            return [$item->id => ($item->learningArea->name ?? '') . ' > ' . $item->name];
        });
        return view('cbc.sub_strands.create', compact('strands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'strand_id' => 'required|exists:cbc_strands,id',
            'name' => 'required|string|max:200',
        ]);

        CbcSubStrand::create($request->all());
        Flash::success('Sub-Strand saved successfully.');
        return redirect(route('sub-strands.index'));
    }

    public function edit($id)
    {
        $subStrand = CbcSubStrand::findOrFail($id);
        $strands = CbcStrand::with('learningArea')->get()->mapWithKeys(function ($item) {
            return [$item->id => ($item->learningArea->name ?? '') . ' > ' . $item->name];
        });
        return view('cbc.sub_strands.edit', compact('subStrand', 'strands'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'strand_id' => 'required|exists:cbc_strands,id',
            'name' => 'required|string|max:200',
        ]);

        $subStrand = CbcSubStrand::findOrFail($id);
        $subStrand->update($request->all());
        Flash::success('Sub-Strand updated successfully.');
        return redirect(route('sub-strands.index'));
    }

    public function destroy($id)
    {
        $subStrand = CbcSubStrand::findOrFail($id);
        $subStrand->delete();
        Flash::success('Sub-Strand deleted successfully.');
        return redirect(route('sub-strands.index'));
    }
}
