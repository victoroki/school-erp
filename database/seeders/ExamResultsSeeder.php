<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSchedule;
use App\Models\ExamResult;
use App\Models\Student;
use App\Models\Staff;

class ExamResultsSeeder extends Seeder
{
    public function run(): void
    {
        $teaching = Staff::where('staff_type', 'teaching')->get();
        if ($teaching->isEmpty()) {
            return;
        }

        $students = Student::where('status', 'active')->with('studentClassEnrollments.classSection')->get();
        if ($students->isEmpty()) {
            return;
        }

        foreach (ExamSchedule::all() as $schedule) {
            $marks = $this->marksFor($schedule->max_marks, $schedule->passing_marks);

            // Students in this schedule's class for the current academic year
            $classStudents = $students->filter(function ($student) use ($schedule) {
                return $student->studentClassEnrollments
                    ->where('status', 'active')
                    ->contains(function ($enrollment) use ($schedule) {
                        return $enrollment->classSection
                            && $enrollment->classSection->class_id == $schedule->class_id;
                    });
            });

            if ($classStudents->isEmpty()) {
                continue;
            }

            foreach ($classStudents as $student) {
                $enrollment = $student->studentClassEnrollments
                    ->where('status', 'active')
                    ->first();

                $marksObtained = $marks[array_rand($marks)];
                $marksObtained = max(0, min((float) $schedule->max_marks, $marksObtained));

                ExamResult::firstOrCreate(
                    [
                        'exam_id' => $schedule->exam_id,
                        'student_id' => $student->student_id,
                        'class_section_id' => $enrollment ? $enrollment->class_section_id : null,
                        'subject_id' => $schedule->subject_id,
                    ],
                    [
                        'marks_obtained' => $marksObtained,
                        'remarks' => $this->remarksFor($marksObtained, $schedule->max_marks, $schedule->passing_marks),
                        'created_by' => $teaching->random()->staff_id,
                    ]
                );
            }
        }
    }

    private function marksFor(float $maxMarks, float $passingMarks): array
    {
        $max = $maxMarks ?: 100;
        unset($passingMarks);

        // Derive marks from percentages that sit *inside* a defined grade
        // band. Avoids raw marks that land on a scale boundary/gap (e.g. 32.5%
        // between D-min 33 and F-max 32) which would leave grade_id null.
        $percentages = [25, 38, 45, 52, 62, 72, 80, 88, 96];

        return array_map(function ($pct) use ($max) {
            return round(($pct / 100) * $max, 2);
        }, $percentages);
    }

    private function remarksFor(float $obtained, float $maxMarks, float $passingMarks): string
    {
        $max = $maxMarks ?: 100;
        $pass = $passingMarks ?: (int) round($max * 0.33);
        $percentage = $max > 0 ? ($obtained / $max) * 100 : 0;

        if ($percentage < $pass) {
            return 'Below the pass mark. Requires remedial support.';
        }
        if ($percentage >= 85) {
            return 'Outstanding performance — keep it up.';
        }
        if ($percentage >= 70) {
            return 'Very good performance.';
        }
        if ($percentage >= 50) {
            return 'Satisfactory performance; keep working hard.';
        }
        return 'A fair pass; more revision needed.';
    }
}
