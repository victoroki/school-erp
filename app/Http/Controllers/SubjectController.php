<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSubjectRequest;
use App\Http\Requests\UpdateSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\SubjectRepository;
use App\Models\AuditTrail;
use App\Models\Subject;
use App\Services\SubjectLifecycleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Flash;

class SubjectController extends AppBaseController
{
    /** @var SubjectRepository $subjectRepository*/
    private $subjectRepository;

    /** @var SubjectLifecycleService $lifecycle */
    private $lifecycle;

    public function __construct(SubjectRepository $subjectRepo, SubjectLifecycleService $lifecycle)
    {
        $this->subjectRepository = $subjectRepo;
        $this->lifecycle = $lifecycle;

        $this->middleware('auth');
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy', 'archive', 'restore']);
    }

    /**
     * Display a listing of the Subject.
     */
    public function index(Request $request)
    {
        // Column lists in an eager-load constraint are literal: the key column
        // must be named as it exists in the table. Departments are keyed on
        // department_id, so selecting `id` here was a QueryException.
        $query = Subject::query()->with('department:department_id,name');

        if ($request->filled('q')) {
            $term = trim((string) $request->get('q'));
            $query->where(function ($inner) use ($term) {
                $inner->where('name', 'like', "%{$term}%")
                    ->orWhere('subject_code', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        // Default view is the working list; 'archived' is opt-in so a retired
        // subject never silently disappears from an administrator's search.
        $status = (string) $request->get('status', 'active');
        if ($status === 'archived') {
            $query->where('is_active', false);
        } elseif ($status === 'all') {
            // no filter
        } else {
            $status = 'active';
            $query->where('is_active', true);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', (int) $request->get('department_id'));
        }

        // Correlated sub-selects tell each row whether it still has history,
        // so the list can offer Archive instead of a Delete that is guaranteed
        // to be refused. One pass over the page, not an N+1 per card.
        $subjects = $this->lifecycle
            ->withUsageCounts($query)
            ->orderBy('name')
            ->paginate(15)
            ->appends($request->query());

        return view('subjects.index')
            ->with('subjects', $subjects)
            ->with('status', $status)
            ->with('departments', \App\Models\Department::query()->orderBy('name')->get(['department_id', 'name']));
    }

    /**
     * Show the form for creating a new Subject.
     */
    public function create()
    {
        return view('subjects.create');
    }

    /**
     * Store a newly created Subject in storage.
     */
    public function store(CreateSubjectRequest $request)
    {
        $input = $request->all();

        $subject = $this->subjectRepository->create($input);

        AuditTrail::log('Subject', 'CREATE', $subject->subject_id, null, $subject->toArray());

        Flash::success('Subject saved successfully.');

        return redirect(route('subjects.index'));
    }

    /**
     * Display the specified Subject.
     */
    public function show($id)
    {
        $subject = $this->subjectRepository->with(['classSubjects.class', 'teacherSubjects.staff'])->find($id);

        if (empty($subject)) {
            Flash::error('Subject not found');

            return redirect(route('subjects.index'));
        }

        return view('subjects.show')
            ->with('subject', $subject)
            // The page has to say *why* a delete is refused, and the counts
            // are the reason — so they are resolved here, not in Blade.
            ->with('usage', $this->lifecycle->usageSummary($subject))
            ->with('hasHistory', $this->lifecycle->hasHistory($subject));
    }

    /**
     * Show the form for editing the specified Subject.
     */
    public function edit($id)
    {
        $subject = $this->subjectRepository->find($id);

        if (empty($subject)) {
            Flash::error('Subject not found');

            return redirect(route('subjects.index'));
        }

        return view('subjects.edit')->with('subject', $subject);
    }

    /**
     * Update the specified Subject in storage.
     */
    public function update($id, UpdateSubjectRequest $request)
    {
        $subject = $this->subjectRepository->find($id);

        if (empty($subject)) {
            Flash::error('Subject not found');

            return redirect(route('subjects.index'));
        }

        $oldData = $subject->toArray();
        $subject = $this->subjectRepository->update($request->all(), $id);

        AuditTrail::log('Subject', 'UPDATE', $subject->subject_id, $oldData, $subject->toArray());

        Flash::success('Subject updated successfully.');

        return redirect(route('subjects.index'));
    }

    /**
     * Remove the specified Subject from storage.
     *
     * A subject that has been allocated, taught, examined or timetabled
     * cannot be deleted: six tables reference it with ON DELETE RESTRICT and
     * the resulting MySQL 1451 is exactly the failure being reported. Rather
     * than dropping those constraints or cascade-deleting a learner's marks,
     * the subject is refused with the exact blockers listed, and the
     * administrator is pointed at Archive.
     */
    public function destroy($id)
    {
        $subject = $this->subjectRepository->find($id);

        if (empty($subject)) {
            Flash::error('Subject not found');

            return redirect(route('subjects.index'));
        }

        if ($this->lifecycle->hasHistory($subject)) {
            Flash::error($this->lifecycle->deletionRefusalMessage($subject));

            return redirect(route('subjects.show', $subject->subject_id));
        }

        $oldData = $subject->toArray();

        try {
            DB::transaction(function () use ($subject) {
                $subject->delete();
            });
        } catch (\Throwable $e) {
            // Something raced us (or a table we do not know about yet holds a
            // reference). Log the real cause; never surface a raw SQL error.
            Log::error('Subject deletion failed', [
                'subject_id' => $subject->subject_id,
                'exception' => $e->getMessage(),
            ]);

            Flash::error('The subject could not be deleted. Please try again or contact an administrator.');

            return redirect(route('subjects.show', $subject->subject_id));
        }

        AuditTrail::log('Subject', 'DELETE', $id, $oldData, null);

        Flash::success('Subject deleted successfully.');

        return redirect(route('subjects.index'));
    }

    /**
     * Retire a subject that carries academic history. No dependent row is
     * touched — the marks, sittings and timetable slots stay exactly as they
     * are; the subject simply stops being offered for new allocations.
     */
    public function archive($id)
    {
        $subject = $this->subjectRepository->find($id);

        if (empty($subject)) {
            Flash::error('Subject not found');

            return redirect(route('subjects.index'));
        }

        if (! $subject->is_active) {
            Flash::info('This subject is already archived.');

            return redirect(route('subjects.show', $subject->subject_id));
        }

        $oldData = $subject->toArray();
        $this->lifecycle->archive($subject);

        AuditTrail::log('Subject', 'ARCHIVE', $subject->subject_id, $oldData, $subject->fresh()->toArray());

        Flash::success('“' . $subject->name . '” has been archived. Its history is preserved and it is no longer offered for new allocations.');

        return redirect(route('subjects.show', $subject->subject_id));
    }

    /**
     * Return an archived subject to the active list.
     */
    public function restore($id)
    {
        $subject = $this->subjectRepository->find($id);

        if (empty($subject)) {
            Flash::error('Subject not found');

            return redirect(route('subjects.index'));
        }

        if ($subject->is_active) {
            Flash::info('This subject is already active.');

            return redirect(route('subjects.show', $subject->subject_id));
        }

        $oldData = $subject->toArray();
        $this->lifecycle->restore($subject);

        AuditTrail::log('Subject', 'RESTORE', $subject->subject_id, $oldData, $subject->fresh()->toArray());

        Flash::success('“' . $subject->name . '” has been restored and can be allocated again.');

        return redirect(route('subjects.show', $subject->subject_id));
    }
}
