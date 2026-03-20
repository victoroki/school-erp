<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClassSubjectRequest;
use App\Http\Requests\UpdateClassSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\AcademicYear;
use App\Repositories\ClassSubjectRepository;
use Illuminate\Http\Request;
use Flash;
use App\Models\SchoolClass; 
use App\Models\Subject;     


class ClassSubjectController extends AppBaseController
{
    /** @var ClassSubjectRepository $classSubjectRepository*/
    private $classSubjectRepository;

    public function __construct(ClassSubjectRepository $classSubjectRepo)
    {
        $this->classSubjectRepository = $classSubjectRepo;

        $this->middleware('auth');
        $this->middleware('can:class-subjects.index')->only(['index', 'show']);
        $this->middleware('can:class-subjects.create')->only(['create', 'store']);
        $this->middleware('can:class-subjects.edit')->only(['edit', 'update']);
        $this->middleware('can:class-subjects.delete')->only('destroy');
    }

    /**
     * Get dropdown data for forms
     */
    private function getDropdownData()
    {
        return [
            'classes' => SchoolClass::pluck('name', 'class_id')->toArray(),
            'subjects' => Subject::pluck('name', 'subject_id')->toArray(),
            'academicYear' => AcademicYear::pluck('name', 'academic_year_id')
        ];
    }

    /**
     * Display a listing of the ClassSubject.
     */
    public function index(Request $request)
    {
        $allClassSubjects = $this->classSubjectRepository->with(['class','subject','academicYear'])->get();

        // Group by Class Name
        $classSubjects = $allClassSubjects->groupBy(function($classSubject) {
            return $classSubject->class ? $classSubject->class->name : 'Unassigned';
        });

        return view('class_subjects.index')
            ->with('classSubjects', $classSubjects);
    }

    /**
     * Show the form for creating a new ClassSubject.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('class_subjects.create', $dropdownData);
    }

    /**
     * Store a newly created ClassSubject in storage.
     */
    public function store(CreateClassSubjectRequest $request)
    {
        $input = $request->all();

        $classSubject = $this->classSubjectRepository->create($input);

        Flash::success('Class Subject saved successfully.');

        return redirect(route('class-subjects.index'));
    }

    /**
     * Display the specified ClassSubject.
     */
    public function show($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('class-subjects.index'));
        }

        // Eager load relations for display
        $classSubject->load(['class', 'subject', 'academicYear']);

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

            return redirect(route('class-subjects.index'));
        }

        $dropdownData = $this->getDropdownData();
        
        return view('class_subjects.edit', array_merge(
            ['classSubject' => $classSubject],
            $dropdownData
        ));
    }

    /**
     * Update the specified ClassSubject in storage.
     */
    public function update($id, UpdateClassSubjectRequest $request)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('class-subjects.index'));
        }

        $classSubject = $this->classSubjectRepository->update($request->all(), $id);

        Flash::success('Class Subject updated successfully.');

        return redirect(route('class-subjects.index'));
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

            return redirect(route('class-subjects.index'));
        }

        $this->classSubjectRepository->delete($id);

        Flash::success('Class Subject deleted successfully.');

        return redirect(route('class-subjects.index'));
    }
}
