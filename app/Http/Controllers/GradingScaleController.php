<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateGradingScaleRequest;
use App\Http\Requests\UpdateGradingScaleRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\GradingScale;
use App\Repositories\GradingScaleRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;

class GradingScaleController extends AppBaseController
{
    /** @var GradingScaleRepository $gradingScaleRepository*/
    private $gradingScaleRepository;

    public function __construct(GradingScaleRepository $gradingScaleRepo)
    {
        $this->gradingScaleRepository = $gradingScaleRepo;
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
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
     * Seed the standard KCSE (8-4-4) grading scale used in Kenya.
     */
    public function seedKcse()
    {
        return $this->seedGrades('kcse');
    }

    /**
     * Seed the standard CBC / CBE performance-level scale used in Kenya.
     */
    public function seedCbc()
    {
        return $this->seedGrades('cbc');
    }

    /**
     * Seed a national standard grading scale. Existing grades
     * (matched by name) are left untouched.
     */
    private function seedGrades(string $system)
    {
        $scales = [
            'kcse' => [
                'label' => 'KCSE (8-4-4)',
                'grades' => [
                    // [grade, min %, max %, points, remark]
                    ['A',  80, 100,   12, 'Excellent'],
                    ['A-', 75, 79.99, 11, 'Very Good'],
                    ['B+', 70, 74.99, 10, 'Good'],
                    ['B',  65, 69.99,  9, 'Good'],
                    ['B-', 60, 64.99,  8, 'Above Average'],
                    ['C+', 55, 59.99,  7, 'Average'],
                    ['C',  50, 54.99,  6, 'Average'],
                    ['C-', 45, 49.99,  5, 'Below Average'],
                    ['D+', 40, 44.99,  4, 'Below Average'],
                    ['D',  35, 39.99,  3, 'Poor'],
                    ['D-', 30, 34.99,  2, 'Very Poor'],
                    ['E',   0, 29.99,  1, 'Fail'],
                ],
            ],
            'cbc' => [
                'label' => 'CBC / CBE',
                'grades' => [
                    // Performance levels under Kenya's competency-based curriculum
                    ['EE', 76, 100,   4, 'Exceeding Expectation'],
                    ['ME', 51, 75.99, 3, 'Meeting Expectation'],
                    ['AE', 26, 50.99, 2, 'Approaching Expectation'],
                    ['BE',  0, 25.99, 1, 'Below Expectation'],
                ],
            ],
        ];

        abort_unless(isset($scales[$system]), 404);

        $label = $scales[$system]['label'];
        $created = 0;

        foreach ($scales[$system]['grades'] as [$name, $min, $max, $points, $remark]) {
            if (GradingScale::where('name', $name)->exists()) {
                continue;
            }

            GradingScale::create([
                'name' => $name,
                'min_percentage' => $min,
                'max_percentage' => $max,
                'grade_point' => $points,
                'description' => $remark,
            ]);
            $created++;
        }

        AuditTrail::log('Grading Scale', 'SEED_' . strtoupper($system), null, null, ['grades_created' => $created]);

        Flash::success("{$label} grading scale ready — {$created} grade(s) added.");

        return redirect(route('gradingScales.index'));
    }

    /**
     * Store a newly created GradingScale in storage.
     */
    public function store(CreateGradingScaleRequest $request)
    {
        $input = $request->all();

        $gradingScale = $this->gradingScaleRepository->create($input);

        AuditTrail::log('Grading Scale', 'CREATE', $gradingScale->grade_id, null, $gradingScale->toArray());

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

        $oldData = $gradingScale->toArray();
        $gradingScale = $this->gradingScaleRepository->update($request->all(), $id);

        AuditTrail::log('Grading Scale', 'UPDATE', $gradingScale->grade_id, $oldData, $gradingScale->toArray());

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

        $oldData = $gradingScale->toArray();
        $this->gradingScaleRepository->delete($id);

        AuditTrail::log('Grading Scale', 'DELETE', $id, $oldData, null);

        Flash::success('Grading Scale deleted successfully.');

        return redirect(route('gradingScales.index'));
    }
}
