<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\Timetable;
use App\Models\AcademicYear;
use Illuminate\Http\Request;

class TeacherWorkloadController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:academics.view');
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $selectedAcademicYearId = $request->get('academic_year_id');
        $selectedDepartmentId = $request->get('department_id');
        
        if (!$selectedAcademicYearId && $academicYears->count() > 0) {
            $current = $academicYears->firstWhere('is_current', true);
            $selectedAcademicYearId = $current ? $current->academic_year_id : $academicYears->first()->academic_year_id;
        }

        // Get departments for filter
        $departments = \App\Models\Department::pluck('name', 'department_id')->prepend('All Departments', '');

        // Get teaching staff with filters
        $teacherQuery = Staff::where('staff_type', 'teaching')
            ->active()
            ->with(['department', 'jobPosition']);

        if ($selectedDepartmentId) {
            $teacherQuery->where('department_id', $selectedDepartmentId);
        }

        $allTeachers = $teacherQuery->get();
        $totalEvaluated = $allTeachers->count();

        // Workload for ALL teachers (needed for summary stats)
        $timetables = Timetable::where('academic_year_id', $selectedAcademicYearId)->get();
        
        $tempWorkload = [];
        foreach ($allTeachers as $teacher) {
            $count = $timetables->where('teacher_id', $teacher->staff_id)->count();
            $tempWorkload[] = [
                'count' => $count,
                'est_hours' => round($count * 0.75, 1)
            ];
        }

        $stats = [
            'total' => $totalEvaluated,
            'overloaded' => collect($tempWorkload)->where('count', '>', 30)->count(),
            'optimum' => collect($tempWorkload)->whereBetween('count', [15, 30])->count(),
            'avg_hours' => round(collect($tempWorkload)->avg('est_hours'), 1)
        ];

        // Pagination
        $page = $request->get('page', 1);
        $perPage = 10;
        $paginatedTeachers = $teacherQuery->paginate($perPage);

        // Process workload ONLY for paginated teachers for better performance
        $workloadData = [];
        foreach ($paginatedTeachers as $teacher) {
            $teacherLessons = $timetables->where('teacher_id', $teacher->staff_id);
            $count = $teacherLessons->count();
            $distribution = $teacherLessons->groupBy('day_of_week')->map->count();
            $maxDaily = $distribution->max() ?: 0;

            $workloadData[] = [
                'teacher' => $teacher,
                'total_periods' => $count,
                'est_hours' => round($count * 0.75, 1), 
                'max_daily' => $maxDaily,
                'distribution' => $distribution,
                'status' => $this->getWorkloadStatus($count)
            ];
        }

        // Sort the current page slice? If we want to sort the whole list we'd need a different approach, 
        // but for workload analytics, usually people want to see all.
        // I'll keep it sorted by total periods for the current page slice or just return as is.
        usort($workloadData, function($a, $b) {
            return $b['total_periods'] <=> $a['total_periods'];
        });

        return view('teacher_workload.index', [
            'paginator' => $paginatedTeachers,
            'workloadData' => $workloadData,
            'academicYears' => $academicYears,
            'departments' => $departments,
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedDepartmentId' => $selectedDepartmentId,
            'stats' => $stats
        ]);
    }

    private function getWorkloadStatus($count)
    {
        if ($count > 30) return ['label' => 'Overloaded', 'class' => 'danger'];
        if ($count > 15) return ['label' => 'Standard', 'class' => 'success'];
        if ($count > 0) return ['label' => 'Underloaded', 'class' => 'warning'];
        return ['label' => 'No Load', 'class' => 'secondary'];
    }
}
