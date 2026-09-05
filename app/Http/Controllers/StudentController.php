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
use App\Models\AuditTrail;

class StudentController extends AppBaseController
{
    /** @var StudentRepository $studentRepository*/
    private $studentRepository;

    public function __construct(StudentRepository $studentRepo)
    {
        $this->studentRepository = $studentRepo;
        $this->middleware('can:students.view')->only(['index', 'show']);
        $this->middleware('can:students.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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
                    ->orWhere('nemis_number', 'like', "%$q%");
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
            $input['photo_url'] = $this->storeStudentPhoto($request->file('photo'));
        }

        $student = $this->studentRepository->create($input);

        // Auto-enroll if class section is provided
        if ($request->filled('class_section_id')) {
            \App\Models\StudentClassEnrollment::create([
                'student_id' => $student->student_id,
                'class_section_id' => $request->class_section_id,
                'academic_year_id' => $request->academic_year_id,
                'roll_number' => $request->input('roll_number_enrollment'),
                'enrollment_date' => $request->admission_date ?? now(),
                'status' => 'active',
                'is_current' => true
            ]);

            // Auto-assign fee structures for the enrolled class
            $feeService = app(\App\Services\FeeAssignmentService::class);
            $feeService->autoAssignFeesToStudent($student, $request->academic_year_id);
        }

        // Auto-create transport registration if uses_transport is toggled
        if ($request->boolean('uses_transport') && $request->input('route_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first();
            \App\Models\TransportRegistration::create([
                'student_id' => $student->student_id,
                'route_id' => $request->input('route_id'),
                'stop_id' => $request->input('stop_id'),
                'fee_amount' => 0,
                'payment_status' => 'unpaid',
                'academic_year_id' => $currentYear?->academic_year_id,
            ]);
        }

        // Auto-create hostel allocation if is_hosteller is toggled
        if ($request->boolean('is_hosteller') && $request->input('hostel_id')) {
            $currentYear = AcademicYear::where('is_current', true)->first();
            \App\Models\HostelAllocation::create([
                'student_id' => $student->student_id,
                'hostel_id' => $request->input('hostel_id'),
                'room_id' => $request->input('room_id'),
                'allocation_date' => $request->admission_date ?? now(),
                'status' => 'pending',
                'academic_year_id' => $currentYear?->academic_year_id,
            ]);
        }

        AuditTrail::log('Student', 'CREATE', $student->student_id, null, $student->toArray());

        Flash::success('Student saved successfully.');

        return redirect(route('students.index'));
    }

    public function show($id)
    {
        $student = $this->resolveStudent($id);

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
        $student = $this->resolveStudent($id);

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
        $student = $this->resolveStudent($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }

        $input = $request->all();

        if ($request->hasFile('photo')) {
            // Delete the old photo if it exists (covers legacy students/photos/ paths too)
            if ($student->photo_url) {
                $oldPath = str_starts_with($student->photo_url, 'students/')
                    ? public_path($student->photo_url)
                    : public_path('uploads/' . $student->photo_url);
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            $input['photo_url'] = $this->storeStudentPhoto($request->file('photo'));
        }


        $oldData = $student->toArray();
        $student = $this->studentRepository->update($input, $id);

        AuditTrail::log('Student', 'UPDATE', $student->student_id, $oldData, $student->toArray());

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
        $student = $this->resolveStudent($id);

        if (empty($student)) {
            Flash::error('Student not found');

            return redirect(route('students.index'));
        }
        
        if ($student->photo_url) {
            $photoPath = str_starts_with($student->photo_url, 'students/')
                ? public_path($student->photo_url)
                : public_path('uploads/' . $student->photo_url);
            if (file_exists($photoPath)) {
                @unlink($photoPath);
            }
        }

        $oldData = $student->toArray();
        $this->studentRepository->delete($id);

        AuditTrail::log('Student', 'DELETE', $id, $oldData, null);

        Flash::success('Student deleted successfully.');

        return redirect(route('students.index'));
    }

    /**
     * Resolve a Student by id, returning null gracefully when the id is
     * missing or non-numeric instead of throwing a TypeError.
     */
    private function resolveStudent($id)
    {
        if ($id === null || ! is_numeric($id)) {
            return null;
        }

        return $this->studentRepository->find((int) $id);
    }

    /**
     * Save an uploaded photo under public/uploads/student_photos and return
     * the relative path (student_photos/...) to store in the database.
     *
     * A plain public folder is used (not the storage disk) so photos are
     * directly servable without a `storage` symlink — which cannot be created
     * on shared cPanel hosting without terminal/SSH access.
     */
    private function storeStudentPhoto($photo): string
    {
        $dir = public_path('uploads/student_photos');
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $photoName = time() . '.' . $photo->getClientOriginalExtension();
        $photo->move($dir, $photoName);

        return 'student_photos/' . $photoName;
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

            AuditTrail::log('Student', 'SIBLING ADDED', $student->student_id, null, ['sibling_id' => $siblingId, 'relationship_type' => $request->input('relationship_type')]);

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

    /**
     * Attach an existing parent to a student, or create + attach a new one.
     */
    public function addParent(Request $request, $id)
    {
        $student = $this->resolveStudent($id);

        if (empty($student)) {
            Flash::error('Student not found');
            return redirect(route('students.index'));
        }

        $input = $request->validate([
            'parent_id'   => 'nullable|exists:parents,parent_id',
            'relationship' => 'required|string|max:50',
            'is_primary_contact' => 'nullable|boolean',
            'first_name'  => 'required_without:parent_id|string|max:50',
            'last_name'   => 'required_without:parent_id|string|max:50',
            'email'       => 'nullable|string|max:100',
            'phone'       => 'required_without:parent_id|nullable|string|max:20',
            'occupation'  => 'nullable|string|max:100',
        ]);

        $isPrimary = $request->boolean('is_primary_contact');

        if (!empty($input['parent_id'])) {
            $parent = \App\Models\Parents::find($input['parent_id']);
        } else {
            $parent = \App\Models\Parents::create([
                'first_name'   => $input['first_name'],
                'last_name'    => $input['last_name'],
                'relationship' => $input['relationship'],
                'email'        => $input['email'] ?? null,
                'phone'        => $input['phone'] ?? null,
                'occupation'   => $input['occupation'] ?? null,
            ]);
        }

        if ($student->parents()->where('parents.parent_id', $parent->parent_id)->exists()) {
            Flash::warning('This parent is already linked to the student.');
            return redirect()->back()->with('active_tab', 'family');
        }

        // If this is being set as the primary contact, clear any existing primary flag.
        if ($isPrimary) {
            \DB::table('student_parent_relationship')
                ->where('student_id', $student->student_id)
                ->update(['is_primary_contact' => false]);
        }

        $student->parents()->attach($parent->parent_id, [
            'is_primary_contact' => $isPrimary,
        ]);

        AuditTrail::log('Student', 'PARENT ADDED', $student->student_id, null, [
            'parent_id' => $parent->parent_id,
            'relationship' => $input['relationship'],
        ]);

        Flash::success('Parent added successfully.');

        return redirect()->back()->with('active_tab', 'family');
    }

    /**
     * Detach a parent from a student.
     */
    public function removeParent(Request $request, $id, $parentId = null)
    {
        $student = $this->resolveStudent($id);
        $parentId = (int) $parentId;

        if (empty($student) || $parentId <= 0) {
            Flash::error('Invalid request.');
            return redirect(route('students.index'));
        }

        $student->parents()->detach($parentId);

        AuditTrail::log('Student', 'PARENT REMOVED', $student->student_id, null, ['parent_id' => $parentId]);

        Flash::success('Parent removed successfully.');

        return redirect()->back()->with('active_tab', 'family');
    }

    public function ajaxSearchParents(Request $request)
    {
        $term = $request->input('q', '');
        $exclude = $request->input('student_id');

        $query = \App\Models\Parents::query()
            ->where(function($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                  ->orWhere('last_name', 'like', "%{$term}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$term}%"])
                  ->orWhere('phone', 'like', "%{$term}%");
            });

        if ($exclude) {
            $linkedIds = \App\Models\Student::find($exclude)?->parents()->pluck('parents.parent_id') ?? collect();
            if ($linkedIds->isNotEmpty()) {
                $query->whereNotIn('parent_id', $linkedIds);
            }
        }

        $query->limit(20);
        $parents = $query->get(['parent_id', 'first_name', 'last_name', 'phone']);

        return response()->json($parents->map(fn($p) => [
            'parent_id' => $p->parent_id,
            'full_name' => $p->full_name,
            'phone' => $p->phone,
        ]));
    }
}
