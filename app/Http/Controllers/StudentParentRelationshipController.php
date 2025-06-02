<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentParentRelationshipRequest;
use App\Http\Requests\UpdateStudentParentRelationshipRequest;
use App\Http\Controllers\AppBaseController;
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
        return view('student_parent_relationships.create');
    }

    /**
     * Store a newly created StudentParentRelationship in storage.
     */
    public function store(CreateStudentParentRelationshipRequest $request)
    {
        $input = $request->all();

        $studentParentRelationship = $this->studentParentRelationshipRepository->create($input);

        Flash::success('Student Parent Relationship saved successfully.');

        return redirect(route('studentParentRelationships.index'));
    }

    /**
     * Display the specified StudentParentRelationship.
     */
    public function show($id)
    {
        $studentParentRelationship = $this->studentParentRelationshipRepository->find($id);

        if (empty($studentParentRelationship)) {
            Flash::error('Student Parent Relationship not found');

            return redirect(route('studentParentRelationships.index'));
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

            return redirect(route('studentParentRelationships.index'));
        }

        return view('student_parent_relationships.edit')->with('studentParentRelationship', $studentParentRelationship);
    }

    /**
     * Update the specified StudentParentRelationship in storage.
     */
    public function update($id, UpdateStudentParentRelationshipRequest $request)
    {
        $studentParentRelationship = $this->studentParentRelationshipRepository->find($id);

        if (empty($studentParentRelationship)) {
            Flash::error('Student Parent Relationship not found');

            return redirect(route('studentParentRelationships.index'));
        }

        $studentParentRelationship = $this->studentParentRelationshipRepository->update($request->all(), $id);

        Flash::success('Student Parent Relationship updated successfully.');

        return redirect(route('studentParentRelationships.index'));
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

            return redirect(route('studentParentRelationships.index'));
        }

        $this->studentParentRelationshipRepository->delete($id);

        Flash::success('Student Parent Relationship deleted successfully.');

        return redirect(route('studentParentRelationships.index'));
    }
}
