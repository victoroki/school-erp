<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClassSubjectRequest;
use App\Http\Requests\UpdateClassSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ClassSubjectRepository;
use Illuminate\Http\Request;
use Flash;

class ClassSubjectController extends AppBaseController
{
    /** @var ClassSubjectRepository $classSubjectRepository*/
    private $classSubjectRepository;

    public function __construct(ClassSubjectRepository $classSubjectRepo)
    {
        $this->classSubjectRepository = $classSubjectRepo;
    }

    /**
     * Display a listing of the ClassSubject.
     */
    public function index(Request $request)
    {
        $classSubjects = $this->classSubjectRepository->paginate(10);

        return view('class_subjects.index')
            ->with('classSubjects', $classSubjects);
    }

    /**
     * Show the form for creating a new ClassSubject.
     */
    public function create()
    {
        return view('class_subjects.create');
    }

    /**
     * Store a newly created ClassSubject in storage.
     */
    public function store(CreateClassSubjectRequest $request)
    {
        $input = $request->all();

        $classSubject = $this->classSubjectRepository->create($input);

        Flash::success('Class Subject saved successfully.');

        return redirect(route('classSubjects.index'));
    }

    /**
     * Display the specified ClassSubject.
     */
    public function show($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('classSubjects.index'));
        }

        return view('class_subjects.show')->with('classSubject', $classSubject);
    }

    /**
     * Show the form for editing the specified ClassSubject.
     */
    public function edit($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('classSubjects.index'));
        }

        return view('class_subjects.edit')->with('classSubject', $classSubject);
    }

    /**
     * Update the specified ClassSubject in storage.
     */
    public function update($id, UpdateClassSubjectRequest $request)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('classSubjects.index'));
        }

        $classSubject = $this->classSubjectRepository->update($request->all(), $id);

        Flash::success('Class Subject updated successfully.');

        return redirect(route('classSubjects.index'));
    }

    /**
     * Remove the specified ClassSubject from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('classSubjects.index'));
        }

        $this->classSubjectRepository->delete($id);

        Flash::success('Class Subject deleted successfully.');

        return redirect(route('classSubjects.index'));
    }
}
