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
use Illuminate\Http\Request;
use Flash;
use Auth;
use DB;
use App\Models\ExamResult;

class ExamResultController extends AppBaseController
{
    /** @var ExamResultRepository */
    private $examResultRepository;

    public function __construct(ExamResultRepository $examResultRepo)
    {
        $this->examResultRepository = $examResultRepo;
        $this->middleware('can:exams.view')->only(['index', 'show']);
        $this->middleware('can:exams.manage')->only(['bulk', 'store', 'update', 'destroy']);
    }

    public function bulk(Request $request)
    {
        $exams = Exam::pluck('name', 'exam_id');
        $classSections = ClassSection::with(['schoolClass', 'section'])->get()->mapWithKeys(function ($cs) {
            return [$cs->class_section_id => ($cs->schoolClass->name ?? '') . ' - ' . ($cs->section->name ?? '')];
        });
        $subjects = Subject::pluck('name', 'subject_id');

        $students = [];
        $existingResults = [];

        if ($request->filled(['exam_id', 'class_section_id', 'subject_id'])) {
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

        $exam_id = $request->exam_id;
        $class_section_id = $request->class_section_id;
        $subject_id = $request->subject_id;
        $marks = $request->marks; // array student_id => marks
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

    /**
     * Generate and download a CSV template for bulk import.
     */
    public function importTemplate(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'subject_id' => 'required',
        ]);

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
            // CSV Headers
            fputcsv($file, ['STUDENT_ID', 'ADMISSION_NO', 'STUDENT_NAME', 'MARKS_OBTAINED', 'REMARKS']);

            foreach ($students as $student) {
                fputcsv($file, [
                    $student->student_id,
                    $student->admission_no,
                    $student->full_name,
                    '', // Empty marks for user to fill
                    ''  // Empty remarks
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Handle bulk import from uploaded CSV.
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'exam_id' => 'required',
            'class_section_id' => 'required',
            'subject_id' => 'required',
            'excel_file' => 'required|file|mimes:csv,txt'
        ]);

        $exam_id = $request->exam_id;
        $class_section_id = $request->class_section_id;
        $subject_id = $request->subject_id;
        $file = $request->file('excel_file');

        $importCount = 0;
        $errorCount = 0;

        if (($handle = fopen($file->getRealPath(), "r")) !== FALSE) {
            fgetcsv($handle); // Skip header row
            
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
        // Get students with full name and ID
        $students = Student::all()->mapWithKeys(function ($student) {
            return [$student->student_id => $student->first_name . ' ' . $student->last_name . ' (' . $student->admission_no . ')'];
        });

        // Get class sections with class name and section name
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

    /**
     * Display a listing of the ExamResult.
     */
    public function index(Request $request)
    {
        $examResults = $this->examResultRepository->paginate(10);

        return view('exam_results.index')
            ->with('examResults', $examResults);
    }

    /**
     * Show the form for creating a new ExamResult.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('exam_results.create', $dropdownData);
    }

    /**
     * Store a newly created ExamResult in storage.
     */
    public function store(CreateExamResultRequest $request)
    {
        $input = $request->all();
        $input['created_by'] = Auth::id();

        $this->examResultRepository->create($input);

        Flash::success('Exam Result saved successfully.');

        return redirect(route('exam-results.index'));
    }

    /**
     * Display the specified ExamResult.
     */
    public function show($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        return view('exam_results.show')->with('examResult', $examResult);
    }

    /**
     * Show the form for editing the specified ExamResult.
     */
    public function edit($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $dropdownData = $this->getDropdownData();

        return view('exam_results.edit', array_merge(['examResult' => $examResult], $dropdownData));
    }

    /**
     * Update the specified ExamResult in storage.
     */
    public function update($id, UpdateExamResultRequest $request)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $this->examResultRepository->update($request->all(), $id);

        Flash::success('Exam Result updated successfully.');

        return redirect(route('exam-results.index'));
    }

    /**
     * Remove the specified ExamResult from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $examResult = $this->examResultRepository->find($id);

        if (empty($examResult)) {
            Flash::error('Exam Result not found');
            return redirect(route('exam-results.index'));
        }

        $this->examResultRepository->delete($id);

        Flash::success('Exam Result deleted successfully.');

        return redirect(route('exam-results.index'));
    }
}