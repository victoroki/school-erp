<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Student;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\ExamResult;
use App\Models\Staff;

class ExamResultsSeeder extends Seeder
{
    public function run(): void
    {
        $examSchedules = ExamSchedule::all();
        $students = Student::where('status', 'active')->get();
        $classSections = ClassSection::all();
        $staff = Staff::where('staff_type', 'teaching')->get();
        
        foreach ($examSchedules as $schedule) {
            // Get students for this class
            $classStudents = $students->filter(function($student) use ($schedule) {
                // Check if student has enrollments and filter by class_id
                if ($student->classEnrollments && $student->classEnrollments->count() > 0) {
                    $enrollment = $student->classEnrollments->where('class_id', $schedule->class_id)->first();
                    return $enrollment ? true : false;
                }
                return false;
            });
            
            // Only process if we have students for this class
            if ($classStudents->count() > 0) {
                $randomStudents = $classStudents->random(min(15, $classStudents->count()));
                foreach ($randomStudents as $student) {
                    $this->createExamResult($schedule, $student, $classSections, $staff);
                }
            }
        }
        
        // Create some special cases (failures, top performers, etc.)
        $this->createSpecialExamResults();
    }
    
    private function createExamResult($schedule, $student, $classSections, $staff)
    {
        $maxMarks = $schedule->max_marks;
        $passingMarks = $schedule->passing_marks;
        
        // Generate varied performance based on student characteristics
        $obtainedMarks = $this->generateVariedMarks($maxMarks, $passingMarks, $student);
        $grade = $this->calculateGrade($obtainedMarks, $maxMarks);
        $remarks = $this->generateRemarks($obtainedMarks, $maxMarks, $passingMarks);
        
        $classSection = $classSections->where('class_id', $schedule->class_id)->first();
        
        ExamResult::firstOrCreate(
            [
                'exam_id' => $schedule->exam_id,
                'student_id' => $student->student_id,
                'class_section_id' => $classSection ? $classSection->class_section_id : null,
                'subject_id' => $schedule->subject_id,
            ],
            [
                'marks_obtained' => $obtainedMarks,
                'grade_id' => $grade,
                'remarks' => $remarks,
                'created_by' => $staff->random()->staff_id,
            ]
        );
    }
    
    private function generateVariedMarks($maxMarks, $passingMarks, $student)
    {
        // Create realistic distribution of marks
        $random = rand(1, 100);
        
        if ($random <= 5) {
            // 5% students fail (below passing)
            return rand($passingMarks - 10, $passingMarks - 1);
        } elseif ($random <= 15) {
            // 10% students just pass (passing to 50%)
            return rand($passingMarks, $maxMarks * 0.5);
        } elseif ($random <= 40) {
            // 25% students average (50-70%)
            return rand($maxMarks * 0.5, $maxMarks * 0.7);
        } elseif ($random <= 70) {
            // 30% students good (70-85%)
            return rand($maxMarks * 0.7, $maxMarks * 0.85);
        } elseif ($random <= 90) {
            // 20% students very good (85-95%)
            return rand($maxMarks * 0.85, $maxMarks * 0.95);
        } else {
            // 10% students excellent (95-100%)
            return rand($maxMarks * 0.95, $maxMarks);
        }
    }
    
    private function calculateGrade($obtainedMarks, $maxMarks)
    {
        $percentage = ($obtainedMarks / $maxMarks) * 100;
        
        if ($percentage >= 90) return 1; // A+
        elseif ($percentage >= 80) return 2; // A
        elseif ($percentage >= 70) return 3; // B+
        elseif ($percentage >= 60) return 4; // B
        elseif ($percentage >= 50) return 5; // C+
        elseif ($percentage >= 40) return 6; // C
        else return 7; // D/Fail
    }
    
    private function generateRemarks($obtainedMarks, $maxMarks, $passingMarks)
    {
        $percentage = ($obtainedMarks / $maxMarks) * 100;
        
        $excellentRemarks = [
            'Outstanding performance! Keep up the excellent work.',
            'Exceptional results! Your hard work has paid off.',
            'Brilliant performance! Continue to excel.',
            'Excellent grasp of concepts! Well done.',
            'Superb performance! Very proud of your achievement.'
        ];
        
        $goodRemarks = [
            'Very good performance! Keep working hard.',
            'Good understanding of subject matter.',
            'Satisfactory performance with room for improvement.',
            'Fair performance, can do better with more effort.',
            'Decent results, focus on weaker areas.'
        ];
        
        $averageRemarks = [
            'Average performance, needs more practice.',
            'Satisfactory but requires consistent effort.',
            'Fair performance, work on fundamentals.',
            'Needs improvement in understanding concepts.',
            'Can perform better with regular study habits.'
        ];
        
        $poorRemarks = [
            'Below average performance, needs special attention.',
            'Requires significant improvement and extra coaching.',
            'Poor performance, needs to focus on basics.',
            'Very disappointing results, immediate action required.',
            'Fails to meet minimum requirements, needs remedial classes.'
        ];
        
        if ($percentage >= 90) {
            return $excellentRemarks[array_rand($excellentRemarks)];
        } elseif ($percentage >= 60) {
            return $goodRemarks[array_rand($goodRemarks)];
        } elseif ($percentage >= $passingMarks) {
            return $averageRemarks[array_rand($averageRemarks)];
        } else {
            return $poorRemarks[array_rand($poorRemarks)];
        }
    }
    
    private function createSpecialExamResults()
    {
        // Create some top performers
        $topStudents = Student::where('status', 'active')->limit(5)->get();
        $firstTermExam = Exam::where('name', 'First Term Examination 2024')->first();
        $mathSubject = Subject::where('subject_code', 'MATH')->first();
        $staff = Staff::where('staff_type', 'teaching')->get();
        
        if ($firstTermExam && $mathSubject) {
            foreach ($topStudents as $student) {
                $schedule = ExamSchedule::where('exam_id', $firstTermExam->exam_id)
                    ->where('subject_id', $mathSubject->subject_id)
                    ->first();
                    
                if ($schedule) {
                    ExamResult::firstOrCreate(
                        [
                            'exam_id' => $firstTermExam->exam_id,
                            'student_id' => $student->student_id,
                            'class_section_id' => $schedule->class_id,
                            'subject_id' => $mathSubject->subject_id,
                        ],
                        [
                            'marks_obtained' => $schedule->max_marks,
                            'grade_id' => 1, // A+
                            'remarks' => 'Perfect score! Outstanding mathematical ability.',
                            'created_by' => $staff->random()->staff_id,
                        ]
                    );
                }
            }
        }
        
        // Create some students who need improvement
        $weakStudents = Student::where('status', 'active')->skip(10)->limit(3)->get();
        $weeklyTest = Exam::where('name', 'Weekly Test - Week 1')->first();
        $englishSubject = Subject::where('subject_code', 'ENG')->first();
        
        if ($weeklyTest && $englishSubject) {
            foreach ($weakStudents as $student) {
                $schedule = ExamSchedule::where('exam_id', $weeklyTest->exam_id)
                    ->where('subject_id', $englishSubject->subject_id)
                    ->first();
                    
                if ($schedule) {
                    ExamResult::firstOrCreate(
                        [
                            'exam_id' => $weeklyTest->exam_id,
                            'student_id' => $student->student_id,
                            'class_section_id' => $schedule->class_id,
                            'subject_id' => $englishSubject->subject_id,
                        ],
                        [
                            'marks_obtained' => $schedule->passing_marks - 2,
                            'grade_id' => 7, // D/Fail
                            'remarks' => 'Needs significant improvement in English grammar and vocabulary.',
                            'created_by' => $staff->random()->staff_id,
                        ]
                    );
                }
            }
        }
        
        // Create some average performers with mixed results
        $averageStudents = Student::where('status', 'active')->skip(5)->limit(7)->get();
        $scienceSubject = Subject::where('subject_code', 'SCI')->first();
        
        if ($scienceSubject) {
            foreach ($averageStudents as $student) {
                $schedules = ExamSchedule::where('subject_id', $scienceSubject->subject_id)->limit(2)->get();
                
                foreach ($schedules as $schedule) {
                    $marks = rand($schedule->max_marks * 0.6, $schedule->max_marks * 0.75);
                    
                    ExamResult::firstOrCreate(
                        [
                            'exam_id' => $schedule->exam_id,
                            'student_id' => $student->student_id,
                            'class_section_id' => $schedule->class_id,
                            'subject_id' => $scienceSubject->subject_id,
                        ],
                        [
                            'marks_obtained' => $marks,
                            'grade_id' => $this->calculateGrade($marks, $schedule->max_marks),
                            'remarks' => 'Good understanding of basic concepts, needs more practice in advanced topics.',
                            'created_by' => $staff->random()->staff_id,
                        ]
                    );
                }
            }
        }
    }
}