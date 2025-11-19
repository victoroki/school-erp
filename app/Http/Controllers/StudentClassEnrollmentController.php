<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentClassEnrollmentRequest;
use App\Http\Requests\UpdateStudentClassEnrollmentRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\ClassSection;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Repositories\StudentClassEnrollmentRepository;
use Illuminate\Http\Request;
use Flash;
use DB;

class StudentClassEnrollmentController extends AppBaseController
{
    /** @var StudentClassEnrollmentRepository $studentClassEnrollmentRepository*/
    private $studentClassEnrollmentRepository;

    public function __construct(StudentClassEnrollmentRepository $studentClassEnrollmentRepo)
    {
        $this->studentClassEnrollmentRepository = $studentClassEnrollmentRepo;
    }

    private function getDropdownData(){
        return [
            'students' => Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', student_id, ')') as full_name")
                ->pluck('full_name', 'student_id')
                ->toArray(),
            'classSections' => ClassSection::with(['class', 'section', 'academicYear'])
                ->get()
                ->pluck('display_name', 'id')
                ->toArray(),
            'academicYears' => AcademicYear::pluck('name', 'academic_year_id')//where('status', 'active')
                ->toArray(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
                'transferred' => 'Transferred',
                'graduated' => 'Graduated',
                'dropped' => 'Dropped'
            ]
        ];
    }

    /**
     * Display a listing of the StudentClassEnrollment.
     */
    public function index(Request $request)
    {
        $studentClassEnrollments = $this->studentClassEnrollmentRepository->paginate(10);

        return view('student_class_enrollments.index')
            ->with('studentClassEnrollments', $studentClassEnrollments);
    }

    /**
     * Show the form for creating a new StudentClassEnrollment.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('student_class_enrollments.create', $dropdownData);
    }

    /**
     * Store a newly created StudentClassEnrollment in storage.
     */
    public function store(CreateStudentClassEnrollmentRequest $request)
    {
        $input = $request->all();

        $studentClassEnrollment = $this->studentClassEnrollmentRepository->create($input);

        Flash::success('Student Class Enrollment saved successfully.');

        return redirect(route('student-class-enrollments.index'));
    }

    /**
     * Display the specified StudentClassEnrollment.
     */
    public function show($id)
    {
        $studentClassEnrollment = $this->studentClassEnrollmentRepository->find($id);

        if (empty($studentClassEnrollment)) {
            Flash::error('Student Class Enrollment not found');

            return redirect(route('student-class-enrollments.index'));
        }

        return view('student_class_enrollments.show')->with('studentClassEnrollment', $studentClassEnrollment);
    }

    /**
     * Show the form for editing the specified StudentClassEnrollment.
     */
    public function edit($id)
    {
        $studentClassEnrollment = $this->studentClassEnrollmentRepository->find($id);

        if (empty($studentClassEnrollment)) {
            Flash::error('Student Class Enrollment not found');

            return redirect(route('student-class-enrollments.index'));
        }

        $dropdownData = $this->getDropdownData();

        return view('student_class_enrollments.edit')
            ->with('studentClassEnrollment', $studentClassEnrollment)
            ->with($dropdownData);
    }

    /**
     * Update the specified StudentClassEnrollment in storage.
     */
    public function update($id, UpdateStudentClassEnrollmentRequest $request)
    {
        $studentClassEnrollment = $this->studentClassEnrollmentRepository->find($id);

        if (empty($studentClassEnrollment)) {
            Flash::error('Student Class Enrollment not found');

            return redirect(route('student-class-enrollments.index'));
        }

        $studentClassEnrollment = $this->studentClassEnrollmentRepository->update($request->all(), $id);

        Flash::success('Student Class Enrollment updated successfully.');

        return redirect(route('student-class-enrollments.index'));
    }

    /**
     * Remove the specified StudentClassEnrollment from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $studentClassEnrollment = $this->studentClassEnrollmentRepository->find($id);

        if (empty($studentClassEnrollment)) {
            Flash::error('Student Class Enrollment not found');

            return redirect(route('student-class-enrollments.index'));
        }

        $this->studentClassEnrollmentRepository->delete($id);

        Flash::success('Student Class Enrollment deleted successfully.');

        return redirect(route('student-class-enrollments.index'));
    }
}