<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentAttendance;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Flash;

class StudentAttendanceController extends Controller
{
    private TeacherScopeService $teacherScope;

    public function __construct(TeacherScopeService $teacherScope)
    {
        $this->teacherScope = $teacherScope;
        $this->middleware('auth');
        // `report` was named in neither list, so the attendance report — which
        // prints every learner's daily attendance for a class — had no permission
        // check at all and answered 200 to any authenticated user.
        $this->middleware('can:academics.view')->only(['index', 'show', 'report']);
        $this->middleware('can:academics.attendance.manage')->only(['store', 'update']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if ($hasSettings) {
            $classSections = ClassSection::with(['schoolClass', 'section'])->get();
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $classSections = ClassSection::with(['schoolClass', 'section'])
                ->whereIn('class_section_id', $classSectionIds)
                ->get();
        }

        $date = $request->get('date', date('Y-m-d'));
        $classSectionId = $request->get('class_section_id');

        if ($classSectionId) {
            if (!$hasSettings && !$this->teacherScope->getClassSectionIds($user)->contains((int) $classSectionId)) {
                Flash::error('You are not authorized to view attendance for this class.');
                return redirect()->route('student-attendance.index');
            }

            $students = Student::whereHas('studentClassEnrollments', function ($query) use ($classSectionId) {
                $query->where('class_section_id', $classSectionId)->where('is_current', true);
            })->get();

            $attendanceData = StudentAttendance::where('class_section_id', $classSectionId)
                ->whereDate('date', $date)
                ->get()
                ->keyBy('student_id');
            
            return view('student_attendance.mark', compact('classSections', 'students', 'attendanceData', 'date', 'classSectionId'));
        }

        return view('student_attendance.index', compact('classSections', 'date'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'class_section_id' => 'required|exists:class_sections,class_section_id',
            'date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        $user = auth()->user();
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            if (!$allowedIds->contains((int) $request->class_section_id)) {
                Flash::error('You are not authorized to mark attendance for this class.');
                return redirect()->back();
            }
        }

        $classSectionId = $request->class_section_id;
        $date = $request->date;
        // marked_by is foreign-keyed to staff.staff_id, NOT users.id. Writing the
        // user id here either violated the FK (for a user with no matching staff
        // row) or - because users.id and staff.staff_id are separate sequences
        // that overlap - silently attributed the register to whichever staff
        // member happened to own that id. Mobile attendance already used
        // $staff?->staff_id; this is the web path being brought into line.
        $markedBy = auth()->user()?->staff?->staff_id;

        foreach ($request->attendance as $studentId => $status) {
            // Keyed on (student_id, date), matching the mobile app and the
            // student_attendance_student_date_unique index. Including
            // class_section_id in the key meant a learner marked on the web and
            // again in the app produced two rows for the same day, and the app's
            // row carried a NULL class_section_id so it also vanished from
            // class-filtered views.
            $period = StudentAttendance::resolvePeriodFor((int) $studentId, $date);

            StudentAttendance::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'date' => $date,
                ],
                [
                    'class_section_id' => $classSectionId,
                    'academic_year_id' => $period['academic_year_id'],
                    'term_id' => $period['term_id'],
                    'status' => $status,
                    'marked_by' => $markedBy,
                    'remarks' => $request->remarks[$studentId] ?? null,
                ]
            );
        }

        AuditTrail::log('Attendance', 'MARK', $classSectionId, null, [
            'class_section_id' => $classSectionId,
            'date' => $date,
            'records' => count($request->attendance),
        ]);

        Flash::success('Attendance marked successfully for ' . $date);
        return redirect()->back();
    }

    public function report(Request $request)
    {
        $user = auth()->user();
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if ($hasSettings) {
            $classSections = ClassSection::with(['schoolClass', 'section'])->get();
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $classSections = ClassSection::with(['schoolClass', 'section'])
                ->whereIn('class_section_id', $classSectionIds)
                ->get();
        }

        $classSectionId = $request->get('class_section_id');
        $month = $request->get('month', date('m'));
        $year = $request->get('year', date('Y'));

        if ($classSectionId) {
            if (!$hasSettings && !$this->teacherScope->getClassSectionIds($user)->contains((int) $classSectionId)) {
                Flash::error('You are not authorized to view attendance for this class.');
                return redirect()->route('student-attendance.report');
            }

            $students = Student::whereHas('studentClassEnrollments', function ($query) use ($classSectionId) {
                $query->where('class_section_id', $classSectionId)->where('is_current', true);
            })->with(['studentAttendances' => function($q) use ($month, $year) {
                $q->whereMonth('date', $month)->whereYear('date', $year);
            }])->get();

            return view('student_attendance.report', compact('classSections', 'students', 'classSectionId', 'month', 'year'));
        }

        return view('student_attendance.report_filter', compact('classSections'));
    }
}
