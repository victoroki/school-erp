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

class ExamResultController extends AppBaseController
{
    /** @var ExamResultRepository */
    private $examResultRepository;

    private TeacherScopeService $teacherScope;

    public function __construct(ExamResultRepository $examResultRepo, TeacherScopeService $teacherScope)
    {
        $this->examResultRepository = $examResultRepo;
        $this->teacherScope = $teacherScope;
        $this->middleware('can:exams.results.view-own')->only(['index', 'show']);
        $this->middleware('can:exams.marks.enter-own')->only(['bulk', 'postBulk']);
        $this->middleware('can:exams.import')->only(['importTemplate', 'importStore']);
        $this->middleware('can:exams.marks.enter-own')->only(['store', 'update', 'destroy', 'create', 'edit']);
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

            $exams = Exam::whereHas('examSchedules', function ($q) use ($classSectionIds) {
                $q->whereIn('class_section_id', $classSectionIds);
            })->orWhereDoesntHave('examSchedules')->pluck('name', 'exam_id');

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

        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
            if (!$viewAll && !$hasSettings) {
                $allowedIds = $this->teacherScope->getClassSectionIds($user);
                $allowedSubjectIds = $this->teacherScope->getSubjectIds($user);
                if (!$allowedIds->contains((int) $request->class_section_id) || !$allowedSubjectIds->contains((int) $request->subject_id)) {
                    Flash::error('You are not authorized to enter marks for this class or subject.');
                    return redirect()->route('exam-results.bulk');
                }
            }

            $students = Student::whereHas('studentClassEnrollments', function ($q) use ($request) {
                $q->where('class_section_id', $request->class_section_id)
                  ->where('status', 'active');
            })->orderBy('last_name')->get();

            $existingResults = ExamResult::where('exam_id', $request->exam_id)
                ->where('class_section_id', $request->class_section_id)
                ->where('subject_id', $request->subject_id)
                ->pluck('marks_obtained', 'student_id')
                ->toArray();
        }

        return view('exam_results.bulk', compact('exams', 'classSections', 'subjects', 'students', 'existingResults'));
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
            'exams' => Exam::whereHas('examSchedules', function ($q) use ($classSectionIds) {
                $q->whereIn('class_section_id', $classSectionIds);
            })->orWhereDoesntHave('examSchedules')->pluck('name', 'exam_id'),
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

        if ($viewAll || $hasSettings) {
            $examResults = $this->examResultRepository->paginate(10);
        } else {
            $classSectionIds = $this->teacherScope->getClassSectionIds($user);
            $examResults = ExamResult::whereIn('class_section_id', $classSectionIds)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('exam_results.index')
            ->with('examResults', $examResults);
    }

    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('exam_results.create', $dropdownData);
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

        $this->examResultRepository->create($input);

        Flash::success('Exam Result saved successfully.');

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

        $this->examResultRepository->update($request->all(), $id);

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

        $this->examResultRepository->delete($id);

        Flash::success('Exam Result deleted successfully.');

        return redirect(route('exam-results.index'));
    }
}