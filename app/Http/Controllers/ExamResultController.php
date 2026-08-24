<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateExamResultRequest;
use App\Http\Requests\UpdateExamResultRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Exam;
use App\Models\Student;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\GradingScale;
use App\Repositories\ExamResultRepository;
use App\Services\TeacherScopeService;
use Illuminate\Http\Request;
use Flash;
use Auth;
use DB;
use App\Models\ExamResult;
use App\Models\AuditTrail;

class ExamResultController extends AppBaseController
{
    /** @var ExamResultRepository */
    private $examResultRepository;

    private TeacherScopeService $teacherScope;

    public function __construct(ExamResultRepository $examResultRepo, TeacherScopeService $teacherScope)
    {
        $this->examResultRepository = $examResultRepo;
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.results.view-own')->only(['index', 'show', 'studentsByClass']);
        $this->middleware('can:exams.marks.enter-own')->only(['bulk', 'postBulk']);
        $this->middleware('can:exams.import')->only(['importTemplate', 'importStore']);
        $this->middleware('can:exams.marks.enter-own')->only(['store', 'update', 'destroy', 'create', 'edit']);
        $this->middleware('can:exams.marks.enter-own')->only(['saveOne']);
    }

    public function bulk(Request $request)
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if ($viewAll || $hasSettings) {
            $exams = Exam::pluck('name', 'exam_id');
            $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
                return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
            });
            $subjects = Subject::pluck('name', 'subject_id');
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $subjectIds = $this->teacherScope->getSubjectIds($user);

            $exams = $this->teacherScope->scopeExams(Exam::query(), $user)->pluck('name', 'exam_id');

            $classSections = ClassSection::with(['schoolClass', 'section'])
                ->whereIn('class_section_id', $classSectionIds)
                ->get()
                ->mapWithKeys(function ($cs) {
                    return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
                });

            $subjects = Subject::whereIn('subject_id', $subjectIds)->pluck('name', 'subject_id');
        }

        $students = [];
        $existingResults = [];

        // Real maximum marks from the exam schedule for this sitting (fallback 100)
        // Note: exam_schedules stores class_id, the request carries class_section_id.
        $maxMarks = 100;
        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            $classSection = ClassSection::find($request->class_section_id);
            if ($classSection) {
                $scheduledMax = \App\Models\ExamSchedule::where('exam_id', $request->exam_id)
                    ->where('class_id', $classSection->class_id)
                    ->where('subject_id', $request->subject_id)
                    ->max('max_marks');
                if ($scheduledMax) {
                    $maxMarks = (int) $scheduledMax;
                }
            }
        }

        // Grading scale for live grade feedback while typing
        $grades = GradingScale::orderByDesc('min_percentage')
            ->get(['name', 'min_percentage', 'max_percentage', 'grade_point', 'description']);

        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            if (!$viewAll && !$hasSettings) {
                $allowedIds = $this->teacherScope->getClassSectionIds($user);
                $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
                if (!$allowedIds->contains((int) $request->class_section_id) || !$allowedSubjectIds->contains((int) $request->subject_id)) {
                    Flash::error('You are not authorized to enter marks for this class or subject.');
                    return redirect()->route('exam-results.bulk');
                }
            }

            // Existing marks are fetched for the WHOLE class so counters and
            // grade chips stay accurate even though the list is paginated.
            $existingResults = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->where('subject_id', $request->subject_id)
                ->pluck('marks_obtained', 'student_id')
                ->toArray();

            $studentQuery = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id)
                  ->where('status', 'active');
            })->orderBy('last_name');

            $totalStudents = (clone $studentQuery)->count();
            $students = $studentQuery->paginate(25)->withQueryString();
        }

        $totalRecorded = count(array_filter($existingResults, fn ($v) => $v !== null && $v !== ''));

        return view('exam_results.bulk', compact(
            'exams', 'classSections', 'subjects', 'students', 'existingResults',
            'maxMarks', 'grades', 'totalStudents', 'totalRecorded'
        ));
    }

    /**
     * AJAX: save one student's mark instantly (used by the inline entry table).
     */
    public function saveOne(Request $request)
    {
        $data = $request->validate([
            'exam_id'          => 'required|integer',
            'class_section_id' => 'required|integer',
            'subject_id'       => 'required|integer',
            'student_id'       => 'required|integer',
            'marks_obtained'   => 'nullable|numeric|min:0',
            'remarks'          => 'nullable|string|max:255',
        ]);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $data['class_section_id']) || !$allowedSubjectIds->contains((int) $data['subject_id'])) {
                return response()->json(['ok' => false, 'message' => 'Not authorized'], 403);
            }
        }

        // Percentage + grade feedback (authoritative, from the scale)
        // exam_schedules keys on class_id — resolve it from the section.
        $classSection = ClassSection::find($data['class_section_id']);
        $maxMarks = (int) (\App\Models\ExamSchedule::where('exam_id', $data['exam_id'])
            ->when($classSection, fn ($q) => $q->where('class_id', $classSection->class_id))
            ->where('subject_id', $data['subject_id'])
            ->max('max_marks') ?: 100);

        $grade = null;
        $percentage = null;

        if ($data['marks_obtained'] !== null && $data['marks_obtained'] !== '') {
            $percentage = round(($data['marks_obtained'] / $maxMarks) * 100, 2);
            $grade = GradingScale::where('min_percentage', '<=', $percentage)
                ->where('max_percentage', '>=', $percentage)
                ->first(['name', 'grade_point', 'description']);
        }

        $key = [
            'exam_id' => $data['exam_id'],
            'student_id' => $data['student_id'],
            'class_section_id' => $data['class_section_id'],
            'subject_id' => $data['subject_id'],
        ];

        if ($data['marks_obtained'] === null || $data['marks_obtained'] === '') {
            ExamResult::where($key)->delete();

            return response()->json([
                'ok' => true,
                'deleted' => true,
                'percentage' => null,
                'grade' => null,
            ]);
        }

        ExamResult::updateOrCreate($key, [
            'marks_obtained' => $data['marks_obtained'],
            'remarks' => $data['remarks'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'ok' => true,
            'deleted' => false,
            'percentage' => $percentage,
            'max_marks' => $maxMarks,
            'grade' => $grade ? ['name' => $grade->name, 'point' => $grade->grade_point, 'description' => $grade->description] : null,
        ]);
    }

    public function postBulk(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'subject_id' => 'required',
            'marks' => 'required|array'
        ]);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $request->class_section_id) || !$allowedSubjectIds->contains((int) $request->subject_id)) {
                Flash::error('You are not authorized to enter marks for this class or subject.');
                return redirect()->back();
            }
        }

        $exam_id = $request->exam_id;
        $class_section_id = $request->class_section_id;
        $subject_id = $request->subject_id;
        $marks = $request->marks;
        $remarks_arr = $request->remarks ?? [];

        DB::transaction(function () use ($exam_id, $class_section_id, $subject_id, $marks, $remarks_arr) {
            foreach ($marks as $student_id => $mark) {
                if ($mark !== null && $mark !== '') {
                    ExamResult::updateOrCreate(
                        [
                            'exam_id' => $exam_id,
                            'student_id' => $student_id,
                            'class_section_id' => $class_section_id,
                            'subject_id' => $subject_id
                        ],
                        [
                            'marks_obtained' => $mark,
                            'remarks' => $remarks_arr[$student_id] ?? null,
                            'created_by' => Auth::id()
                        ]
                    );
                }
            }
        });

        AuditTrail::log('Exam Result', 'BULK UPDATE', $exam_id, null, [
            'exam_id' => $exam_id,
            'class_section_id' => $class_section_id,
            'subject_id' => $subject_id,
            'students_updated' => count($marks),
        ]);

        Flash::success('Marks updated successfully.');
        return redirect()->back()->withInput();
    }

    public function importTemplate(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'subject_id' => 'required',
        ]);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $request->class_section_id) || !$allowedSubjectIds->contains((int) $request->subject_id)) {
                Flash::error('You are not authorized to export marks template for this class or subject.');
                return redirect()->back();
            }
        }

        $exam = Exam::findOrFail($request->exam_id);
        $classSection = ClassSection::with(['schoolClass', 'section'])->findOrFail($request->class_section_id);
        $subject = Subject::findOrFail($request->subject_id);

        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
            $q->where('class_section_id', $request->class_section_id)
              ->where('status', 'active');
        })->orderBy('last_name')->get();

        $filename = "marks_entry_" . str_replace(' ', '_', strtolower($exam->name)) . "_" . str_replace(' ', '_', strtolower($classSection->schoolClass->name)) . ".csv";
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['STUDENT_ID', 'ADMISSION_NO', 'STUDENT_NAME', 'MARKS_OBTAINED', 'REMARKS']);

            foreach ($students as $student) {
                fputcsv($file, [
                    $student->student_id,
                    $student->admission_no,
                    $student->full_name,
                    '',
                    ''
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'subject_id' => 'required',
            'excel_file' => 'required|file|mimes:csv,txt'
        ]);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $request->class_section_id) || !$allowedSubjectIds->contains((int) $request->subject_id)) {
                Flash::error('You are not authorized to import marks for this class or subject.');
                return redirect()->back();
            }
        }

        $exam_id = $request->exam_id;
        $class_section_id = $request->class_section_id;
        $subject_id = $request->subject_id;
        $file = $request->file('excel_file');

        $importCount = 0;

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            fgetcsv($handle);
            
            DB::beginTransaction();
            try {
                while (($data = fgetcsv($handle)) !== FALSE) {
                    if (count($data) < 4) continue;

                    $student_id = $data[0];
                    $marks = $data[3];
                    $remarks = $data[4] ?? null;

                    if ($student_id && ($marks !== '' && $marks !== null)) {
                        ExamResult::updateOrCreate(
                            [
                                'exam_id' => $exam_id,
                                'student_id' => $student_id,
                                'class_section_id' => $class_section_id,
                                'subject_id' => $subject_id
                            ],
                            [
                                'marks_obtained' => $marks,
                                'remarks' => $remarks,
                                'created_by' => Auth::id()
                            ]
                        );
                        $importCount++;
                    }
                }
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Flash::error('Error processing file: ' . $e->getMessage());
                return redirect()->back();
            }
            fclose($handle);
        }

        AuditTrail::log('Exam Result', 'IMPORT', $exam_id, null, [
            'exam_id' => $exam_id,
            'class_section_id' => $class_section_id,
            'subject_id' => $subject_id,
            'results_imported' => $importCount,
        ]);

        Flash::success("Successfully imported $importCount results.");
        return redirect()->back()->withInput();
    }

    private function getDropdownData()
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if ($viewAll || $hasSettings) {
            $students = Student::all()->mapWithKeys(function ($student) {
                return [$student->student_id => $student->first_name . ' ' . $student->last_name . ' (' . $student->admission_no . ')'];
            });

            $classSections = ClassSection::with(['class', 'section'])->get()->mapWithKeys(function ($cs) {
                $name = ($cs->class->name ?? 'N/A') . ' - ' . ($cs->section->name ?? 'N/A');
                return [$cs->class_section_id => $name];
            });

            return [
                'exams' => Exam::pluck('name', 'exam_id'),
                'students' => $students,
                'classSections' => $classSections,
                'subjects' => Subject::pluck('name', 'subject_id'),
            ];
        }

        $classSectionIds = $this->teacherScope->getClassSectionIds($user);
        $subjectIds = $this->teacherScope->getSubjectIds($user);

        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($classSectionIds) {
            $q->whereIn('class_section_id', $classSectionIds);
        })->get()->mapWithKeys(function ($student) {
            return [$student->student_id => $student->first_name . ' ' . $student->last_name . ' (' . $student->admission_no . ')'];
        });

        $classSections = ClassSection::with(['class', 'section'])
            ->whereIn('class_section_id', $classSectionIds)
            ->get()
            ->mapWithKeys(function ($cs) {
                $name = ($cs->class->name ?? 'N/A') . ' - ' . ($cs->section->name ?? 'N/A');
                return [$cs->class_section_id => $name];
            });

        return [
            'exams' => $this->teacherScope->scopeExams(Exam::query(), $user)->pluck('name', 'exam_id'),
            'students' => $students,
            'classSections' => $classSections,
            'subjects' => Subject::whereIn('subject_id', $subjectIds)->pluck('name', 'subject_id'),
        ];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        $scopeIds = ($viewAll || $hasSettings) ? null : $this->teacherScope->getClassSectionIds($user);

        // Filter dropdowns (scoped for class teachers).
        $examQuery = Exam::orderByDesc('exam_id');
        $classSectionQuery = ClassSection::with(['schoolClass', 'section']);
        if ($scopeIds !== null) {
            $classSectionQuery->whereIn('class_section_id', $scopeIds);
            $usedExamIds = ExamResult::whereIn('class_section_id', $scopeIds)->distinct()->pluck('exam_id');
            $examQuery->whereIn('exam_id', $usedExamIds);
        }

        $exams = $examQuery->pluck('name', 'exam_id');
        $classSections = $classSectionQuery->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });

        $filtered = $request->filled(['exam_id', 'class_section_id']);

        // ── Grouped overview: one summary row per exam × class stream ──
        $summaryQuery = ExamResult::query()
            ->selectRaw('exam_id, class_section_id,
                COUNT(*) AS records_count,
                SUM(is_approved) AS approved_count,
                SUM(NOT is_approved) AS pending_count,
                AVG(marks_obtained) AS avg_marks,
                MIN(created_at) AS latest_entry')
            ->groupBy('exam_id', 'class_section_id')
            ->orderByDesc('latest_entry');

        if ($scopeIds !== null) {
            $summaryQuery->whereIn('class_section_id', $scopeIds);
        }
        if ($request->filled('exam_id')) {
            $summaryQuery->where('exam_id', $request->exam_id);
        }
        if ($request->filled('class_section_id')) {
            $summaryQuery->where('class_section_id', $request->class_section_id);
        }

        $examNameMap = $exams;
        $classNameMap = $classSections;

        $groups = $summaryQuery->get()->map(function ($group) use ($examNameMap, $classNameMap) {
            $group->exam_name = $examNameMap[$group->exam_id] ?? 'Exam #' . $group->exam_id;
            $group->class_name = $classNameMap[$group->class_section_id] ?? 'Class #' . $group->class_section_id;

            return $group;
        });

        // ── Detailed records: shown once an exam + class are selected ──
        $examResults = null;

        if ($filtered) {
            $recordsQuery = ExamResult::with(['student', 'subject', 'exam', 'grade', 'classSection.schoolClass', 'classSection.section'])
                ->where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->orderByDesc('updated_at');

            if ($request->filled('subject_id')) {
                $recordsQuery->where('subject_id', $request->subject_id);
            }

            if ($request->input('approval') === 'pending') {
                $recordsQuery->where('is_approved', false);
            } elseif ($request->input('approval') === 'approved') {
                $recordsQuery->where('is_approved', true);
            }

            if (!$viewAll && !$hasSettings && !$scopeIds->contains((int) $request->class_section_id)) {
                abort(403, 'You are not authorized to view results for this class.');
            }

            $examResults = $recordsQuery->paginate(25)->withQueryString();

            $subjectsForFilter = Subject::whereIn('subject_id',
                ExamResult::where('exam_id', $request->exam_id)
                    ->where('class_section_id', $request->class_section_id)
                    ->distinct()->pluck('subject_id')
            )->pluck('name', 'subject_id');
        } else {
            $subjectsForFilter = collect();
        }

        return view('exam_results.index')
            ->with('examResults', $examResults)
            ->with('exams', $exams)
            ->with('classSections', $classSections)
            ->with('subjectsForFilter', $subjectsForFilter)
            ->with('groups', $groups)
            ->with('filtered', $filtered);
    }

    public function create()
    {
        $dropdownData = $this->getDropdownData();

        // For live grade feedback while typing
        $grades = GradingScale::orderByDesc('min_percentage')
            ->get(['name', 'min_percentage', 'max_percentage', 'grade_point', 'description']);

        return view('exam_results.create', array_merge($dropdownData, ['grades' => $grades]));
    }

    /**
     * AJAX: students enrolled in a class section, plus any marks already
     * recorded for the given exam+subject and the sitting's max marks.
     */
    public function studentsByClass(Request $request)
    {
        $data = $request->validate([
            'class_section_id' => 'required|integer',
            'exam_id'          => 'nullable|integer',
            'subject_id'       => 'nullable|integer',
        ]);

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            if (!$allowedIds->contains((int) $data['class_section_id'])) {
                return response()->json(['ok' => false, 'message' => 'Not authorized for this class'], 403);
            }
        }

        $students = Student::whereHas('studentClassEnrollments', function ($q) use ($data) {
                $q->where('class_section_id', $data['class_section_id'])
                  ->where('status', 'active');
            })
            ->orderBy('last_name')
            ->get(['student_id', 'first_name', 'middle_name', 'last_name', 'admission_no']);

        $existing = [];
        $maxMarks = null;

        if (!empty($data['exam_id']) && !empty($data['subject_id'])) {
            $existing = ExamResult::where('exam_id', $data['exam_id'])
                ->where('class_section_id', $data['class_section_id'])
                ->where('subject_id', $data['subject_id'])
                ->pluck('marks_obtained', 'student_id')
                ->toArray();

            $classSection = ClassSection::find($data['class_section_id']);
            $maxMarks = \App\Models\ExamSchedule::where('exam_id', $data['exam_id'])
                ->when($classSection, fn ($q) => $q->where('class_id', $classSection->class_id))
                ->where('subject_id', $data['subject_id'])
                ->max('max_marks');
            $maxMarks = $maxMarks ? (int) $maxMarks : null;
        }

        return response()->json([
            'ok' => true,
            'students' => $students->map(function ($s) {
                return [
                    'id' => $s->student_id,
                    'name' => trim(implode(' ', array_filter([$s->first_name, $s->middle_name, $s->last_name]))),
                    'admission_no' => $s->admission_no,
                ];
            }),
            'existing' => $existing,
            'max_marks' => $maxMarks,
        ]);
    }

    public function store(CreateExamResultRequest $request)
    {
        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!empty($request->class_section_id) && !$allowedIds->contains((int) $request->class_section_id)) {
                Flash::error('You are not authorized to add results for this class.');
                return redirect()->back();
            }
            if (!empty($request->subject_id) && !$allowedSubjectIds->contains((int) $request->subject_id)) {
                Flash::error('You are not authorized to add results for this subject.');
                return redirect()->back();
            }
        }

        $input = $request->all();
        $input['created_by'] = Auth::id();

        $examResult = $this->examResultRepository->create($input);

        AuditTrail::log('Exam Result', 'CREATE', $examResult->result_id, null, $examResult->toArray());

        // AJAX (single-entry flow): let the form continue with the next student
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'result_id' => $examResult->result_id]);
        }

        Flash::success('Exam Result saved successfully.');

        // Keep the picked context so a teacher can enter the next one quickly
        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            return redirect(route('exam-results.create', $request->only(['exam_id', 'class_section_id', 'subject_id'])));
        }

        return redirect(route('exam-results.index'));
    }

    public function show($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            if (!$allowedIds->contains((int) $examResult->class_section_id)) {
                Flash::error('You are not authorized to view this result.');
                return redirect(route('exam-results.index'));
            }
        }

        return view('exam_results.show')->with('examResult', $examResult);
    }

    public function edit($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $examResult->class_section_id)) {
                Flash::error('You are not authorized to edit this result.');
                return redirect(route('exam-results.index'));
            }
            if (!$allowedSubjectIds->contains((int) $examResult->subject_id)) {
                Flash::error('You are not authorized to edit results for this subject.');
                return redirect(route('exam-results.index'));
            }
        }

        $dropdownData = $this->getDropdownData();

        return view('exam_results.edit', array_merge(['examResult' => $examResult], $dropdownData));
    }

    public function update($id, UpdateExamResultRequest $request)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $examResult->class_section_id)) {
                Flash::error('You are not authorized to update this result.');
                return redirect(route('exam-results.index'));
            }
            if (!$allowedSubjectIds->contains((int) $examResult->subject_id)) {
                Flash::error('You are not authorized to update results for this subject.');
                return redirect(route('exam-results.index'));
            }
        }

        $oldData = $examResult->toArray();
        $examResult = $this->examResultRepository->update($request->all(), $id);

        AuditTrail::log('Exam Result', 'UPDATE', $examResult->result_id, $oldData, $examResult->toArray());

        Flash::success('Exam Result updated successfully.');

        return redirect(route('exam-results.index'));
    }

    public function destroy($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $user = auth()->user();
        $viewAll = $user->hasPermission('exams.results.view-all');
        $hasSettings = $user->hasPermission('academics.settings.manage');

        if (!$viewAll && !$hasSettings) {
            $allowedIds = $this->teacherScope->getClassSectionIds($user);
            $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
            if (!$allowedIds->contains((int) $examResult->class_section_id)) {
                Flash::error('You are not authorized to delete this result.');
                return redirect(route('exam-results.index'));
            }
            if (!$allowedSubjectIds->contains((int) $examResult->subject_id)) {
                Flash::error('You are not authorized to delete results for this subject.');
                return redirect(route('exam-results.index'));
            }
        }

        $oldData = $examResult->toArray();
        $this->examResultRepository->delete($id);

        AuditTrail::log('Exam Result', 'DELETE', $id, $oldData, null);

        Flash::success('Exam Result deleted successfully.');

        return redirect(route('exam-results.index'));
    }
}