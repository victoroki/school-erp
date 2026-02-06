<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SchoolClassRepository;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Flash;

class SchoolClassController extends AppBaseController
{
    /** @var SchoolClassRepository $schoolClassRepository*/
    private $schoolClassRepository;

    public function __construct(SchoolClassRepository $schoolClassRepo)
    {
        $this->schoolClassRepository = $schoolClassRepo;

        $this->middleware('auth');
        $this->middleware('can:school-classes.index')->only(['index', 'show']);
        $this->middleware('can:school-classes.create')->only(['create', 'store']);
        $this->middleware('can:school-classes.edit')->only(['edit', 'update']);
        $this->middleware('can:school-classes.delete')->only('destroy');
    }

    /**
     * Display a listing of the SchoolClass.
     */
    public function index(Request $request)
    {
        $schoolClasses = SchoolClass::withCount(['sections', 'classSubjects'])
            ->orderBy('numeric_value')
            ->paginate(12);

        return view('school_classes.index')
            ->with('schoolClasses', $schoolClasses);
    }

    /**
     * Show the form for creating a new SchoolClass.
     */
    public function create()
    {
        return view('school_classes.create');
    }

    /**
     * Store a newly created SchoolClass in storage.
     */
    public function store(CreateSchoolClassRequest $request)
    {
        $input = $request->all();

        $schoolClass = $this->schoolClassRepository->create($input);

        Flash::success('School Class saved successfully.');

        return redirect(route('school-classes.index'));
    }

    /**
     * Display the specified SchoolClass.
     */
    public function show($id)
    {
        $schoolClass = $this->schoolClassRepository->with([
            'classSections.section', 
            'classSections.academicYear', 
            'classSubjects.subject'
        ])->find($id);

        if (empty($schoolClass)) {
            Flash::error('School Class not found');

            return redirect(route('school-classes.index'));
        }

        // Count students in this class across all sections
        $studentCount = \App\Models\StudentClassEnrollment::whereIn('class_section_id', $schoolClass->classSections->pluck('class_section_id'))->count();

        return view('school_classes.show')
            ->with('schoolClass', $schoolClass)
            ->with('studentCount', $studentCount);
    }

    /**
     * Show the form for editing the specified SchoolClass.
     */
    public function edit($id)
    {
        $schoolClass = $this->schoolClassRepository->find($id);

        if (empty($schoolClass)) {
            Flash::error('School Class not found');

            return redirect(route('school-classes.index'));
        }

        return view('school_classes.edit')->with('schoolClass', $schoolClass);
    }

    /**
     * Update the specified SchoolClass in storage.
     */
    public function update($id, UpdateSchoolClassRequest $request)
    {
        $schoolClass = $this->schoolClassRepository->find($id);

        if (empty($schoolClass)) {
            Flash::error('School Class not found');

            return redirect(route('school-classes.index'));
        }

        $schoolClass = $this->schoolClassRepository->update($request->all(), $id);

        Flash::success('School Class updated successfully.');

        return redirect(route('school-classes.index'));
    }

    /**
     * Remove the specified SchoolClass from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $schoolClass = $this->schoolClassRepository->find($id);

        if (empty($schoolClass)) {
            Flash::error('School Class not found');

            return redirect(route('school-classes.index'));
        }

        $this->schoolClassRepository->delete($id);

        Flash::success('School Class deleted successfully.');

        return redirect(route('school-classes.index'));
    }
}
