<?php

namespace App\Http\Controllers;

use App\Models\ClassSection;
use App\Models\Staff;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Flash;

class ClassTeacherController extends Controller
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

        $classSections = ClassSection::with(['class', 'section', 'classTeacher'])
            ->where('academic_year_id', $selectedAcademicYearId)
            ->get();

        $teachers = Staff::where('staff_type', 'teaching')
            ->where('status', 'active')
            ->orderBy('first_name')
            ->get();

        return view('class_teachers.index', compact('classSections', 'teachers', 'academicYears', 'selectedAcademicYearId'));
    }

    public function update(Request $request, $id)
    {
        $classSection = ClassSection::findOrFail($id);
        $classSection->class_teacher_id = $request->get('teacher_id');
        $classSection->save();

        Flash::success('Class Teacher updated successfully.');
        return redirect(route('class-teachers.index'));
    }
}
