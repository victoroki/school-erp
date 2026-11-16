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
        $validated = $request->validate([
            'from_class_section_id' => 'required|exists:class_sections,class_section_id',
            'to_class_section_id' => 'required|exists:class_sections,class_section_id',
            'academic_year_id' => 'required|exists:academic_years,academic_year_id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'integer|exists:students,student_id',
        ]);

        $fromClassSectionId = (int) $validated['from_class_section_id'];
        $toClassSectionId = (int) $validated['to_class_section_id'];
        $academicYearId = (int) $validated['academic_year_id'];

        // The target placement has to be internally consistent before anything
        // moves. A class-section belongs to exactly one academic year, so an
        // enrollment whose academic_year disagreed with its class-section's year
        // would be invisible to every year-scoped report and attendance sheet.
        if ((int) ClassSection::findOrFail($toClassSectionId)->academic_year_id !== $academicYearId) {
            Flash::error('The target class and stream belong to a different academic year than the one selected.');

            return redirect()->route('student-promotion.index', ['from_class_section_id' => $fromClassSectionId]);
        }

        // The same learner named twice in one submission used to create two
        // enrollments for the same year.
        $studentIds = array_values(array_unique(array_map('intval', $validated['student_ids'])));

        $promoted = [];
        $skipped = [];

        DB::beginTransaction();
        try {
            foreach ($studentIds as $studentId) {
                // The learner must actually be sitting in the class the user
                // selected. `from_class_section_id` was validated as required and
                // then ignored — the deactivation keyed on student_id alone — so a
                // crafted POST could promote a learner out of an unrelated class.
                $current = StudentClassEnrollment::where('student_id', $studentId)
                    ->where('is_current', true)
                    ->first();

                if (! $current || (int) $current->class_section_id !== $fromClassSectionId) {
                    $skipped[] = 'student ' . $studentId . ' is not currently in the selected class';

                    continue;
                }

                // Idempotency. Re-running the same promotion must not append
                // another row: without this check the second run deactivated the
                // row the first run had just created and inserted a third.
                $alreadyPlaced = StudentClassEnrollment::where('student_id', $studentId)
                    ->where('academic_year_id', $academicYearId)
                    ->where('class_section_id', $toClassSectionId)
                    ->exists();

                if ($alreadyPlaced) {
                    $skipped[] = 'student ' . $studentId . ' is already placed in the target class for that year';

                    continue;
                }

                // History-preserving move: the old row is closed, never overwritten.
                $current->update(['is_current' => false, 'status' => 'completed']);

                $new = StudentClassEnrollment::create([
                    'student_id' => $studentId,
                    'class_section_id' => $toClassSectionId,
                    'academic_year_id' => $academicYearId,
                    'is_current' => true,
                    'enrollment_date' => now(),
                    'status' => 'active',
                ]);

                $promoted[] = [
                    'student_id' => $studentId,
                    'from_enrollment_id' => $current->enrollment_id,
                    'from_class_section_id' => $fromClassSectionId,
                    'to_enrollment_id' => $new->enrollment_id,
                    'to_class_section_id' => $toClassSectionId,
                    'academic_year_id' => $academicYearId,
                ];
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error('Error promoting students: ' . $e->getMessage());

            return redirect()->route('student-promotion.index', ['from_class_section_id' => $fromClassSectionId]);
        }

        // One entry per learner, with the learner as the record. A single entry
        // carrying only a count cannot answer "which class did this child move
        // to, and when?" — which is the question a promotion audit has to answer.
        foreach ($promoted as $row) {
            AuditTrail::log('Student', 'PROMOTE', $row['student_id'], null, $row);
        }

        if ($promoted !== []) {
            Flash::success(count($promoted) . ' students promoted successfully.');
        }

        if ($skipped !== []) {
            Flash::warning(
                count($skipped) . ' student(s) skipped — ' . implode('; ', array_slice($skipped, 0, 5))
                    . (count($skipped) > 5 ? '; and ' . (count($skipped) - 5) . ' more' : '') . '.'
            );
        }

        return redirect()->route('student-promotion.index', ['from_class_section_id' => $fromClassSectionId]);
    }
}
