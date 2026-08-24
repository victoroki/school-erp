<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\CbcAssessment;
use App\Models\CbcLearningArea;
use App\Models\ClassSection;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Auth;
use Flash;
use DB;

class CompetencyAssessmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $learningAreas = CbcLearningArea::where('status', true)->pluck('name', 'id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        $strands = [];
        $subStrands = [];

        if ($request->filled('learning_area_id')) {
            $strands = \App\Models\CbcStrand::where('learning_area_id', $request->learning_area_id)->pluck('name', 'id');
        }
        if ($request->filled('strand_id')) {
            $subStrands = \App\Models\CbcSubStrand::where('strand_id', $request->strand_id)->pluck('name', 'id');
        }

        $students = [];
        $existingAssessments = [];

        if ($request->filled(['learning_area_id', 'class_section_id'])) {
            $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id)
                  ->where('status', 'active');
            })->orderBy('last_name')->get();

            $query = CbcAssessment::where('learning_area_id', $request->learning_area_id)
                ->whereIn('student_id', $students->pluck('student_id'));
            
            if ($request->filled('strand_id')) {
                $query->where('strand_id', $request->strand_id);
            }
            if ($request->filled('sub_strand_id')) {
                $query->where('sub_strand_id', $request->sub_strand_id);
            }

            $existingAssessments = $query->get()->keyBy('student_id');
        }

        return view('cbc.assessments.index', compact('learningAreas', 'classSections', 'students', 'existingAssessments', 'strands', 'subStrands'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'learning_area_id' => 'required',
            'ratings' => 'required|array',
        ]);

        $learning_area_id = $request->learning_area_id;
        $strand_id = $request->strand_id;
        $sub_strand_id = $request->sub_strand_id;
        $ratings = $request->ratings; // student_id => rating

        DB::transaction(function () use ($learning_area_id, $strand_id, $sub_strand_id, $ratings) {
            foreach ($ratings as $student_id => $rating) {
                if ($rating) {
                    CbcAssessment::updateOrCreate(
                        [
                            'student_id' => $student_id,
                            'learning_area_id' => $learning_area_id,
                            'strand_id' => $strand_id,
                            'sub_strand_id' => $sub_strand_id,
                            'assessment_date' => now()->toDateString(),
                        ],
                        [
                            'rating' => $rating,
                            'assessed_by' => Auth::id(),
                        ]
                    );
                }
            }
        });

        AuditTrail::log('CBC Assessment', 'RECORD', $learning_area_id, null, [
            'learning_area_id' => $learning_area_id,
            'strand_id' => $strand_id,
            'sub_strand_id' => $sub_strand_id,
            'students_assessed' => count(array_filter($ratings)),
        ]);

        Flash::success('CBC Assessments recorded successfully.');
        return redirect()->back()->withInput();
    }
}
