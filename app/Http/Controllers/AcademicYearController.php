<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\AcademicYearRepository;
use Illuminate\Http\Request;
use Flash;

class AcademicYearController extends AppBaseController
{
    /** @var AcademicYearRepository $academicYearRepository*/
    private $academicYearRepository;

    public function __construct(AcademicYearRepository $academicYearRepo)
    {
        $this->academicYearRepository = $academicYearRepo;
    }

    /**
     * Display a listing of the AcademicYear.
     */
    public function index(Request $request)
    {
        $academicYears = $this->academicYearRepository->paginate(10);

        return view('academic_years.index')
            ->with('academicYears', $academicYears);
    }

    /**
     * Show the form for creating a new AcademicYear.
     */
    public function create()
    {
        return view('academic_years.create');
    }

    /**
     * Store a newly created AcademicYear in storage.
     */
    public function store(CreateAcademicYearRequest $request)
    {
        $input = $request->all();

        $academicYear = $this->academicYearRepository->create($input);

        Flash::success('Academic Year saved successfully.');

        return redirect(route('academicYears.index'));
    }

    /**
     * Display the specified AcademicYear.
     */
    public function show($id)
    {
        $academicYear = $this->academicYearRepository->find($id);

        if (empty($academicYear)) {
            Flash::error('Academic Year not found');

            return redirect(route('academicYears.index'));
        }

        return view('academic_years.show')->with('academicYear', $academicYear);
    }

    /**
     * Show the form for editing the specified AcademicYear.
     */
    public function edit($id)
    {
        $academicYear = $this->academicYearRepository->find($id);

        if (empty($academicYear)) {
            Flash::error('Academic Year not found');

            return redirect(route('academicYears.index'));
        }

        return view('academic_years.edit')->with('academicYear', $academicYear);
    }

    /**
     * Update the specified AcademicYear in storage.
     */
    public function update($id, UpdateAcademicYearRequest $request)
    {
        $academicYear = $this->academicYearRepository->find($id);

        if (empty($academicYear)) {
            Flash::error('Academic Year not found');

            return redirect(route('academicYears.index'));
        }

        $academicYear = $this->academicYearRepository->update($request->all(), $id);

        Flash::success('Academic Year updated successfully.');

        return redirect(route('academicYears.index'));
    }

    /**
     * Remove the specified AcademicYear from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $academicYear = $this->academicYearRepository->find($id);

        if (empty($academicYear)) {
            Flash::error('Academic Year not found');

            return redirect(route('academicYears.index'));
        }

        $this->academicYearRepository->delete($id);

        Flash::success('Academic Year deleted successfully.');

        return redirect(route('academicYears.index'));
    }
}
