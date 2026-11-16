<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAcademicYearRequest;
use App\Http\Requests\UpdateAcademicYearRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\AcademicYearRepository;
use App\Models\AcademicYear;
use App\Models\AuditTrail;
use App\Services\AcademicCalendarService;
use Illuminate\Http\Request;
use Flash;

class AcademicYearController extends AppBaseController
{
    /** @var AcademicYearRepository $academicYearRepository*/
    private $academicYearRepository;

    /** @var AcademicCalendarService $calendar */
    private $calendar;

    public function __construct(AcademicYearRepository $academicYearRepo, AcademicCalendarService $calendar)
    {
        $this->academicYearRepository = $academicYearRepo;
        $this->calendar = $calendar;

        $this->middleware('auth');
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'rollForward']);
    }

    /**
     * Display a listing of the AcademicYear.
     */
    public function index(Request $request)
    {
        $academicYears = $this->academicYearRepository->paginate(10);

        // With term counts on the row, a year that was created without terms —
        // which is what used to happen — is obvious at a glance instead of
        // showing up later as "no terms are defined for the selected year".
        $academicYears->loadCount('terms');
        $academicYears->load(['terms' => fn ($q) => $q->orderBy('display_order')->orderBy('start_date')]);

        return view('academic_years.index')
            ->with('academicYears', $academicYears);
    }

    /**
     * Show the form for creating a new AcademicYear.
     */
    public function create()
    {
        $previousYear = AcademicYear::orderByDesc('start_date')->first();

        return view('academic_years.create', [
            'previousYear' => $previousYear,
            'willCopyTerms' => (bool) $previousYear?->terms()->exists(),
        ]);
    }

    /**
     * Store a newly created AcademicYear in storage.
     */
    public function store(CreateAcademicYearRequest $request)
    {
        $academicYear = $this->academicYearRepository->create($request->all());

        // A year with no terms cannot be used: fee assignment rejects it and the
        // fee structure form has nothing to attach a price to. So the terms are
        // built here, from the previous year's pattern, rather than left as a
        // manual follow-up task.
        $terms = $this->calendar->createTermsFor($academicYear);

        if ($academicYear->is_current) {
            $this->calendar->makeCurrent($academicYear);
        }

        AuditTrail::log('Academic Year', 'CREATE', $academicYear->academic_year_id, null, $academicYear->toArray());

        Flash::success(sprintf(
            'Academic Year saved with %d term%s.',
            $terms->count(),
            $terms->count() === 1 ? '' : 's'
        ));

        return redirect(route('academic-years.index'));
    }

    /**
     * Set up the academic year after the current one.
     *
     * The seeded calendar stopped in 2026, so there was no way to reach 2027
     * without editing a seeder and reseeding. This copies the current year's
     * shape forward, brings the terms across, and makes the new year current.
     */
    public function rollForward()
    {
        $next = $this->calendar->rollForward();

        AuditTrail::log('Academic Year', 'ROLL_FORWARD', $next->academic_year_id, null, $next->toArray());

        Flash::success(sprintf(
            'Academic Year %s is ready with %d term%s.',
            $next->name,
            $next->terms()->count(),
            $next->terms()->count() === 1 ? '' : 's'
        ));

        return redirect(route('academic-years.index'));
    }

    /**
     * Display the specified AcademicYear.
     */
    public function show($id)
    {
        $academicYear = $this->academicYearRepository->find($id);

        if (empty($academicYear)) {
            Flash::error('Academic Year not found');

            return redirect(route('academic-years.index'));
        }

        $academicYear->load(['terms' => fn ($q) => $q->orderBy('display_order')->orderBy('start_date')]);

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

            return redirect(route('academic-years.index'));
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

            return redirect(route('academic-years.index'));
        }

        $oldData = $academicYear->toArray();
        $academicYear = $this->academicYearRepository->update($request->all(), $id);

        // Ticking 'is current' here used to leave the previous year current as
        // well, and the ~15 places that resolve the current year with
        // where('is_current', true)->first() then returned whichever row MySQL
        // happened to produce.
        if ($academicYear->is_current) {
            $this->calendar->makeCurrent($academicYear);
        }

        AuditTrail::log('Academic Year', 'UPDATE', $academicYear->academic_year_id, $oldData, $academicYear->toArray());

        Flash::success('Academic Year updated successfully.');

        return redirect(route('academic-years.index'));
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

            return redirect(route('academic-years.index'));
        }

        $oldData = $academicYear->toArray();
        $this->academicYearRepository->delete($id);

        AuditTrail::log('Academic Year', 'DELETE', $id, $oldData, null);

        Flash::success('Academic Year deleted successfully.');

        return redirect(route('academic-years.index'));
    }
}
