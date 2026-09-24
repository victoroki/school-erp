<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateClassSubjectRequest;
use App\Http\Requests\UpdateClassSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\AcademicYear;
use App\Models\ClassSubject;
use App\Repositories\ClassSubjectRepository;
use App\Models\AuditTrail;
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
     * Get dropdown data for forms
     */
    private function getDropdownData()
    {
        return [
            'classes' => SchoolClass::pluck('name', 'class_id')->toArray(),
            'subjects' => Subject::pluck('name', 'subject_id')->toArray(),
            'academicYear' => AcademicYear::pluck('name', 'academic_year_id')
        ];
    }

    /**
     * Display a listing of the ClassSubject.
     */
    public function index(Request $request)
    {
        $allClassSubjects = $this->classSubjectRepository->with(['class','subject','academicYear'])->get();

        // Group by Class Name
        $classSubjects = $allClassSubjects->groupBy(function($classSubject) {
            return $classSubject->class ? $classSubject->class->name : 'Unassigned';
        });

        return view('class_subjects.index')
            ->with('classSubjects', $classSubjects);
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
            if ($created === 0) {
                Flash::warning(
                    $skipped > 0
                        ? 'No change: ' . $skipped . ' selected subject(s) are already assigned to this class.'
                        : 'No subjects were selected, so nothing was assigned.'
                );
            } else {
                Flash::success(
                    'Subjects assigned to class successfully.'
                    . ($skipped > 0 ? ' ' . $skipped . ' already assigned subject(s) were skipped.' : '')
                );
            }
        } else {
            $classSubject = $this->classSubjectRepository->create($input);
            AuditTrail::log('Class Subject', 'CREATE', $classSubject->class_subject_id, null, $classSubject->toArray());
            Flash::success('Class Subject saved successfully.');
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
            ?: \App\Models\AcademicYear::where('is_current', true)->value('academic_year_id');

        if (empty($academicYearId)) {
            Flash::error('No academic year selected and none is marked current, so nothing was cleared.');

            return redirect(route('class-subjects.index'));
        }

        $deletedCount = \App\Models\ClassSubject::where('class_id', $classId)
            ->where('academic_year_id', $academicYearId)
            ->delete();

        AuditTrail::log('Class Subject', 'BULK DELETE', $classId, [
            'class_id' => $classId,
            'academic_year_id' => $academicYearId,
        ], ['deleted_count' => $deletedCount]);

        $yearLabel = \App\Models\AcademicYear::whereKey($academicYearId)->value('name') ?? $academicYearId;

        Flash::success("Cleared $deletedCount subject(s) from the class for $yearLabel.");

        return redirect(route('class-subjects.index'));
    }
}
