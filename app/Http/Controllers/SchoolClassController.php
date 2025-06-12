<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSchoolClassRequest;
use App\Http\Requests\UpdateSchoolClassRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SchoolClassRepository;
use Illuminate\Http\Request;
use Flash;

class SchoolClassController extends AppBaseController
{
    /** @var SchoolClassRepository $schoolClassRepository*/
    private $schoolClassRepository;

    public function __construct(SchoolClassRepository $schoolClassRepo)
    {
        $this->schoolClassRepository = $schoolClassRepo;
    }

    /**
     * Display a listing of the SchoolClass.
     */
    public function index(Request $request)
    {
        $schoolClasses = $this->schoolClassRepository->paginate(10);

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
        $schoolClass = $this->schoolClassRepository->find($id);

        if (empty($schoolClass)) {
            Flash::error('School Class not found');

            return redirect(route('schoolClasses.index'));
        }

        return view('school_classes.show')->with('schoolClass', $schoolClass);
    }

    /**
     * Show the form for editing the specified SchoolClass.
     */
    public function edit($id)
    {
        $schoolClass = $this->schoolClassRepository->find($id);

        if (empty($schoolClass)) {
            Flash::error('School Class not found');

            return redirect(route('schoolClasses.index'));
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

            return redirect(route('schoolClasses.index'));
        }

        $schoolClass = $this->schoolClassRepository->update($request->all(), $id);

        Flash::success('School Class updated successfully.');

        return redirect(route('schoolClasses.index'));
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

            return redirect(route('schoolClasses.index'));
        }

        $this->schoolClassRepository->delete($id);

        Flash::success('School Class deleted successfully.');

        return redirect(route('schoolClasses.index'));
    }
}
