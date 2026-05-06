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

        // Check if subject_id is an array (multi-select)
        if (isset($input['subject_id']) && is_array($input['subject_id'])) {
            foreach ($input['subject_id'] as $subjectId) {
                $this->classSubjectRepository->create([
                    'class_id' => $input['class_id'],
                    'subject_id' => $subjectId,
                    'academic_year_id' => $input['academic_year_id']
                ]);
            }
            Flash::success('Subjects assigned to class successfully.');
        } else {
            $this->classSubjectRepository->create($input);
            Flash::success('Class Subject saved successfully.');
        }

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

    /**
     * Remove all subjects assigned to a specific class.
     */
    public function bulkDestroy(Request $request)
    {
        $classId = $request->input('class_id');
        
        if (empty($classId)) {
            Flash::error('Class selection is required for bulk deletion.');
            return redirect(route('class-subjects.index'));
        }

        // Delete all class subjects for this class
        $deletedCount = \App\Models\ClassSubject::where('class_id', $classId)->delete();

        Flash::success("Successfully cleared all ($deletedCount) subjects from the class.");

        return redirect(route('class-subjects.index'));
    }
}
