<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;

class AcademicDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:academics.view');
    }

    public function index()
    {
        $user = auth()->user();

        // Non-managers (e.g. Teachers) only see their own slice of the dashboard.
        $isManager = $user->hasPermission('academics.settings.manage');

        $currentYear = AcademicYear::where('is_current', true)->first();
        $currentYearId = $currentYear ? $currentYear->academic_year_id : null;

        $stats = [
            'total_classes' => SchoolClass::count(),
            'total_subjects' => Subject::count(),
            'total_teachers' => Staff::where('staff_type', 'teaching')->count(),
            'total_students' => Student::where('status', 'active')->count(),
            'total_classrooms' => Classroom::count(),
        ];

        // Today's lessons
        $today = strtolower(now()->format('l'));
        $todayLessons = Timetable::with(['subject', 'teacher', 'classSection.class', 'classroom'])
            ->where('day_of_week', $today)
            ->when($currentYearId, function($q) use ($currentYearId) {
                return $q->where('academic_year_id', $currentYearId);
            })
            ->when(!$isManager, function ($q) use ($user) {
                $staff = Staff::where('user_id', $user->id)->first();
                if (!$staff) {
                    return $q->whereRaw('1 = 0');
                }
                return $q->where('teacher_id', $staff->staff_id);
            })
            ->get();

        // Classroom utilization (Simple calculation)
        // For each classroom, check how many periods it's occupied today
        $totalPeriods = \App\Models\Period::count();
        $utilization = [];
        if ($isManager && $totalPeriods > 0) {
            $classrooms = Classroom::all();
            foreach ($classrooms as $room) {
                $occupied = $todayLessons->where('classroom_id', $room->classroom_id)->count();
                $rate = ($occupied / $totalPeriods) * 100;
                if ($rate > 0) {
                    $utilization[] = [
                        'room' => $room->room_number,
                        'rate' => round($rate, 1)
                    ];
                }
            }
        }

        // Recent Timetable changes
        $recentChanges = $isManager
            ? Timetable::with(['subject', 'classSection.class'])
                ->orderBy('updated_at', 'desc')
                ->take(5)
                ->get()
            : collect();

        return view('academic_dashboard.index', compact('stats', 'todayLessons', 'utilization', 'recentChanges', 'currentYear', 'isManager'));
    }
}
