<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamSchedule;
use App\Models\Student;
use App\Models\ExamResult;
use App\Models\Staff;

class SimpleExamResultsSeeder extends Seeder
{
    public function run(): void
    {
        $examSchedules = ExamSchedule::limit(10)->get();
        $students = Student::where('status', 'active')->limit(10)->get();
        $staff = Staff::where('staff_type', 'teaching')->first();
        
        if ($examSchedules->isEmpty() || $students->isEmpty()) {
            $this->command->info('No exam schedules or students found. Skipping exam results.');
            return;
        }
        
        $resultsCreated = 0;
        
        foreach ($examSchedules as $schedule) {
            foreach ($students->random(min(5, $students->count())) as $student) {
                try {
                    $maxMarks = $schedule->max_marks;
                    $passingMarks = $schedule->passing_marks;
                    
                    // Generate realistic marks distribution
                    $random = rand(1, 100);
                    if ($random <= 5) {
                        $marks = rand($passingMarks - 10, $passingMarks - 1); // Fail
                    } elseif ($random <= 20) {
                        $marks = rand($passingMarks, $maxMarks * 0.6); // Just pass
                    } elseif ($random <= 50) {
                        $marks = rand($maxMarks * 0.6, $maxMarks * 0.75); // Average
                    } elseif ($random <= 80) {
                        $marks = rand($maxMarks * 0.75, $maxMarks * 0.9); // Good
                    } else {
                        $marks = rand($maxMarks * 0.9, $maxMarks); // Excellent
                    }
                    
                    $percentage = ($marks / $maxMarks) * 100;
                    $grade = $this->calculateGrade($percentage);
                    $remarks = $this->generateRemarks($percentage, $passingMarks);
                    
                    ExamResult::create([
                        'exam_id' => $schedule->exam_id,
                        'student_id' => $student->student_id,
                        'class_section_id' => $schedule->class_id,
                        'subject_id' => $schedule->subject_id,
                        'marks_obtained' => $marks,
                        'grade_id' => $grade,
                        'remarks' => $remarks,
                        'created_by' => $staff->staff_id,
                    ]);
                    
                    $resultsCreated++;
                    
                } catch (\Exception $e) {
                    $this->command->warn("Failed to create result for student {$student->student_id} in exam {$schedule->exam_id}: " . $e->getMessage());
                    continue;
                }
            }
        }
        
        $this->command->info("Created {$resultsCreated} exam results successfully.");
    }
    
    private function calculateGrade($percentage)
    {
        if ($percentage >= 90) return 1; // A+
        elseif ($percentage >= 80) return 2; // A
        elseif ($percentage >= 70) return 3; // B+
        elseif ($percentage >= 60) return 4; // B
        elseif ($percentage >= 50) return 5; // C+
        elseif ($percentage >= 40) return 6; // C
        elseif ($percentage >= 33) return 7; // D
        else return 8; // F
    }
    
    private function generateRemarks($percentage, $passingMarks)
    {
        if ($percentage >= 90) {
            $remarks = ['Outstanding performance!', 'Exceptional results!', 'Brilliant work!', 'Excellent grasp of concepts!'];
        } elseif ($percentage >= 70) {
            $remarks = ['Very good performance!', 'Good understanding!', 'Satisfactory performance!', 'Well done!'];
        } elseif ($percentage >= $passingMarks) {
            $remarks = ['Average performance!', 'Needs more practice!', 'Fair performance!', 'Can improve with effort!'];
        } else {
            $remarks = ['Needs improvement!', 'Requires extra coaching!', 'Below average!', 'Must work harder!'];
        }
        
        return $remarks[array_rand($remarks)];
    }
}