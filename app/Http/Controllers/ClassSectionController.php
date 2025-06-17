<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClassSectionRequest;
use App\Http\Requests\UpdateClassSectionRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ClassSectionRepository;
use Illuminate\Http\Request;
use Flash;

class ClassSectionController extends AppBaseController
{
    /** @var ClassSectionRepository $classSectionRepository*/
    private $classSectionRepository;

    public function __construct(ClassSectionRepository $classSectionRepo)
    {
        $this->classSectionRepository = $classSectionRepo;
    }

    /**
     * Display a listing of the ClassSection.
     */
    public function index(Request $request)
    {
        $classSections = $this->classSectionRepository->paginate(10);

        return view('class_sections.index')
            ->with('classSections', $classSections);
    }

    /**
     * Show the form for creating a new ClassSection.
     */
    public function create()
    {
        // Get all dropdown data
        $academicYears = \App\Models\AcademicYear::pluck('name', 'academic_year_id');
        $classes = \App\Models\SchoolClass::pluck('name', 'class_id');
        $sections = \App\Models\Section::pluck('name', 'section_id');
        $classrooms = \App\Models\Classroom::pluck('room_number', 'classroom_id');
        $teachers = \App\Models\Staff::where('staff_type', 'teacher') // or 'is_teaching', true, etc.
        ->get()
        ->mapWithKeys(function ($staff) {
            return [$staff->staff_id => $staff->first_name . ' ' . $staff->last_name];
        });
        
        return view('class_sections.create')
            ->with('academicYears', $academicYears)
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->with('classrooms', $classrooms)
            ->with('teachers', $teachers);
    }

    /**
     * Store a newly created ClassSection in storage.
     */
    public function store(CreateClassSectionRequest $request)
    {
        $input = $request->all();

        $classSection = $this->classSectionRepository->create($input);

        Flash::success('Class Section saved successfully.');

        return redirect(route('classSections.index'));
    }

    /**
     * Display the specified ClassSection.
     */
    public function show($id)
    {
        $classSection = $this->classSectionRepository->find($id);

        if (empty($classSection)) {
            Flash::error('Class Section not found');

            return redirect(route('classSections.index'));
        }

        return view('class_sections.show')->with('classSection', $classSection);
    }

    /**
     * Show the form for editing the specified ClassSection.
     */
    public function edit($id)
    {
        $classSection = $this->classSectionRepository->find($id);

        if (empty($classSection)) {
            Flash::error('Class Section not found');

            return redirect(route('classSections.index'));
        }

        // Get all dropdown data for edit form
        $academicYears = \App\Models\AcademicYear::pluck('name', 'academic_year_id');
        $classes = \App\Models\SchoolClass::pluck('name', 'class_id');
        $sections = \App\Models\Section::pluck('name', 'section_id');
        $classrooms = \App\Models\Classroom::pluck('room_number', 'classroom_id');
        $teachers = \App\Models\Staff::where('staff_type', 'teacher') // or 'is_teaching', true, etc.
        ->get()
        ->mapWithKeys(function ($staff) {
            return [$staff->staff_id => $staff->first_name . ' ' . $staff->last_name];
        });

        return view('class_sections.edit')
            ->with('classSection', $classSection)
            ->with('academicYears', $academicYears)
            ->with('classes', $classes)
            ->with('sections', $sections)
            ->with('classrooms', $classrooms)
            ->with('teachers', $teachers);
    }

    /**
     * Update the specified ClassSection in storage.
     */
    public function update($id, UpdateClassSectionRequest $request)
    {
        $classSection = $this->classSectionRepository->find($id);

        if (empty($classSection)) {
            Flash::error('Class Section not found');

            return redirect(route('classSections.index'));
        }

        $classSection = $this->classSectionRepository->update($request->all(), $id);

        Flash::success('Class Section updated successfully.');

        return redirect(route('classSections.index'));
    }

    /**
     * Remove the specified ClassSection from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $classSection = $this->classSectionRepository->find($id);

        if (empty($classSection)) {
            Flash::error('Class Section not found');

            return redirect(route('classSections.index'));
        }

        $this->classSectionRepository->delete($id);

        Flash::success('Class Section deleted successfully.');

        return redirect(route('classSections.index'));
    }
}