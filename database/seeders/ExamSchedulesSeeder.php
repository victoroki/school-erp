<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Classroom;
use App\Models\ExamSchedule;

class ExamSchedulesSeeder extends Seeder
{
    public function run(): void
    {
        $exams = Exam::all();
        $classes = SchoolClass::all();
        $subjects = Subject::all();
        $classrooms = Classroom::all();
        
        // First Term Examination Schedule
        $firstTermExam = $exams->where('name', 'First Term Examination 2024')->first();
        if ($firstTermExam) {
            $this->createTermExamSchedule($firstTermExam, $classes, $subjects, $classrooms, '2024-10-15', '2024-10-25');
        }
        
        // Second Term Examination Schedule
        $secondTermExam = $exams->where('name', 'Second Term Examination 2025')->first();
        if ($secondTermExam) {
            $this->createTermExamSchedule($secondTermExam, $classes, $subjects, $classrooms, '2025-03-15', '2025-03-25');
        }
        
        // Unit Test Schedules
        $this->createUnitTestSchedules($exams, $classes, $subjects, $classrooms);
        
        // Monthly Test Schedules
        $this->createMonthlyTestSchedules($exams, $classes, $subjects, $classrooms);
        
        // Practical Examination Schedules
        $this->createPracticalExamSchedules($exams, $classes, $subjects, $classrooms);
        
        // Oral Examination Schedules
        $this->createOralExamSchedules($exams, $classes, $subjects, $classrooms);
        
        // Olympiad Schedules
        $this->createOlympiadSchedules($exams, $classes, $subjects, $classrooms);
        
        // Weekly Test Schedules
        $this->createWeeklyTestSchedules($exams, $classes, $subjects, $classrooms);
    }
    
    private function createTermExamSchedule($exam, $classes, $subjects, $classrooms, $startDate, $endDate)
    {
        $examDate = $startDate;
        $timeSlots = [
            ['start' => '09:00:00', 'end' => '11:00:00'],
            ['start' => '11:30:00', 'end' => '13:30:00'],
            ['start' => '14:00:00', 'end' => '16:00:00'],
        ];
        
        $coreSubjects = $subjects->whereIn('subject_code', ['MATH', 'ENG', 'SCI', 'SOC', 'COMP']);
        
        foreach ($classes as $class) {
            $timeSlotIndex = 0;
            foreach ($coreSubjects as $subject) {
                if (strtotime($examDate) > strtotime($endDate)) {
                    $examDate = $startDate;
                }
                
                $timeSlot = $timeSlots[$timeSlotIndex % count($timeSlots)];
                
                ExamSchedule::firstOrCreate(
                    [
                        'exam_id' => $exam->exam_id,
                        'class_id' => $class->class_id,
                        'subject_id' => $subject->subject_id,
                        'exam_date' => $examDate,
                    ],
                    [
                        'start_time' => $timeSlot['start'],
                        'end_time' => $timeSlot['end'],
                        'room_id' => $classrooms->random()->room_id,
                        'max_marks' => $this->getMaxMarksForClass($class->class_name),
                        'passing_marks' => $this->getPassingMarksForClass($class->class_name),
                    ]
                );
                
                $timeSlotIndex++;
                if ($timeSlotIndex >= count($timeSlots)) {
                    $examDate = date('Y-m-d', strtotime($examDate . ' +1 day'));
                    $timeSlotIndex = 0;
                }
            }
        }
    }
    
    private function createUnitTestSchedules($exams, $classes, $subjects, $classrooms)
    {
        $unitTests = $exams->filter(function($exam) {
            return strpos($exam->name, 'Unit Test') !== false;
        });
        
        foreach ($unitTests as $unitTest) {
            foreach ($classes->random(min(3, $classes->count())) as $class) {
                $subject = $subjects->random();
                
                ExamSchedule::firstOrCreate(
                    [
                        'exam_id' => $unitTest->exam_id,
                        'class_id' => $class->class_id,
                        'subject_id' => $subject->subject_id,
                        'exam_date' => $unitTest->start_date,
                    ],
                    [
                        'start_time' => '10:00:00',
                        'end_time' => '11:00:00',
                        'room_id' => $classrooms->random()->room_id,
                        'max_marks' => 25,
                        'passing_marks' => 10,
                    ]
                );
            }
        }
    }
    
    private function createMonthlyTestSchedules($exams, $classes, $subjects, $classrooms)
    {
        $monthlyTests = $exams->filter(function($exam) {
            return strpos($exam->name, 'Monthly Test') !== false;
        });
        
        foreach ($monthlyTests as $monthlyTest) {
            $coreSubjects = $subjects->whereIn('subject_code', ['MATH', 'ENG', 'SCI']);
            
            foreach ($classes as $class) {
                foreach ($coreSubjects as $subject) {
                    ExamSchedule::firstOrCreate(
                        [
                            'exam_id' => $monthlyTest->exam_id,
                            'class_id' => $class->class_id,
                            'subject_id' => $subject->subject_id,
                            'exam_date' => $monthlyTest->start_date,
                        ],
                        [
                            'start_time' => '09:30:00',
                            'end_time' => '11:30:00',
                            'room_id' => $classrooms->random()->room_id,
                            'max_marks' => 50,
                            'passing_marks' => 20,
                        ]
                    );
                }
            }
        }
    }
    
    private function createPracticalExamSchedules($exams, $classes, $subjects, $classrooms)
    {
        $practicalExams = $exams->filter(function($exam) {
            return strpos($exam->name, 'Practical') !== false;
        });
        
        foreach ($practicalExams as $practicalExam) {
            $practicalSubjects = $subjects->whereIn('subject_code', ['SCI', 'COMP', 'PHY', 'CHEM', 'BIO']);
            
            foreach ($classes->random(min(2, $classes->count())) as $class) {
                foreach ($practicalSubjects->random(min(2, $practicalSubjects->count())) as $subject) {
                    ExamSchedule::firstOrCreate(
                        [
                            'exam_id' => $practicalExam->exam_id,
                            'class_id' => $class->class_id,
                            'subject_id' => $subject->subject_id,
                            'exam_date' => $practicalExam->start_date,
                        ],
                        [
                            'start_time' => '14:00:00',
                            'end_time' => '16:00:00',
                            'room_id' => $classrooms->random()->room_id,
                            'max_marks' => 30,
                            'passing_marks' => 12,
                        ]
                    );
                }
            }
        }
    }
    
    private function createOralExamSchedules($exams, $classes, $subjects, $classrooms)
    {
        $oralExams = $exams->filter(function($exam) {
            return strpos($exam->name, 'Oral') !== false;
        });
        
        foreach ($oralExams as $oralExam) {
            $languageSubjects = $subjects->whereIn('subject_code', ['ENG', 'HINDI', 'FRENCH', 'SPAN']);
            
            foreach ($classes->random(min(2, $classes->count())) as $class) {
                foreach ($languageSubjects->random(min(1, $languageSubjects->count())) as $subject) {
                    ExamSchedule::firstOrCreate(
                        [
                            'exam_id' => $oralExam->exam_id,
                            'class_id' => $class->class_id,
                            'subject_id' => $subject->subject_id,
                            'exam_date' => $oralExam->start_date,
                        ],
                        [
                            'start_time' => '11:00:00',
                            'end_time' => '12:00:00',
                            'room_id' => $classrooms->random()->room_id,
                            'max_marks' => 20,
                            'passing_marks' => 8,
                        ]
                    );
                }
            }
        }
    }
    
    private function createOlympiadSchedules($exams, $classes, $subjects, $classrooms)
    {
        $olympiads = $exams->filter(function($exam) {
            return strpos($exam->name, 'Olympiad') !== false;
        });
        
        foreach ($olympiads as $olympiad) {
            $olympiadSubjects = $subjects->whereIn('subject_code', ['MATH', 'SCI', 'COMP']);
            
            foreach ($classes->random(min(2, $classes->count())) as $class) {
                foreach ($olympiadSubjects->random(min(1, $olympiadSubjects->count())) as $subject) {
                    ExamSchedule::firstOrCreate(
                        [
                            'exam_id' => $olympiad->exam_id,
                            'class_id' => $class->class_id,
                            'subject_id' => $subject->subject_id,
                            'exam_date' => $olympiad->start_date,
                        ],
                        [
                            'start_time' => '10:00:00',
                            'end_time' => '12:00:00',
                            'room_id' => $classrooms->random()->room_id,
                            'max_marks' => 60,
                            'passing_marks' => 24,
                        ]
                    );
                }
            }
        }
    }
    
    private function createWeeklyTestSchedules($exams, $classes, $subjects, $classrooms)
    {
        $weeklyTests = $exams->filter(function($exam) {
            return strpos($exam->name, 'Weekly Test') !== false;
        });
        
        foreach ($weeklyTests as $weeklyTest) {
            foreach ($classes->random(min(2, $classes->count())) as $class) {
                $subject = $subjects->random();
                
                ExamSchedule::firstOrCreate(
                    [
                        'exam_id' => $weeklyTest->exam_id,
                        'class_id' => $class->class_id,
                        'subject_id' => $subject->subject_id,
                        'exam_date' => $weeklyTest->start_date,
                    ],
                    [
                        'start_time' => '09:00:00',
                        'end_time' => '09:45:00',
                        'room_id' => $classrooms->random()->room_id,
                        'max_marks' => 15,
                        'passing_marks' => 6,
                    ]
                );
            }
        }
    }
    
    private function getMaxMarksForClass($className)
    {
        $classNumber = (int) filter_var($className, FILTER_SANITIZE_NUMBER_INT);
        
        if ($classNumber <= 2) {
            return 50;
        } elseif ($classNumber <= 5) {
            return 80;
        } else {
            return 100;
        }
    }
    
    private function getPassingMarksForClass($className)
    {
        $maxMarks = $this->getMaxMarksForClass($className);
        return round($maxMarks * 0.33); // 33% passing criteria
    }
}