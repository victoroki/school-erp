<?php

namespace App\Http\Controllers;

use App\Models\CbcLearningArea;
use App\Models\AuditTrail;
use Database\Seeders\CbeCurriculumSeeder;
use Illuminate\Http\Request;
use Flash;

class LearningAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'seedDefaults']);
    }

    /**
     * Learning areas grouped by education level (Pre-Primary → Junior
     * School), each with its strand / sub-strand counts and quick links.
     */
    public function index(Request $request)
    {
        $query = CbcLearningArea::withCount(['strands'])
            ->with('strands.subStrands');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('q')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', '%' . $request->q . '%')
                ->orWhere('code', 'like', '%' . strtoupper($request->q) . '%'));
        }

        $learningAreas = $query->orderBy('level')->orderBy('name')->get();
        $levels = CbcLearningArea::select('level')->distinct()->orderBy('level')->pluck('level');
        $totalCount = CbcLearningArea::count();

        return view('cbc.learning_areas.index', compact('learningAreas', 'levels', 'totalCount'));
    }

    /**
     * One-click load of the official Kenyan CBE curriculum
     * (learning areas + strands + sub-strands). Idempotent.
     */
    public function seedDefaults()
    {
        $before = CbcLearningArea::count();

        $this->runSeeder();

        $after = CbcLearningArea::count();

        AuditTrail::log('CBE Curriculum', 'IMPORT', null, ['areas_before' => $before], ['areas_after' => $after]);

        Flash::success("Kenyan CBE curriculum loaded — {$after} learning areas now configured (was {$before}).");

        return redirect()->route('learning-areas.index');
    }

    protected function runSeeder(): void
    {
        (new CbeCurriculumSeeder())->run();
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

        $learningArea = CbcLearningArea::create($request->all());
        AuditTrail::log('Learning Area', 'CREATE', $learningArea->id, null, $learningArea->toArray());
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
        $oldData = $learningArea->toArray();
        $learningArea->update($request->all());
        AuditTrail::log('Learning Area', 'UPDATE', $learningArea->id, $oldData, $learningArea->toArray());
        Flash::success('Learning Area updated successfully.');
        return redirect(route('learning-areas.index'));
    }

    public function destroy($id)
    {
        $learningArea = CbcLearningArea::findOrFail($id);
        $oldData = $learningArea->toArray();
        $learningArea->delete();
        AuditTrail::log('Learning Area', 'DELETE', $id, $oldData, null);
        Flash::success('Learning Area deleted successfully.');
        return redirect(route('learning-areas.index'));
    }
}
