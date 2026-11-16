<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClassSubjectRequest;
use App\Http\Requests\UpdateClassSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Repositories\ClassSubjectRepository;
use App\Models\AuditTrail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Flash;
use App\Models\SchoolClass; 
use App\Models\Subject;     


class ClassSubjectController extends AppBaseController
{
    /** @var ClassSubjectRepository $classSubjectRepository*/
    private $classSubjectRepository;

    public function __construct(ClassSubjectRepository $classSubjectRepo)
    {
        $this->classSubjectRepository = $classSubjectRepo;

        $this->middleware('auth');
        $this->middleware('can:academics.view')->only(['index', 'show']);
        $this->middleware('can:academics.settings.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * The academic year this screen manages.
     *
     * Assignments are stored per year, but classes are not: a class keeps the
     * same identity every year, so listing every year showed the same class
     * over and over. The page is scoped to the year flagged current and names
     * that year in the header, so the scope is never ambiguous.
     */
    private function currentAcademicYear(): ?AcademicYear
    {
        return AcademicYear::where('is_current', true)
            ->orderByDesc('start_date')
            ->orderByDesc('academic_year_id')
            ->first();
    }

    /**
     * Get dropdown data for forms
     */
    private function getDropdownData()
    {
        $currentYear = $this->currentAcademicYear();

        return [
            'classes' => SchoolClass::pluck('name', 'class_id')->toArray(),
            'subjects' => Subject::pluck('name', 'subject_id')->toArray(),
            'academicYear' => AcademicYear::orderByDesc('start_date')
                ->get()
                ->mapWithKeys(fn (AcademicYear $year) => [
                    $year->academic_year_id => $year->name . ($year->is_current ? ' (current)' : ''),
                ])
                ->all(),
            // New assignments belong to the year the curriculum page is showing.
            'defaultAcademicYearId' => $currentYear?->academic_year_id,
        ];
    }

    /**
     * Display a listing of the ClassSubject.
     */
    public function index(Request $request)
    {
        $currentYear = $this->currentAcademicYear();

        $rows = $currentYear
            ? $this->classSubjectRepository
                ->with(['class', 'subject'])
                ->where('academic_year_id', $currentYear->academic_year_id)
                ->get()
            : collect();

        // One card per class, and nothing more. The subject rows themselves are
        // served by curriculum() when a card is opened — shipping them inline
        // made this page render every assignment in the year, and each one
        // carried its own <form>, CSRF token and inline confirm() handler, so a
        // 14-class school produced 758 KB of HTML and 350 forms before anything
        // appeared on screen.
        $groups = $rows
            ->sortBy([
                fn ($a, $b) => ($a->class->numeric_value ?? 999) <=> ($b->class->numeric_value ?? 999),
                fn ($a, $b) => strcasecmp((string) ($a->class->name ?? ''), (string) ($b->class->name ?? '')),
            ])
            ->groupBy('class_id')
            ->map(function ($classRows) {
                $first = $classRows->first();

                return (object) [
                    'class_id' => $first->class_id,
                    'class_name' => $first->class->name ?? 'Unassigned',
                    'class_grade' => $first->class->numeric_value,
                    'academic_year_id' => $first->academic_year_id,
                    'subject_count' => $classRows->count(),
                    'weekly_periods' => $classRows->sum(fn ($cs) => (int) ($cs->periods_per_week ?: 0)),
                    'archived_count' => $classRows->filter(fn ($cs) => ! ($cs->subject->is_active ?? true))->count(),
                    // One lowercase string per class so the search box is a
                    // single substring test per card. Subject names live here
                    // rather than in the DOM, which is what keeps a card
                    // searchable before its list has been loaded.
                    'search' => mb_strtolower(trim(
                        ($first->class->name ?? '') . ' '
                        . $classRows->pluck('subject.name')->filter()->implode(' ')
                    )),
                ];
            })
            ->values();

        return view('class_subjects.index')
            ->with('classSubjects', $groups)
            ->with('currentYear', $currentYear)
            ->with('totalAssignments', $rows->count())
            ->with('totalPeriods', $groups->sum('weekly_periods'));
    }

    /**
     * Subject assignments for the current academic year, grouped by class.
     *
     * The grid renders card shells only and asks for the subjects when a card
     * is opened, so this is the single place the assignments themselves are
     * serialised. Always the current year: the page has no year filter, and
     * the client cannot widen the scope.
     */
    public function curriculum(): JsonResponse
    {
        $currentYear = $this->currentAcademicYear();

        if (! $currentYear) {
            return response()->json(['year' => null, 'classes' => (object) []]);
        }

        $classes = ClassSubject::with('subject')
            ->where('academic_year_id', $currentYear->academic_year_id)
            ->get()
            ->groupBy('class_id')
            ->map(function ($rows) {
                return $rows
                    ->sortBy(fn ($cs) => strcasecmp((string) $cs->subject->name, (string) $cs->subject->name))
                    ->map(fn ($cs) => [
                        'id' => (int) $cs->class_subject_id,
                        'name' => $cs->subject->name,
                        'periods' => (int) ($cs->periods_per_week ?: 0),
                        'archived' => ! ($cs->subject->is_active ?? true),
                        'show_url' => route('class-subjects.show', [$cs->class_subject_id]),
                        'edit_url' => route('class-subjects.edit', [$cs->class_subject_id]),
                        'delete_url' => route('class-subjects.destroy', [$cs->class_subject_id]),
                    ])
                    ->values()
                    ->all();
            })
            ->all();

        return response()->json([
            'year' => [
                'id' => (int) $currentYear->academic_year_id,
                'name' => $currentYear->name,
            ],
            'classes' => $classes,
        ]);
    }

    /**
     * Show the form for creating a new ClassSubject.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('class_subjects.create', $dropdownData);
    }

    /**
     * Store a newly created ClassSubject in storage.
     */
    public function store(CreateClassSubjectRequest $request)
    {
        $input = $request->all();

        // Check if subject_id is an array (multi-select)
        if (isset($input['subject_id']) && is_array($input['subject_id'])) {
            $periodsPerWeek = (int) ($input['periods_per_week'] ?? 1);

            // class_subjects has no unique key on (class, subject, year), so
            // re-posting the bulk grid — or ticking a subject that is already
            // assigned — used to add a second identical curriculum row for the
            // same class. Assigning what is already assigned is a no-op, not an
            // error, so those subjects are skipped and reported.
            $alreadyAssigned = ClassSubject::where('class_id', $input['class_id'])
                ->where('academic_year_id', $input['academic_year_id'])
                ->pluck('subject_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $created = 0;
            $skipped = 0;

            foreach (array_unique($input['subject_id']) as $subjectId) {
                if (in_array((int) $subjectId, $alreadyAssigned, true)) {
                    $skipped++;
                    continue;
                }

                $classSubject = $this->classSubjectRepository->create([
                    'class_id' => $input['class_id'],
                    'subject_id' => $subjectId,
                    'academic_year_id' => $input['academic_year_id'],
                    'periods_per_week' => $periodsPerWeek
                ]);
                AuditTrail::log('Class Subject', 'CREATE', $classSubject->class_subject_id, null, $classSubject->toArray());
                $created++;
            }

            // A form submitted with nothing actually added must not report success.
            // Every message names the year, because the index only lists the
            // current one — an assignment made against another year looks like
            // it vanished unless the flash says where it went.
            $yearLabel = AcademicYear::whereKey($input['academic_year_id'])->value('name')
                ?? $input['academic_year_id'];

            if ($created === 0) {
                Flash::warning(
                    $skipped > 0
                        ? "No change: $skipped selected subject(s) are already assigned to this class for $yearLabel."
                        : 'No subjects were selected, so nothing was assigned.'
                );
            } else {
                Flash::success(
                    "$created subject(s) assigned to the class for $yearLabel."
                    . ($skipped > 0 ? " $skipped already assigned subject(s) were skipped." : '')
                );
            }
        } else {
            $classSubject = $this->classSubjectRepository->create($input);
            AuditTrail::log('Class Subject', 'CREATE', $classSubject->class_subject_id, null, $classSubject->toArray());

            $yearLabel = AcademicYear::whereKey($classSubject->academic_year_id)->value('name')
                ?? $classSubject->academic_year_id;

            Flash::success("Class Subject saved for $yearLabel.");
        }

        return redirect(route('class-subjects.index'));
    }

    /**
     * Display the specified ClassSubject.
     */
    public function show($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('class-subjects.index'));
        }

        // Eager load relations for display
        $classSubject->load(['class', 'subject', 'academicYear']);

        return view('class_subjects.show')->with('classSubject', $classSubject);
    }

    /**
     * Show the form for editing the specified ClassSubject.
     */
    public function edit($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('class-subjects.index'));
        }

        $dropdownData = $this->getDropdownData();
        
        return view('class_subjects.edit', array_merge(
            ['classSubject' => $classSubject],
            $dropdownData
        ));
    }

    /**
     * Update the specified ClassSubject in storage.
     */
    public function update($id, UpdateClassSubjectRequest $request)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('class-subjects.index'));
        }

        $oldData = $classSubject->toArray();
        $classSubject = $this->classSubjectRepository->update($request->all(), $id);

        AuditTrail::log('Class Subject', 'UPDATE', $classSubject->class_subject_id, $oldData, $classSubject->toArray());

        Flash::success('Class Subject updated successfully.');

        return redirect(route('class-subjects.index'));
    }

    /**
     * Remove the specified ClassSubject from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $classSubject = $this->classSubjectRepository->find($id);

        if (empty($classSubject)) {
            Flash::error('Class Subject not found');

            return redirect(route('class-subjects.index'));
        }

        $oldData = $classSubject->toArray();
        $this->classSubjectRepository->delete($id);

        AuditTrail::log('Class Subject', 'DELETE', $id, $oldData, null);

        Flash::success('Class Subject deleted successfully.');

        return redirect(route('class-subjects.index'));
    }

    /**
     * Get subjects filtered by class grade level (AJAX).
     * Subjects without a grade_level are always included (general subjects).
     * Subjects matching the class's numeric_value (grade level) are included.
     */
    public function getSubjectsByClass($classId)
    {
        $class = SchoolClass::find($classId);
        if (!$class) {
            return response()->json([]);
        }

        $gradeLevel = $class->numeric_value;

        $subjects = Subject::where(function ($query) use ($gradeLevel) {
                $query->whereNull('grade_level')
                    ->orWhere('grade_level', $gradeLevel);
            })
            ->orderBy('name')
            ->get(['subject_id', 'name', 'grade_level'])
            ->map(fn($s) => [
                'id' => $s->subject_id,
                'name' => $s->name,
                'grade_level' => $s->grade_level,
            ]);

        return response()->json($subjects);
    }

    /**
     * Remove all subjects assigned to a specific class.
     */
    public function bulkDestroy(Request $request)
    {
        $classId = $request->input('class_id');

        if (empty($classId)) {
            Flash::error('Class selection is required for bulk deletion.');
            return redirect(route('class-subjects.index'));
        }

        // Scope the clear to ONE academic year. This previously deleted every
        // subject assignment for the class across every year, so clearing the
        // current year silently destroyed the record of what had been taught in
        // previous years.
        $academicYearId = $request->input('academic_year_id')
            ?: AcademicYear::where('is_current', true)->value('academic_year_id');

        if (empty($academicYearId)) {
            Flash::error('No academic year selected and none is marked current, so nothing was cleared.');

            return redirect(route('class-subjects.index'));
        }

        $deletedCount = ClassSubject::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->delete();

        AuditTrail::log('Class Subject', 'BULK DELETE', $classId, [
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
        ], ['deleted_count' => $deletedCount]);

        $yearLabel = AcademicYear::whereKey($academicYearId)->value('name') ?? $academicYearId;

        Flash::success("Cleared $deletedCount subject(s) from the class for $yearLabel.");

        return redirect(route('class-subjects.index'));
    }
}
