<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateParentsRequest;
use App\Http\Requests\UpdateParentsRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\ParentsRepository;
use App\Models\AuditTrail;
use App\Models\Parents;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Flash;

class ParentsController extends AppBaseController
{
    /** @var ParentsRepository $parentsRepository*/
    private $parentsRepository;

    public function __construct(ParentsRepository $parentsRepo)
    {
        $this->parentsRepository = $parentsRepo;
        $this->middleware('can:students.view')->only(['index', 'show']);
        $this->middleware('can:students.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the Parents.
     */
    public function index(Request $request)
    {
        // Taken before any filter is applied so the summary cards describe the
        // whole register, not the current search.
        $totalParents = Parents::query()->count();
        $totalLinked = Parents::query()->has('students')->count();

        $query = Parents::query()
            ->withCount('students')
            ->with(['user:id,name']);

        if ($request->filled('q')) {
            $q = $request->get('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%$q%")
                    ->orWhere('last_name', 'like', "%$q%")
                    ->orWhere('email', 'like', "%$q%")
                    ->orWhere('phone', 'like', "%$q%")
                    ->orWhere('occupation', 'like', "%$q%");
            });
        }

        // A parent can be linked to several students; narrow the roster to the
        // guardians of one class when the registrar is working through a single
        // class list. Enrollments hang off a class *section*, so the class is
        // reached through classSection.class_id.
        if ($request->filled('class_id')) {
            $classId = (int) $request->get('class_id');
            $query->whereHas('students', function ($student) use ($classId) {
                $student->whereHas('studentClassEnrollments', function ($enrollment) use ($classId) {
                    $enrollment->where('is_current', true)
                        ->whereHas('classSection', fn ($section) => $section->where('class_id', $classId));
                });
            });
        }

        if ($request->filled('relationship')) {
            $query->where('relationship', $request->get('relationship'));
        }

        $parents = $query->orderBy('last_name')->orderBy('first_name')
            ->paginate(15)->appends($request->query());

        $relationships = Parents::query()
            ->whereNotNull('relationship')
            ->distinct()
            ->orderBy('relationship')
            ->pluck('relationship');

        return view('parents.index')
            ->with('parents', $parents)
            ->with('totalParents', $totalParents)
            ->with('totalLinked', $totalLinked)
            ->with('relationships', $relationships);
    }

    /**
     * Show the form for creating a new Parents.
     */
    public function create()
    {
        return view('parents.create');
    }

    /**
     * Store a newly created Parents in storage.
     */
    public function store(CreateParentsRequest $request)
    {
        $input = $request->validated();

        $parents = $this->parentsRepository->create($input);

        AuditTrail::log('Parent', 'CREATE', $parents->parent_id, null, $parents->toArray());

        Flash::success('Parent saved successfully.');

        return redirect(route('parents.index'));
    }

    /**
     * Display the specified Parents, together with the learners they are
     * registered against.
     */
    public function show($id)
    {
        // The model lives in App\Models; without the import above, `Parents::`
        // would resolve to App\Http\Controllers\Parents and fatal on every
        // profile view.
        $parents = Parents::with(['user:id,name'])
            ->with(['students' => fn ($q) => $q->orderBy('last_name')->orderBy('first_name')])
            ->find($id);

        if (empty($parents)) {
            Flash::error('Parent not found.');

            return redirect(route('parents.index'));
        }

        return view('parents.show')->with('parents', $parents);
    }

    /**
     * Show the form for editing the specified Parents.
     */
    public function edit($id)
    {
        $parents = $this->parentsRepository->find($id);

        if (empty($parents)) {
            Flash::error('Parent not found.');

            return redirect(route('parents.index'));
        }

        return view('parents.edit')->with('parents', $parents);
    }

    /**
     * Update the specified Parents in storage.
     */
    public function update($id, UpdateParentsRequest $request)
    {
        $parents = $this->parentsRepository->find($id);

        if (empty($parents)) {
            Flash::error('Parent not found.');

            return redirect(route('parents.index'));
        }

        $oldData = $parents->toArray();
        $parents = $this->parentsRepository->update($request->validated(), $id);

        AuditTrail::log('Parent', 'UPDATE', $parents->parent_id, $oldData, $parents->toArray());

        Flash::success('Parent updated successfully.');

        return redirect(route('parents.show', $parents->parent_id));
    }

    /**
     * Remove the specified Parents from storage.
     *
     * A guardian row is referenced by student_parent_relationship (RESTRICT),
     * so the pivot is the only thing standing between an admin and a 1451
     * error. Detaching it is safe *only* when the parent is not the last
     * guardian a learner still has: removing the sole/primary guardian of an
     * active learner would leave the learner unreachable in an emergency and
     * cut them off from fee and communication records, so that is refused with
     * an explanation instead of silently succeeding.
     */
    public function destroy($id)
    {
        $parent = $this->parentsRepository->find($id);

        if (empty($parent)) {
            Flash::error('Parent not found.');

            return redirect(route('parents.index'));
        }

        $blocking = $this->guardianshipBlockers($parent);

        if (! empty($blocking)) {
            Flash::error(
                'This parent cannot be deleted because they are the only registered guardian for: '
                . implode('; ', $blocking)
                . '. Link another guardian to each learner first, then try again.'
            );

            return redirect(route('parents.show', $parent->parent_id));
        }

        $oldData = $parent->toArray();

        try {
            DB::transaction(function () use ($parent, $oldData) {
                $detached = DB::table('student_parent_relationship')
                    ->where('parent_id', $parent->parent_id)
                    ->delete();

                // parent_notification_preferences cascades at the database
                // level, but detaching explicitly keeps the delete symmetrical
                // and independent of the FK's onDelete clause.
                DB::table('parent_notification_preferences')
                    ->where('parent_id', $parent->parent_id)
                    ->delete();

                // The portal login (if any) outlives the guardian record: the
                // user row is shared with audit history and is not ours to
                // delete here, so the link is severed instead of cascaded.
                if ($parent->user_id) {
                    DB::table('parents')
                        ->where('parent_id', $parent->parent_id)
                        ->update(['user_id' => null]);
                }

                DB::table('parents')->where('parent_id', $parent->parent_id)->delete();

                AuditTrail::log('Parent', 'DELETE', $parent->parent_id, $oldData, [
                    'detached_relationships' => $detached,
                    'unlinked_user_id' => $parent->user_id,
                ]);
            });
        } catch (\Throwable $e) {
            Log::error('Parent deletion failed', [
                'parent_id' => $parent->parent_id,
                'exception' => $e->getMessage(),
            ]);

            Flash::error('The parent could not be deleted. Please try again or contact an administrator.');

            return redirect(route('parents.show', $parent->parent_id));
        }

        Flash::success('Parent deleted successfully.');

        return redirect(route('parents.index'));
    }

    /**
     * Learners this parent must not be removed from: the ones where they are
     * the primary contact, and the ones where they are the only guardian at
     * all. Both cases leave an active learner without a reachable guardian.
     *
     * @return array<int, string>
     */
    private function guardianshipBlockers(Parents $parent): array
    {
        $learners = DB::table('student_parent_relationship as spr')
            ->join('students', 'students.student_id', '=', 'spr.student_id')
            ->where('spr.parent_id', $parent->parent_id)
            ->whereNull('students.deleted_at')
            ->where('students.is_active', true)
            ->orderBy('students.admission_no')
            ->get([
                'students.student_id',
                'students.admission_no',
                'students.first_name',
                'students.last_name',
                'spr.is_primary_contact',
                DB::raw('(select count(*) from student_parent_relationship as other
                          where other.student_id = students.student_id) as guardian_count'),
            ]);

        $blockers = [];

        foreach ($learners as $learner) {
            $isPrimary = (bool) $learner->is_primary_contact;
            $isSole = (int) $learner->guardian_count <= 1;

            if ($isPrimary || $isSole) {
                $name = trim($learner->first_name . ' ' . $learner->last_name);
                $reason = $isSole
                    ? 'sole registered guardian'
                    : 'primary guardian';
                $blockers[] = $name . ' (' . ($learner->admission_no ?: 'no admission no') . ') — ' . $reason;
            }
        }

        return $blockers;
    }
}
