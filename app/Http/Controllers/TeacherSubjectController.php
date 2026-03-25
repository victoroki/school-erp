<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTeacherSubjectRequest;
use App\Http\Requests\UpdateTeacherSubjectRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\TeacherSubjectRepository;
use Illuminate\Http\Request;
use Flash;

// Add these model imports for dropdowns
use App\Models\Staff;
use App\Models\Subject;
use App\Models\ClassSection;
use App\Models\AcademicYear;

class TeacherSubjectController extends AppBaseController
{
    /** @var TeacherSubjectRepository $teacherSubjectRepository*/
    private $teacherSubjectRepository;

    public function __construct(TeacherSubjectRepository $teacherSubjectRepo)
    {
        $this->teacherSubjectRepository = $teacherSubjectRepo;

        $this->middleware('auth');
        $this->middleware('can:teacher-subjects.index')->only(['index', 'show']);
        $this->middleware('can:teacher-subjects.create')->only(['create', 'store']);
        $this->middleware('can:teacher-subjects.edit')->only(['edit', 'update']);
        $this->middleware('can:teacher-subjects.delete')->only('destroy');
    }

    /**
     * Display a listing of TeacherSubjects grouped by Academic Year, paginated by teacher.
     */
    public function index(Request $request)
    {
        // ── Academic Year ─────────────────────────────────────────────────
        $academicYears  = AcademicYear::orderByDesc('start_date')->get();
        $selectedYearId = $request->get('academic_year_id');

        if (!$selectedYearId) {
            $current        = $academicYears->firstWhere('is_current', true);
            $selectedYearId = $current
                ? $current->academic_year_id
                : optional($academicYears->first())->academic_year_id;
        }

        // ── Optional teacher filter ───────────────────────────────────────
        $selectedStaffId = $request->get('staff_id');

        // ── Step 1: paginate distinct staff IDs for this year ─────────────
        // We query the teacher_subjects table to get only IDs of teachers
        // who actually have assignments in the selected year, then paginate
        // that list 8 teachers per page.
        $staffQuery = \App\Models\TeacherSubject::query()
            ->select('staff_id')
            ->where('academic_year_id', $selectedYearId)
            ->when($selectedStaffId, fn($q) => $q->where('staff_id', $selectedStaffId))
            ->distinct()
            ->orderBy('staff_id');

        // Use a simple manual paginator so the count reflects unique teachers
        $perPage        = 8;
        $currentPage    = (int) $request->get('page', 1);
        $totalTeachers  = (clone $staffQuery)->count();

        $staffIds = $staffQuery
            ->offset(($currentPage - 1) * $perPage)
            ->limit($perPage)
            ->pluck('staff_id');

        // Build paginator manually (preserves query string in links)
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $staffIds,
            $totalTeachers,
            $perPage,
            $currentPage,
            [
                'path'  => $request->url(),
                'query' => $request->query(),       // keeps year/staff filters in page links
            ]
        );

        // ── Step 2: load all assignments for just this page's teachers ────
        $assignments = \App\Models\TeacherSubject::with([
                'staff',
                'subject',
                'classSection.class',
                'classSection.section',
            ])
            ->where('academic_year_id', $selectedYearId)
            ->whereIn('staff_id', $staffIds)
            ->get();

        // Group by staff_id preserving the paginated order
        $grouped = $staffIds->mapWithKeys(
            fn($id) => [$id => $assignments->where('staff_id', $id)]
        );

        // ── Staff filter dropdown (all teachers for this year) ────────────
        $teacherOptions = \App\Models\TeacherSubject::with('staff')
            ->where('academic_year_id', $selectedYearId)
            ->get()
            ->pluck('staff')
            ->filter()
            ->unique('staff_id')
            ->mapWithKeys(fn($s) => [$s->staff_id => $s->full_name])
            ->sort();

        return view('teacher_subjects.index', [
            'grouped'         => $grouped,
            'paginator'       => $paginator,
            'totalTeachers'   => $totalTeachers,
            'totalAssignments'=> $assignments->count(),
            'academicYears'   => $academicYears,
            'selectedYearId'  => $selectedYearId,
            'teacherOptions'  => $teacherOptions,
            'selectedStaffId' => $selectedStaffId,
        ]);
    }



    /**
     * Show the form for creating a new TeacherSubject.
     */
    public function create()
    {
        // Get dropdown data
        $dropdownData = $this->getDropdownData();
        
        // Find current year to pre-select it
        $currentYear = AcademicYear::where('is_current', true)->first();
        $dropdownData['currentYearId'] = $currentYear ? $currentYear->academic_year_id : null;
        
        return view('teacher_subjects.create', $dropdownData);
    }

    /**
     * Store a newly created TeacherSubject in storage.
     */
    public function store(CreateTeacherSubjectRequest $request)
    {
        $input = $request->all();

        $teacherSubject = $this->teacherSubjectRepository->create($input);

        Flash::success('Teacher Subject saved successfully.');

        return redirect(route('teacher-subjects.index', ['academic_year_id' => $input['academic_year_id'] ?? null]));
    }

    /**
     * Display the specified TeacherSubject.
     */
    public function show($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacher-subjects.index'));
        }

        return view('teacher_subjects.show')->with('teacherSubject', $teacherSubject);
    }

    /**
     * Show the form for editing the specified TeacherSubject.
     */
    public function edit($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacher-subjects.index'));
        }

        // Get dropdown data for edit form
        $dropdownData = $this->getDropdownData();
        $dropdownData['teacherSubject'] = $teacherSubject;
        $dropdownData['currentYearId'] = $teacherSubject->academic_year_id;

        return view('teacher_subjects.edit', $dropdownData);
    }

    /**
     * Update the specified TeacherSubject in storage.
     */
    public function update($id, UpdateTeacherSubjectRequest $request)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacher-subjects.index'));
        }

        $teacherSubject = $this->teacherSubjectRepository->update($request->all(), $id);

        Flash::success('Teacher Subject updated successfully.');

        return redirect(route('teacher-subjects.index'));
    }

    /**
     * Remove the specified TeacherSubject from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $teacherSubject = $this->teacherSubjectRepository->find($id);

        if (empty($teacherSubject)) {
            Flash::error('Teacher Subject not found');

            return redirect(route('teacher-subjects.index'));
        }

        $this->teacherSubjectRepository->delete($id);

        Flash::success('Teacher Subject deleted successfully.');

        return redirect(route('teacher-subjects.index'));
    }

/**
     * Get dropdown data for forms (Simplified approach)
     */
    private function getDropdownData()
    {
        // Staff dropdown with concatenated names
        $staff = Staff::select('staff_id', 'first_name', 'middle_name', 'last_name')->get();
        $staffList = collect(['Select Staff']);
        foreach ($staff as $member) {
            $fullName = trim($member->first_name . ' ' . $member->middle_name . ' ' . $member->last_name);
            $staffList[$member->staff_id] = $fullName;
        }

        // Class Section dropdown with descriptive names
        $classSections = ClassSection::with(['class', 'section'])->get();
        $classSectionList = collect(['' => 'Select Class Section']);
        foreach ($classSections as $cs) {
            $className = optional($cs->class)->name ?? 'Unknown Class';
            $sectionName = optional($cs->section)->name ?? 'Unknown Section';
            $classSectionList[$cs->class_section_id] = "{$className} - {$sectionName}";
        }

        return [
            'staffList' => $staffList,
            'subjectList' => Subject::pluck('name', 'subject_id')->prepend('Select Subject', ''),
            'classSectionList' => $classSectionList,
            'academicYearList' => AcademicYear::orderByDesc('start_date')->pluck('name', 'academic_year_id')->prepend('Select Academic Year', '')
        ];
    }
}
