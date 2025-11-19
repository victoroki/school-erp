<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TimetableRepository;
use Illuminate\Http\Request;
use Flash;

class TimetableController extends AppBaseController
{
    /** @var TimetableRepository $timetableRepository*/
    private $timetableRepository;

    public function __construct(TimetableRepository $timetableRepo)
    {
        $this->timetableRepository = $timetableRepo;
    }

    /**
     * Display a listing of the Timetable.
     */
    public function index(Request $request)
    {
        $timetables = $this->timetableRepository->paginate(10);

        return view('timetables.index')
            ->with('timetables', $timetables);
    }

    /**
     * Show the form for creating a new Timetable.
     */
    public function create()
    {
        return view('timetables.create');
    }

    /**
     * Store a newly created Timetable in storage.
     */
    public function store(CreateTimetableRequest $request)
    {
        $input = $request->all();

        $timetable = $this->timetableRepository->create($input);

        Flash::success('Timetable saved successfully.');

        return redirect(route('timetables.index'));
    }

    /**
     * Display the specified Timetable.
     */
    public function show($id)
    {
        $timetable = $this->timetableRepository->find($id);

        if (empty($timetable)) {
            Flash::error('Timetable not found');

            return redirect(route('timetables.index'));
        }

        return view('timetables.show')->with('timetable', $timetable);
    }

    /**
     * Show the form for editing the specified Timetable.
     */
    public function edit($id)
    {
        $timetable = $this->timetableRepository->find($id);

        if (empty($timetable)) {
            Flash::error('Timetable not found');

            return redirect(route('timetables.index'));
        }

        return view('timetables.edit')->with('timetable', $timetable);
    }

    /**
     * Update the specified Timetable in storage.
     */
    public function update($id, UpdateTimetableRequest $request)
    {
        $timetable = $this->timetableRepository->find($id);

        if (empty($timetable)) {
            Flash::error('Timetable not found');

            return redirect(route('timetables.index'));
        }

        $timetable = $this->timetableRepository->update($request->all(), $id);

        Flash::success('Timetable updated successfully.');

        return redirect(route('timetables.index'));
    }

    /**
     * Remove the specified Timetable from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $timetable = $this->timetableRepository->find($id);

        if (empty($timetable)) {
            Flash::error('Timetable not found');

            return redirect(route('timetables.index'));
        }

        $this->timetableRepository->delete($id);

        Flash::success('Timetable deleted successfully.');

        return redirect(route('timetables.index'));
    }
}
