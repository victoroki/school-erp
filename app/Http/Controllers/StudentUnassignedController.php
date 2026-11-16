<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\AuditTrail;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Flash;

/**
 * Bulk class assignment for students who have no current class enrollment
 * (missed at admission, or imported/legacy students). Writes the same
 * StudentClassEnrollment records that admission and the Class Enrollments
 * screen create, so assignments here show up everywhere else in the app.
 */
class StudentUnassignedController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.manage');
    }

    /**
     * List students with no current class enrollment.
     */
    public function index()
    {
        $students = Student::with(['studentClassEnrollments.classSection.schoolClass', 'studentClassEnrollments.classSection.section'])
            ->where('status', 'active')
            ->whereDoesntHave('studentClassEnrollments', function ($query) {
                $query->where('is_current', true);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $classSections = ClassSection::with(['schoolClass', 'section', 'academicYear'])
            ->get()
            ->mapWithKeys(function ($cs) {
                $name = ($cs->schoolClass && $cs->schoolClass->name ? $cs->schoolClass->name : 'Class')
                    . ' - ' . ($cs->section && $cs->section->name ? $cs->section->name : 'Section')
                    . ' (' . ($cs->academicYear && $cs->academicYear->name ? $cs->academicYear->name : 'Year') . ')';
                return [$cs->class_section_id => $name];
            })
            ->toArray();

        $academicYears = AcademicYear::orderBy('start_date', 'desc')->pluck('name', 'academic_year_id')->toArray();

        return view('students.unassigned.index', compact('students', 'classSections', 'academicYears'));
    }

    /**
     * Assign the checked students to their chosen class sections.
     *
     * Payload shape: student_ids[] = student_id, assignments[student_id] = class_section_id
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,student_id',
            'assignments' => 'required|array',
            'assignments.*' => 'required|exists:class_sections,class_section_id',
        ]);

        // Every checked student must have a class chosen.
        $missing = collect($request->student_ids)
            ->filter(fn ($id) => empty($request->assignments[$id] ?? null));
        if ($missing->isNotEmpty()) {
            return redirect()->back()
                ->withErrors(['assignments' => 'Every selected student must have a class chosen.'])
                ->withInput();
        }

        $assigned = 0;
        $skipped = 0;

        DB::beginTransaction();
        try {
            foreach ($request->student_ids as $studentId) {
                $classSectionId = $request->assignments[$studentId] ?? null;
                $student = Student::where('student_id', $studentId)->where('status', 'active')->first();

                if (!$student || !$classSectionId) {
                    $skipped++;

                    continue;
                }

                $classSection = ClassSection::find($classSectionId);

                if (!$classSection) {
                    $skipped++;

                    continue;
                }

                // Safety net: if the student somehow gained a current enrollment
                // after the page loaded, retire it so only one is current.
                StudentClassEnrollment::where('student_id', $studentId)
                    ->where('is_current', true)
                    ->update(['is_current' => false]);

                StudentClassEnrollment::create([
                    'student_id' => $studentId,
                    'class_section_id' => $classSectionId,
                    'academic_year_id' => $classSection->academic_year_id,
                    'is_current' => true,
                    'enrollment_date' => now()->toDateString(),
                    'status' => 'active',
                ]);

                $assigned++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Flash::error('Error assigning classes: ' . $e->getMessage());

            return redirect()->route('student-unassigned.index');
        }

        AuditTrail::log('Student Enrollment', 'BULK ASSIGN', null, null, [
            'assignments' => $request->assignments,
            'students_assigned' => $assigned,
            'students_skipped' => $skipped,
        ]);

        if ($assigned > 0) {
            Flash::success($assigned . ' student' . ($assigned === 1 ? '' : 's') . ' assigned to class successfully.');
        } else {
            Flash::warning('No students were assigned.');
        }

        return redirect()->route('student-unassigned.index');
    }
}
