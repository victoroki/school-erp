<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentParentRelationshipRequest;
use App\Http\Requests\UpdateStudentParentRelationshipRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Parents;
use App\Models\Student;
use App\Repositories\StudentParentRelationshipRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;



class StudentParentRelationshipController extends AppBaseController
{
    /** @var StudentParentRelationshipRepository $studentParentRelationshipRepository*/
    private $studentParentRelationshipRepository;

    public function __construct(StudentParentRelationshipRepository $studentParentRelationshipRepo)
    {
        $this->studentParentRelationshipRepository = $studentParentRelationshipRepo;
        $this->middleware('can:students.view')->only(['index', 'show']);
        $this->middleware('can:students.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * AJAX: Search students for Select2 dropdown.
     */
    public function searchStudents(Request $request)
    {
        $term = $request->input('q', '');
        $page = $request->input('page', 1);

        $query = Student::select('student_id', 'first_name', 'middle_name', 'last_name', 'admission_no')
            ->where('status', 'active');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('admission_no', 'like', "%{$term}%")
                    ->orWhere('student_id', 'like', "%{$term}%");
            });
        }

        $results = $query->orderBy('first_name')
            ->paginate(15, ['*'], 'page', $page);

        $formatted = $results->getCollection()->map(function ($s) {
            $name = trim(implode(' ', array_filter([$s->first_name, $s->middle_name, $s->last_name])));

            return [
                'id' => $s->student_id,
                'text' => $name . ' (' . ($s->admission_no ?: 'ID ' . $s->student_id) . ')',
            ];
        });

        return response()->json([
            'results' => $formatted->toArray(),
            'pagination' => [
                'more' => $results->hasMorePages(),
            ],
        ]);
    }

    /**
     * AJAX: Search parents for Select2 dropdown.
     */
    public function searchParents(Request $request)
    {
        $term = $request->input('q', '');
        $page = $request->input('page', 1);

        $query = Parents::select('parent_id', 'first_name', 'last_name', 'relationship', 'email', 'phone');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('relationship', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('alternate_phone', 'like', "%{$term}%");
            });
        }

        $results = $query->orderBy('first_name')
            ->paginate(15, ['*'], 'page', $page);

        $formatted = $results->getCollection()->map(function ($p) {
            $name = trim($p->first_name . ' ' . $p->last_name);
            $label = $name . ($p->relationship ? ' · ' . ucfirst($p->relationship) : '');

            $contact = $p->phone ?: ($p->email ?? '');
            if ($contact) {
                $label .= ' (' . $contact . ')';
            }

            return [
                'id' => $p->parent_id,
                'text' => $label,
            ];
        });

        return response()->json([
            'results' => $formatted->toArray(),
            'pagination' => [
                'more' => $results->hasMorePages(),
            ],
        ]);
    }

    /**
     * Display a listing of the StudentParentRelationship.
     */
    public function index(Request $request)
    {
        $studentParentRelationships = $this->studentParentRelationshipRepository->paginate(10);

        return view('student_parent_relationships.index')
            ->with('studentParentRelationships', $studentParentRelationships);
    }

    /**
     * Show the form for creating a new StudentParentRelationship.
     */
    public function create()
    {
        $students = [];
        $parents = [];

        return view('student_parent_relationships.create', compact('students', 'parents'));
    }

    /**
     * Store a newly created StudentParentRelationship in storage.
     */
    public function store(CreateStudentParentRelationshipRequest $request)
    {
        $input = $request->all();

        $studentParentRelationship = $this->studentParentRelationshipRepository->create($input);

        AuditTrail::log('Parent Relationship', 'CREATE', $studentParentRelationship->id, null, $studentParentRelationship->toArray());

        Flash::success('Student Parent Relationship saved successfully.');

        return redirect(route('student-parent-relationships.index'));
    }

    /**
     * Display the specified StudentParentRelationship.
     */
    public function show($id)
    {
        $studentParentRelationship = $this->studentParentRelationshipRepository->find($id);

        if (empty($studentParentRelationship)) {
            Flash::error('Student Parent Relationship not found');

            return redirect(route('student-parent-relationships.index'));
        }

        return view('student_parent_relationships.show')->with('studentParentRelationship', $studentParentRelationship);
    }

    /**
     * Show the form for editing the specified StudentParentRelationship.
     */
    public function edit($id)
    {
        $studentParentRelationship = $this->studentParentRelationshipRepository->find($id);

        if (empty($studentParentRelationship)) {
            Flash::error('Student Parent Relationship not found');

            return redirect(route('student-parent-relationships.index'));
        }

        $selectedStudent = $studentParentRelationship->student;
        $selectedParent = $studentParentRelationship->parent;
        $isPrimary = $studentParentRelationship->is_primary_contact;

        return view('student_parent_relationships.edit', compact(
            'studentParentRelationship',
            'selectedStudent',
            'selectedParent',
            'isPrimary'
        ));
    }

    /**
     * Update the specified StudentParentRelationship in storage.
     */
    public function update($id, UpdateStudentParentRelationshipRequest $request)
    {
        $studentParentRelationship = $this->studentParentRelationshipRepository->find($id);

        if (empty($studentParentRelationship)) {
            Flash::error('Student Parent Relationship not found');

            return redirect(route('student-parent-relationships.index'));
        }

        $oldData = $studentParentRelationship->toArray();
        $studentParentRelationship = $this->studentParentRelationshipRepository->update($request->all(), $id);

        AuditTrail::log('Parent Relationship', 'UPDATE', $studentParentRelationship->id, $oldData, $studentParentRelationship->toArray());

        Flash::success('Student Parent Relationship updated successfully.');

        return redirect(route('student-parent-relationships.index'));
    }

    /**
     * Remove the specified StudentParentRelationship from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $studentParentRelationship = $this->studentParentRelationshipRepository->find($id);

        if (empty($studentParentRelationship)) {
            Flash::error('Student Parent Relationship not found');

            return redirect(route('student-parent-relationships.index'));
        }

        $oldData = $studentParentRelationship->toArray();
        $this->studentParentRelationshipRepository->delete($id);

        AuditTrail::log('Parent Relationship', 'DELETE', $id, $oldData, null);

        Flash::success('Student Parent Relationship deleted successfully.');

        return redirect(route('student-parent-relationships.index'));
    }
}
