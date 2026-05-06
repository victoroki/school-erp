<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StudentRepository;
use Illuminate\Http\Request;
use Flash;

use App\Models\Student;
use App\Models\ClassSection;
use App\Models\AcademicYear;

class StudentController extends AppBaseController
{
    /** @var StudentRepository $studentRepository*/
    private $studentRepository;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepository = $studentRepo;
    }

    /**
     * Display a listing of the Student.
     */
    public function index(Request $request)
    {
        $query = Student::query();

        // Advanced Search & Filters
        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function($sub) use ($q) {
                $sub->where('first_name', 'like', "%$q%")
                    ->orWhere('middle_name', 'like', "%$q%")
                    ->orWhere('last_name', 'like', "%$q%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$q%"])
                    ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) LIKE ?", ["%$q%"])
                    ->orWhere('admission_no', 'like', "%$q%")
                    ->orWhere('nemis_number', 'like', "%$q%")
                    ->orWhere('upi_number', 'like', "%$q%")
                    ->orWhere('roll_number', 'like', "%$q%");
            });
        }

        if ($request->filled('class_section_id')) {
            $classSectionId = $request->get('class_section_id');
            $query->whereHas('studentClassEnrollments', function($q) use ($classSectionId) {
                $q->where('class_section_id', $classSectionId)->where('is_current', true);
            });
        }

        if ($request->filled('academic_year_id')) {
            $academicYearId = $request->get('academic_year_id');
            $query->whereHas('studentClassEnrollments', function($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId);
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->get('gender'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        
        if ($request->filled('enrollment_status')) {
            $query->where('enrollment_status', $request->get('enrollment_status'));
        }

        $students = $query->with([
            'studentClassEnrollments.classSection.schoolClass', 
            'studentClassEnrollments.classSection.section',
            'studentClassEnrollments.academicYear'
        ])
        ->orderBy('created_at', 'desc')
        ->paginate(10)
        ->appends($request->all());

        $classSections = ClassSection::with(['schoolClass', 'section'])->get();
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        return view('students.index', compact('students', 'classSections', 'academicYears'));
    }

    /**
     * Show the form for creating a new Student.
     */
    public function create()
    {
        $classSections = ClassSection::with(['schoolClass', 'section', 'academicYear'])
            ->get()
            ->mapWithKeys(function ($cs) {
                $name = ($cs->schoolClass && $cs->schoolClass->name ? $cs->schoolClass->name : 'Class')
                    . ' - ' . ($cs->section && $cs->section->name ? $cs->section->name : 'Section')
                    . ' (' . ($cs->academicYear && $cs->academicYear->name ? $cs->academicYear->name : 'Year') . ')';
                return [$cs->class_section_id => $name];
            })
            ->toArray();
        $academicYears = AcademicYear::pluck('name', 'academic_year_id')->toArray();

        return view('students.create', compact('classSections', 'academicYears'));
    }

    /**
     * Store a newly created Student in storage.
     */
    public function store(CreateStudentRequest $request)
    {
        $input = $request->all();

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('students/photos'), $photoName);
            $input['photo_url'] = 'students/photos/' . $photoName;
        }

        $student = $this->studentRepository->create($input);

        // Auto-enroll if class section is provided
        if ($request->filled('class_section_id')) {
            \App\Models\StudentClassEnrollment::create([
                'student_id' => $student->student_id,
                'class_section_id' => $request->class_section_id,
                'academic_year_id' => $request->academic_year_id,
                'roll_number' => $request->input('roll_number_enrollment'), // separate name in fields to avoid confusion with student's own roll number
                'enrollment_date' => $request->admission_date ?? now(),
                'status' => 'active',
                'is_current' => true
            ]);
        }

        Flash::success('Student saved successfully.');

        return redirect(route('students.index'));
    }

    public function show($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        // Eager load all necessary relationships
        $student->load([
            'studentDocuments',
            'studentClassEnrollments.classSection.schoolClass',
            'studentClassEnrollments.classSection.section',
            'studentClassEnrollments.academicYear',
            'parents',
            'siblings',
            'feeStructures',
            'payments.collectedBy',
            'studentAttendances',
            'transportRegistrations',
            'hostelAllocations',
            'disciplinaryRecords.reporter',
            'medicalIncidents.marker'
        ]);

        return view('students.show')->with('student', $student);
    }

    /**
     * Show the form for editing the specified Student.
     */
    public function edit($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        return view('students.edit')->with('student', $student);
    }

    /**
     * Update the specified Student in storage.
     */
    public function update($id, UpdateStudentRequest $request)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        $input = $request->all();

        if ($request->hasFile('photo')) {
            // Optional: Delete old photo if it exists
            if ($student->photo_url && file_exists(public_path($student->photo_url))) {
                unlink(public_path($student->photo_url));
            }

            $photo = $request->file('photo');
            $photoName = time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('students/photos'), $photoName);
            $input['photo_url'] = 'students/photos/' . $photoName;
        }


        $student = $this->studentRepository->update($input, $id);

        Flash::success('Student updated successfully.');

        return redirect(route('students.index'));
    }

    /**
     * Remove the specified Student from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }
        
        if ($student->photo_url && file_exists(public_path($student->photo_url))) {
            unlink(public_path($student->photo_url));
        }

        $this->studentRepository->delete($id);

        Flash::success('Student deleted successfully.');

        return redirect(route('students.index'));
    }

    public function addSibling(Request $request, $id)
    {
        $student = $this->studentRepository->find($id);

        if (empty($student)) {
            Flash::error('Student not found');
            return redirect(route('students.index'));
        }

        $request->validate([
            'sibling_id' => 'required|exists:students,student_id|different:'.$id,
            'relationship_type' => 'required|string|max:50',
        ]);

        $siblingId = $request->input('sibling_id');

        // Check if relationship already exists
        if (!$student->siblings->contains($siblingId)) {
            $student->siblings()->attach($siblingId, [
                'relationship_type' => $request->input('relationship_type'),
                'is_twin' => $request->has('is_twin'),
                'notes' => $request->input('notes')
            ]);
            
            // Siblings are reciprocal
            $sibling = Student::find($siblingId);
            $sibling->siblings()->attach($id, [
                'relationship_type' => $request->input('relationship_type') === 'brother' ? 'brother' : ($request->input('relationship_type') === 'sister' ? 'sister' : 'sibling'), 
                'is_twin' => $request->has('is_twin'),
                'notes' => $request->input('notes')
            ]);

            Flash::success('Sibling added successfully.');
        } else {
            Flash::warning('This student is already linked as a sibling.');
        }

        return redirect()->back()->with('active_tab', 'family');
    }

    public function ajaxSearch(Request $request)
    {
        $term = $request->input('q', '');

        $students = Student::where('status', 'active')
            ->where(function($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhere('admission_no', 'like', "%{$term}%");
            })
            ->limit(20)
            ->get(['student_id', 'first_name', 'last_name', 'admission_no']);

        return response()->json($students);
    }
}
