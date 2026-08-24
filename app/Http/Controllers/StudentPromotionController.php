<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClassEnrollment;
use App\Models\SchoolClass;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use DB;

class StudentPromotionController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.manage');
    }

    public function index(Request $request)
    {
        $classes = SchoolClass::all();
        $classSections = ClassSection::with(['schoolClass', 'section'])->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        $fromClassSectionId = $request->get('from_class_section_id');
        $students = [];

        if ($fromClassSectionId) {
            $students = Student::whereHas('studentClassEnrollments', function ($query) use ($fromClassSectionId) {
                $query->where('class_section_id', $fromClassSectionId)->where('is_current', true);
            })->get();
        }

        return view('students.promotion.index', compact('classes', 'classSections', 'academicYears', 'students', 'fromClassSectionId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_class_section_id' => 'required',
            'to_class_section_id' => 'required',
            'academic_year_id' => 'required',
            'student_ids' => 'required|array',
        ]);

        $studentIds = $request->student_ids;
        $toClassSectionId = $request->to_class_section_id;
        $academicYearId = $request->academic_year_id;

        DB::beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                // Deactivate current enrollment
                StudentClassEnrollment::where('student_id', $studentId)
                    ->where('is_current', true)
                    ->update(['is_current' => false, 'status' => 'completed']);

                // Create new enrollment
                StudentClassEnrollment::create([
                    'student_id' => $studentId,
                    'class_section_id' => $toClassSectionId,
                    'academic_year_id' => $academicYearId,
                    'is_current' => true,
                    'enrollment_date' => now(),
                    'status' => 'active'
                ]);
            }
            DB::commit();
            AuditTrail::log('Student', 'PROMOTE', null, null, [
                'from_class_section_id' => $request->from_class_section_id,
                'to_class_section_id' => $toClassSectionId,
                'academic_year_id' => $academicYearId,
                'students_promoted' => count($studentIds),
            ]);
            Flash::success(count($studentIds) . ' students promoted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error promoting students: ' . $e->getMessage());
        }

        return redirect()->route('student-promotion.index');
    }
}
