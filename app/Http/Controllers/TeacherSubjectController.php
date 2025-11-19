<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTeacherSubjectRequest;
use App\Http\Requests\UpdateTeacherSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TeacherSubjectRepository;
use Illuminate\Http\Request;
use Flash;

// Add these model imports for dropdowns
use App\Models\Staff;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\AcademicYear;

class TeacherSubjectController extends AppBaseController
{
    /** @var TeacherSubjectRepository $teacherSubjectRepository*/
    private $teacherSubjectRepository;

    public function __construct(TeacherSubjectRepository $teacherSubjectRepo)
    {
        $this->teacherSubjectRepository = $teacherSubjectRepo;
    }

    /**
     * Display a listing of the TeacherSubject.
     */
    public function index(Request $request)
    {
        $teacherSubjects = $this->teacherSubjectRepository->paginate(10);

        return view('teacher_subjects.index')
            ->with('teacherSubjects', $teacherSubjects);
    }

    /**
     * Show the form for creating a new TeacherSubject.
     */
    public function create()
    {
        // Get dropdown data
        $dropdownData = $this->getDropdownData();
        
        return view('teacher_subjects.create', $dropdownData);
    }

    /**
     * Store a newly created TeacherSubject in storage.
     */
    public function store(CreateTeacherSubjectRequest $request)
    {
        $input = $request->all();

        $teacherSubject = $this->teacherSubjectRepository->create($input);

        Flash::success('Teacher Subject saved successfully.');

        return redirect(route('teacher-subjects.index'));
    }

    /**
     * Display the specified TeacherSubject.
     */
    public function show($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacher-subjects.index'));
        }

        return view('teacher_subjects.show')->with('teacherSubject', $teacherSubject);
    }

    /**
     * Show the form for editing the specified TeacherSubject.
     */
    public function edit($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacher-subjects.index'));
        }

        // Get dropdown data for edit form
        $dropdownData = $this->getDropdownData();
        $dropdownData['teacherSubject'] = $teacherSubject;

        return view('teacher_subjects.edit', $dropdownData);
    }

    /**
     * Update the specified TeacherSubject in storage.
     */
    public function update($id, UpdateTeacherSubjectRequest $request)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacherSubjects.index'));
        }

        $teacherSubject = $this->teacherSubjectRepository->update($request->all(), $id);

        Flash::success('Teacher Subject updated successfully.');

        return redirect(route('teacher-subjects.index'));
    }

    /**
     * Remove the specified TeacherSubject from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacherSubjects.index'));
        }

        $this->teacherSubjectRepository->delete($id);

        Flash::success('Teacher Subject deleted successfully.');

        return redirect(route('teacher-subjects.index'));
    }

/**
     * Get dropdown data for forms (Simplified approach)
     */
    private function getDropdownData()
    {
        // Staff dropdown with concatenated names
        $staff = Staff::select('staff_id', 'first_name', 'middle_name', 'last_name')->get();
        $staffList = collect(['Select Staff']);
        foreach ($staff as $member) {
            $fullName = trim($member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name);
            $staffList[$member->staff_id] = $fullName;
        }

        // Class Section dropdown - you might want to show IDs or create a simple format
        $classSections = ClassSection::all();
        $classSectionList = collect(['Select Class Section']);
        foreach ($classSections as $cs) {
            // Simple format: "Class ID - Section ID" or just use the ID
            $classSectionList[$cs->class_section_id] = "Class {$cs->class_id} - Section {$cs->section_id}";
        }

        return [
            'staffList' => $staffList,
            'subjectList' => Subject::pluck('name', 'subject_id')->prepend('Select Subject', ''),
            'classSectionList' => $classSectionList,
            'academicYearList' => AcademicYear::pluck('name', 'academic_year_id')->prepend('Select Academic Year', '')
        ];
    }
}