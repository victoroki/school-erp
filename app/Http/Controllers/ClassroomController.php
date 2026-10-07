<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClassroomRequest;
use App\Http\Requests\UpdateClassroomRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ClassroomRepository;
use App\Models\AuditTrail;
use App\Models\Classroom;
use App\Services\ClassroomLifecycleService;
use Illuminate\Http\Request;
use Flash;

class ClassroomController extends AppBaseController
{
    /** @var ClassroomRepository $classroomRepository*/
    private $classroomRepository;

    /** @var ClassroomLifecycleService */
    private $lifecycle;

    public function __construct(ClassroomRepository $classroomRepo, ClassroomLifecycleService $lifecycle)
    {
        $this->classroomRepository = $classroomRepo;
        $this->lifecycle = $lifecycle;

        $this->middleware('auth');
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'archive', 'restore']);
    }

    /**
     * Display a listing of the Classroom.
     */
    public function index(Request $request)
    {
        $query = Classroom::query();

        // Archived rooms stay out of the working list unless explicitly asked
        // for, mirroring the subjects module: status=archived shows them.
        $status = $request->get('status', 'active');
        if ($status === 'archived') {
            $query->where('is_active', false);
        } elseif ($status === 'all') {
            // no filter
        } else {
            $query->where('is_active', true);
        }

        if ($request->filled('q')) {
            $term = $request->get('q');
            $query->where(function ($w) use ($term) {
                $w->where('room_number', 'like', "%{$term}%")
                    ->orWhere('building', 'like', "%{$term}%");
            });
        }

        $classrooms = $query->orderBy('room_number')->paginate(10)->withQueryString();

        return view('classrooms.index')
            ->with('classrooms', $classrooms)
            ->with('status', $status);
    }

    /**
     * Show the form for creating a new Classroom.
     */
    public function create()
    {
        return view('classrooms.create');
    }

    /**
     * Store a newly created Classroom in storage.
     */
    public function store(CreateClassroomRequest $request)
    {
        $input = $request->all();

        $classroom = $this->classroomRepository->create($input);

        AuditTrail::log('Classroom', 'CREATE', $classroom->classroom_id, null, $classroom->toArray());

        Flash::success('Classroom saved successfully.');

        return redirect(route('classrooms.index'));
    }

    /**
     * Display the specified Classroom.
     */
    public function show($id)
    {
        $classroom = $this->classroomRepository->find($id);

        if (empty($classroom)) {
            Flash::error('Classroom not found');

            return redirect(route('classrooms.index'));
        }

        // Tells the administrator up-front whether a delete would be refused,
        // and what exactly is holding the room — same pattern as subjects.
        $usage = $this->lifecycle->usageSummary($classroom);

        return view('classrooms.show')
            ->with('classroom', $classroom)
            ->with('usage', $usage);
    }

    /**
     * Show the form for editing the specified Classroom.
     */
    public function edit($id)
    {
        $classroom = $this->classroomRepository->find($id);

        if (empty($classroom)) {
            Flash::error('Classroom not found');

            return redirect(route('classrooms.index'));
        }

        return view('classrooms.edit')->with('classroom', $classroom);
    }

    /**
     * Update the specified Classroom in storage.
     */
    public function update($id, UpdateClassroomRequest $request)
    {
        $classroom = $this->classroomRepository->find($id);

        if (empty($classroom)) {
            Flash::error('Classroom not found');

            return redirect(route('classrooms.index'));
        }

        $oldData = $classroom->toArray();
        $classroom = $this->classroomRepository->update($request->all(), $id);

        AuditTrail::log('Classroom', 'UPDATE', $classroom->classroom_id, $oldData, $classroom->toArray());

        Flash::success('Classroom updated successfully.');

        return redirect(route('classrooms.index'));
    }

    /**
     * Remove the specified Classroom from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $classroom = $this->classroomRepository->find($id);

        if (empty($classroom)) {
            Flash::error('Classroom not found');

            return redirect(route('classrooms.index'));
        }

        // class_sections, timetable and exam_schedules hold RESTRICT foreign
        // keys to classrooms. Deleting a room in use must be refused with a
        // message that names the blockers — never caught-and-swallowed.
        if ($this->lifecycle->hasHistory($classroom)) {
            Flash::error($this->lifecycle->deletionRefusalMessage($classroom));

            return redirect(route('classrooms.show', $classroom->classroom_id));
        }

        $oldData = $classroom->toArray();
        $this->classroomRepository->delete($id);

        AuditTrail::log('Classroom', 'DELETE', $id, $oldData, null);

        Flash::success('Classroom deleted successfully.');

        return redirect(route('classrooms.index'));
    }

    /**
     * Retire a classroom that has academic history: nothing is destroyed, the
     * room just stops being offered for new allocations.
     */
    public function archive($id)
    {
        $classroom = $this->classroomRepository->find($id);

        if (empty($classroom)) {
            Flash::error('Classroom not found');

            return redirect(route('classrooms.index'));
        }

        $this->lifecycle->archive($classroom);

        AuditTrail::log('Classroom', 'ARCHIVE', $classroom->classroom_id, ['is_active' => true], ['is_active' => false]);

        Flash::success('Classroom ' . $classroom->room_number . ' archived. Its timetable entries and exam records are untouched, and it no longer appears in allocation pickers.');

        return redirect(route('classrooms.show', $classroom->classroom_id));
    }

    /**
     * Bring an archived classroom back into the active pool.
     */
    public function restore($id)
    {
        $classroom = $this->classroomRepository->find($id);

        if (empty($classroom)) {
            Flash::error('Classroom not found');

            return redirect(route('classrooms.index'));
        }

        $this->lifecycle->restore($classroom);

        AuditTrail::log('Classroom', 'RESTORE', $classroom->classroom_id, ['is_active' => false], ['is_active' => true]);

        Flash::success('Classroom ' . $classroom->room_number . ' restored to the active list.');

        return redirect(route('classrooms.show', $classroom->classroom_id));
    }
}
