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
    }

    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();
        $selectedAcademicYearId = $request->get('academic_year_id');
        
        if (!$selectedAcademicYearId && $academicYears->count() > 0) {
            $current = $academicYears->firstWhere('is_current', true);
            $selectedAcademicYearId = $current ? $current->academic_year_id : $academicYears->first()->academic_year_id;
        }

        // Get all teaching staff
        $teachers = Staff::where('staff_type', 'teaching')
            ->active()
            ->with(['department'])
            ->get();

        // Get workload data
        $workloadData = [];
        $timetables = Timetable::where('academic_year_id', $selectedAcademicYearId)->get();

        foreach ($teachers as $teacher) {
            $teacherLessons = $timetables->where('teacher_id', $teacher->staff_id);
            $count = $teacherLessons->count();
            
            // Group by day to see distribution
            $distribution = $teacherLessons->groupBy('day_of_week')->map->count();
            
            // Max lessons in a single day
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

        // Sort by workload (highest first)
        usort($workloadData, function($a, $b) {
            return $b['total_periods'] <=> $a['total_periods'];
        });

        return view('teacher_workload.index', [
            'workloadData' => $workloadData,
            'academicYears' => $academicYears,
            'selectedAcademicYearId' => $selectedAcademicYearId
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
