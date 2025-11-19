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
    }

    /**
     * Display a listing of the Timetable.
     */
    public function index(Request $request)
    {
        $timetables = $this->timetableRepository->with([
            'classSection.class',
            'classSection.section',
            'period',
            'subject',
            'teacher',
            'classroom',
            'academicYear'
        ])->paginate(10);

        return view('timetables.index')
            ->with('timetables', $timetables);
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
