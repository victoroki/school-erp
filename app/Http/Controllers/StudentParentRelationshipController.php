<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentParentRelationshipRequest;
use App\Http\Requests\UpdateStudentParentRelationshipRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Parents;
use App\Models\Student;
use App\Repositories\StudentParentRelationshipRepository;
use Illuminate\Http\Request;
use Flash;



class StudentParentRelationshipController extends AppBaseController
{
    /** @var StudentParentRelationshipRepository $studentParentRelationshipRepository*/
    private $studentParentRelationshipRepository;

    public function __construct(StudentParentRelationshipRepository $studentParentRelationshipRepo)
    {
        $this->studentParentRelationshipRepository = $studentParentRelationshipRepo;
    }

    /**
     * Get dropdown data for forms
     */
    private function getDropdownData()
    {
        return [
            'students' => Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' (', student_id, ')') as full_name")
                ->pluck('full_name', 'student_id')
                ->toArray(),
            'parents' => Parents::selectRaw("parent_id, CONCAT(first_name, ' ', last_name, ' (', email, ')') as full_name")
                ->pluck('full_name', 'parent_id')
                ->toArray(),
        ];
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
        $dropdownData = $this->getDropdownData();
        
        return view('student_parent_relationships.create', $dropdownData);
    }

    /**
     * Store a newly created StudentParentRelationship in storage.
     */
    public function store(CreateStudentParentRelationshipRequest $request)
    {
        $input = $request->all();

        $studentParentRelationship = $this->studentParentRelationshipRepository->create($input);

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

        $dropdownData = $this->getDropdownData();
        
        return view('student_parent_relationships.edit', array_merge(
            ['studentParentRelationship' => $studentParentRelationship],
            $dropdownData
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

        $studentParentRelationship = $this->studentParentRelationshipRepository->update($request->all(), $id);

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

        $this->studentParentRelationshipRepository->delete($id);

        Flash::success('Student Parent Relationship deleted successfully.');

        return redirect(route('student-parent-relationships.index'));
    }
}