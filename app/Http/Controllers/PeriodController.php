<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePeriodRequest;
use App\Http\Requests\UpdatePeriodRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\PeriodRepository;
use Illuminate\Http\Request;
use Flash;

class PeriodController extends AppBaseController
{
    /** @var PeriodRepository $periodRepository*/
    private $periodRepository;

    public function __construct(PeriodRepository $periodRepo)
    {
        $this->periodRepository = $periodRepo;
    }

    /**
     * Display a listing of the Period.
     */
    public function index(Request $request)
    {
        $periods = $this->periodRepository->paginate(10);

        return view('periods.index')
            ->with('periods', $periods);
    }

    /**
     * Show the form for creating a new Period.
     */
    public function create()
    {
        return view('periods.create');
    }

    /**
     * Store a newly created Period in storage.
     */
    public function store(CreatePeriodRequest $request)
    {
        $input = $request->all();

        $period = $this->periodRepository->create($input);

        Flash::success('Period saved successfully.');

        return redirect(route('periods.index'));
    }

    /**
     * Display the specified Period.
     */
    public function show($id)
    {
        $period = $this->periodRepository->find($id);

        if (empty($period)) {
            Flash::error('Period not found');

            return redirect(route('periods.index'));
        }

        return view('periods.show')->with('period', $period);
    }

    /**
     * Show the form for editing the specified Period.
     */
    public function edit($id)
    {
        $period = $this->periodRepository->find($id);

        if (empty($period)) {
            Flash::error('Period not found');

            return redirect(route('periods.index'));
        }

        return view('periods.edit')->with('period', $period);
    }

    /**
     * Update the specified Period in storage.
     */
    public function update($id, UpdatePeriodRequest $request)
    {
        $period = $this->periodRepository->find($id);

        if (empty($period)) {
            Flash::error('Period not found');

            return redirect(route('periods.index'));
        }

        $period = $this->periodRepository->update($request->all(), $id);

        Flash::success('Period updated successfully.');

        return redirect(route('periods.index'));
    }

    /**
     * Remove the specified Period from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $period = $this->periodRepository->find($id);

        if (empty($period)) {
            Flash::error('Period not found');

            return redirect(route('periods.index'));
        }

        $this->periodRepository->delete($id);

        Flash::success('Period deleted successfully.');

        return redirect(route('periods.index'));
    }
}
