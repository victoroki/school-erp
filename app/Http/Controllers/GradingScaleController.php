<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGradingScaleRequest;
use App\Http\Requests\UpdateGradingScaleRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\GradingScaleRepository;
use Illuminate\Http\Request;
use Flash;

class GradingScaleController extends AppBaseController
{
    /** @var GradingScaleRepository $gradingScaleRepository*/
    private $gradingScaleRepository;

    public function __construct(GradingScaleRepository $gradingScaleRepo)
    {
        $this->gradingScaleRepository = $gradingScaleRepo;
    }

    /**
     * Display a listing of the GradingScale.
     */
    public function index(Request $request)
    {
        $gradingScales = $this->gradingScaleRepository->paginate(10);

        return view('grading_scales.index')
            ->with('gradingScales', $gradingScales);
    }

    /**
     * Show the form for creating a new GradingScale.
     */
    public function create()
    {
        return view('grading_scales.create');
    }

    /**
     * Store a newly created GradingScale in storage.
     */
    public function store(CreateGradingScaleRequest $request)
    {
        $input = $request->all();

        $gradingScale = $this->gradingScaleRepository->create($input);

        Flash::success('Grading Scale saved successfully.');

        return redirect(route('gradingScales.index'));
    }

    /**
     * Display the specified GradingScale.
     */
    public function show($id)
    {
        $gradingScale = $this->gradingScaleRepository->find($id);

        if (empty($gradingScale)) {
            Flash::error('Grading Scale not found');

            return redirect(route('gradingScales.index'));
        }

        return view('grading_scales.show')->with('gradingScale', $gradingScale);
    }

    /**
     * Show the form for editing the specified GradingScale.
     */
    public function edit($id)
    {
        $gradingScale = $this->gradingScaleRepository->find($id);

        if (empty($gradingScale)) {
            Flash::error('Grading Scale not found');

            return redirect(route('gradingScales.index'));
        }

        return view('grading_scales.edit')->with('gradingScale', $gradingScale);
    }

    /**
     * Update the specified GradingScale in storage.
     */
    public function update($id, UpdateGradingScaleRequest $request)
    {
        $gradingScale = $this->gradingScaleRepository->find($id);

        if (empty($gradingScale)) {
            Flash::error('Grading Scale not found');

            return redirect(route('gradingScales.index'));
        }

        $gradingScale = $this->gradingScaleRepository->update($request->all(), $id);

        Flash::success('Grading Scale updated successfully.');

        return redirect(route('gradingScales.index'));
    }

    /**
     * Remove the specified GradingScale from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $gradingScale = $this->gradingScaleRepository->find($id);

        if (empty($gradingScale)) {
            Flash::error('Grading Scale not found');

            return redirect(route('gradingScales.index'));
        }

        $this->gradingScaleRepository->delete($id);

        Flash::success('Grading Scale deleted successfully.');

        return redirect(route('gradingScales.index'));
    }
}
