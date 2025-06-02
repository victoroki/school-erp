<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTeacherSubjectRequest;
use App\Http\Requests\UpdateTeacherSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TeacherSubjectRepository;
use Illuminate\Http\Request;
use Flash;

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
        return view('teacher_subjects.create');
    }

    /**
     * Store a newly created TeacherSubject in storage.
     */
    public function store(CreateTeacherSubjectRequest $request)
    {
        $input = $request->all();

        $teacherSubject = $this->teacherSubjectRepository->create($input);

        Flash::success('Teacher Subject saved successfully.');

        return redirect(route('teacherSubjects.index'));
    }

    /**
     * Display the specified TeacherSubject.
     */
    public function show($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacherSubjects.index'));
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

            return redirect(route('teacherSubjects.index'));
        }

        return view('teacher_subjects.edit')->with('teacherSubject', $teacherSubject);
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

        return redirect(route('teacherSubjects.index'));
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

        return redirect(route('teacherSubjects.index'));
    }
}
