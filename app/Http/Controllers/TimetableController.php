<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTimetableRequest;
use App\Http\Requests\UpdateTimetableRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TimetableRepository;
use App\Models\ClassSection;
use App\Models\Period;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\Classroom;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Flash;

class TimetableController extends AppBaseController
{
    /** @var TimetableRepository $timetableRepository*/
    private $timetableRepository;

    public function __construct(TimetableRepository $timetableRepo)
    {
        $this->timetableRepository = $timetableRepo;

        $this->middleware('auth');
        $this->middleware('can:timetables.index')->only(['index', 'show']);
        $this->middleware('can:timetables.create')->only(['create', 'store']);
        $this->middleware('can:timetables.edit')->only(['edit', 'update']);
        $this->middleware('can:timetables.delete')->only('destroy');
    }

    /**
     * Display a listing of the Timetable.
     */
    public function index(Request $request)
    {
        $academicYears = AcademicYear::orderBy('start_date', 'desc')->get();

        $selectedAcademicYearId = $request->get('academic_year_id');
        if (!$selectedAcademicYearId && $academicYears->count() > 0) {
            $current = $academicYears->firstWhere('is_current', true);
            $selectedAcademicYearId = $current ? $current->academic_year_id : $academicYears->first()->academic_year_id;
        }

        $classSections = collect();
        $selectedClassSectionId = $request->get('class_section_id');
        $timetables = collect();
        $periods = collect();

        $daysOfWeek = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday',
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday',
        ];

        if ($selectedAcademicYearId) {
            $classSections = ClassSection::with(['class', 'section'])
                ->where('academic_year_id', $selectedAcademicYearId)
                ->orderBy('class_id')
                ->orderBy('section_id')
                ->get();

            if (!$selectedClassSectionId && $classSections->count() > 0) {
                $selectedClassSectionId = $classSections->first()->class_section_id;
            }

            if ($selectedClassSectionId) {
                $timetables = $this->timetableRepository
                    ->with([
                        'classSection.class',
                        'classSection.section',
                        'period',
                        'subject',
                        'teacher',
                        'classroom',
                        'academicYear',
                    ])
                    ->where('academic_year_id', $selectedAcademicYearId)
                    ->where('class_section_id', $selectedClassSectionId)
                    ->get();

                $periods = Period::orderBy('start_time')->get();
            }
        }

        $academicYearOptions = $academicYears->pluck('name', 'academic_year_id');

        $classSectionOptions = $classSections->mapWithKeys(function ($item) {
            $className = $item->class->name ?? '';
            $sectionName = $item->section->name ?? '';
            $parts = array_filter([$className, $sectionName]);
            $label = count($parts) ? implode(' - ', $parts) : 'Section ' . $item->class_section_id;
            return [$item->class_section_id => $label];
        });

        $schedule = [];
        foreach ($timetables as $entry) {
            $day = $entry->day_of_week;
            $periodId = $entry->period_id;
            if (!isset($schedule[$day])) {
                $schedule[$day] = [];
            }
            $schedule[$day][$periodId] = $entry;
        }

        return view('timetables.index', [
            'timetables' => $timetables,
            'academicYearOptions' => $academicYearOptions,
            'classSectionOptions' => $classSectionOptions,
            'selectedAcademicYearId' => $selectedAcademicYearId,
            'selectedClassSectionId' => $selectedClassSectionId,
            'periods' => $periods,
            'daysOfWeek' => $daysOfWeek,
            'schedule' => $schedule,
        ]);
    }

    /**
     * Show the form for creating a new Timetable.
     */
    public function create()
    {
        $data = $this->getFormData();
        return view('timetables.create', $data);
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
        $timetable = $this->timetableRepository->with([
            'classSection.class',
            'classSection.section',
            'period',
            'subject',
            'teacher',
            'classroom',
            'academicYear'
        ])->find($id);

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

        $data = $this->getFormData();
        $data['timetable'] = $timetable;

        return view('timetables.edit', $data);
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

    /**
     * Get data needed for create/edit forms
     */
    private function getFormData(): array
    {
        return [
            'classSections' => ClassSection::with(['class', 'section', 'academicYear'])
                ->get()
                ->mapWithKeys(function ($item) {
                    $className = $item->class->name ?? 'Unknown Class';
                    $sectionName = $item->section->name ?? 'Unknown Section';
                    return [$item->class_section_id => $className . ' - ' . $sectionName];
                })
                ->toArray(),

            'periods' => Period::orderBy('start_time')
                ->pluck('name', 'period_id')
                ->toArray(),

            'subjects' => Subject::orderBy('name')
                ->pluck('name', 'subject_id')
                ->toArray(),

            'teachers' => Staff::where('staff_type', 'teaching')
                ->where('status', 'active')
                ->orderBy('first_name')
                ->get()
                ->mapWithKeys(function ($staff) {
                    $name = trim($staff->first_name . ' ' . $staff->middle_name . ' ' . $staff->last_name);
                    if ($staff->employee_id) {
                        $name .= ' (' . $staff->employee_id . ')';
                    }
                    return [$staff->staff_id => $name];
                })
                ->toArray(),

            'classrooms' => Classroom::pluck('room_number', 'classroom_id')
                ->toArray(),

            'academicYears' => AcademicYear::orderBy('start_date', 'desc')
                ->pluck('name', 'academic_year_id')
                ->toArray(),

            'daysOfWeek' => [
                'monday' => 'Monday',
                'tuesday' => 'Tuesday',
                'wednesday' => 'Wednesday',
                'thursday' => 'Thursday',
                'friday' => 'Friday',
                'saturday' => 'Saturday',
                'sunday' => 'Sunday'
            ]
        ];
    }
}
