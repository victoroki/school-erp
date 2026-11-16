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
    private const TIME_SLOTS = [
        ['start' => '09:00:00', 'end' => '11:00:00'],
        ['start' => '11:30:00', 'end' => '13:00:00'],
        ['start' => '14:00:00', 'end' => '16:00:00'],
    ];

    public function run(): void
    {
        $classrooms = Classroom::all();
        if ($classrooms->isEmpty()) {
            return;
        }

        foreach (SchoolClass::all() as $class) {
            $subjects = $this->subjectsFor($class);
            if ($subjects->isEmpty()) {
                continue;
            }

            [$max, $passing] = $this->marksFor($class->numeric_value);
            $slot = 0;

            $eot = $this->exam('Term 1 End of Term Examination 2026') ?: $this->exam('Term 2 End of Term Examination 2026');
            if ($eot) {
                $this->schedule($eot, $class, $subjects, $classrooms, $max, $passing, $slot);
            }
        }

        $this->scheduleNationalExams($classrooms);
        $this->scheduleContinuousAssessments($classrooms);
    }

    private function scheduleNationalExams($classrooms)
    {
        $defs = [
            ['exam' => 'KPSEA Trial Assessment 2026', 'class' => 'Grade 6', 'max' => 60],
            ['exam' => 'Grade 9 Junior School Assessment 2026', 'class' => 'Grade 9', 'max' => 100],
            ['exam' => 'KCSE Mock Examination 2026', 'class' => 'Grade 12', 'max' => 100],
        ];

        foreach ($defs as $def) {
            $exam = $this->exam($def['exam']);
            $class = SchoolClass::where('name', $def['class'])->first();
            if (!$exam || !$class) {
                continue;
            }
            $subjects = $this->subjectsFor($class);
            if ($subjects->isEmpty()) {
                continue;
            }
            $slot = 0;
            $this->schedule($exam, $class, $subjects, $classrooms, $def['max'], (int) round($def['max'] * 0.33), $slot);
        }
    }

    private function scheduleContinuousAssessments($classrooms)
    {
        $cats = [
            ['name' => 'Term 2 Mid-Term Assessment 2026', 'max' => 50],
            ['name' => 'CAT 2 Continuous Assessment 2026', 'max' => 30],
            ['name' => 'CBC Project & Practical Assessment 2026', 'max' => 40],
            ['name' => 'Oral & Communication Assessment 2026', 'max' => 20],
        ];

        foreach (SchoolClass::all() as $class) {
            if (str_starts_with($class->name, 'PP')) {
                continue;
            }
            $subjects = $this->subjectsFor($class);
            if ($subjects->isEmpty()) {
                continue;
            }

            foreach ($cats as $cat) {
                $exam = $this->exam($cat['name']);
                if (!$exam) {
                    continue;
                }
                $slot = 0;
                $this->schedule($exam, $class, $subjects, $classrooms, $cat['max'], (int) round($cat['max'] * 0.33), $slot);
            }
        }
    }

    private function schedule($exam, $class, $subjects, $classrooms, $maxMarks, $passing, &$slot)
    {
        $examDate = $exam->start_date ? $exam->start_date->format('Y-m-d') : now()->format('Y-m-d');
        $roomPool = $classrooms->pluck('classroom_id')->all();
        $roomCount = count($roomPool) ?: 1;

        foreach ($subjects as $subject) {
            $time = self::TIME_SLOTS[$slot % count(self::TIME_SLOTS)];

            ExamSchedule::firstOrCreate(
                [
                    'exam_id' => $exam->exam_id,
                    'class_id' => $class->class_id,
                    'subject_id' => $subject->subject_id,
                    'exam_date' => $examDate,
                ],
                [
                    'start_time' => $time['start'],
                    'end_time' => $time['end'],
                    'room_id' => $roomPool[$slot % $roomCount],
                    'max_marks' => $maxMarks,
                    'passing_marks' => $passing,
                ]
            );

            $slot++;
        }
    }

    private function subjectsFor(?SchoolClass $class)
    {
        if (!$class) {
            return collect();
        }

        $nv = (int) $class->numeric_value;

        if ($nv <= 2) {
            return Subject::whereIn('subject_code', ['ENG', 'KIS', 'MAT'])->get();
        }
        if ($nv <= 8) {
            return Subject::whereIn('subject_code', ['ENG', 'KIS', 'MAT', 'SCI', 'SST', 'AGR', 'PES'])->get();
        }
        if ($nv <= 11) {
            return Subject::whereIn('subject_code', ['ENG', 'KIS', 'MAT', 'ISC', 'SST', 'PTS', 'AGR'])->get();
        }

        return Subject::whereIn('subject_code', ['ENG', 'KIS', 'MAT', 'BIO', 'CHE', 'PHY', 'GEO', 'HIS', 'BUS', 'COM'])->get();
    }

    private function marksFor(?int $numericValue): array
    {
        $nv = (int) $numericValue;
        if ($nv <= 2) {
            return [30, 10];
        }
        if ($nv <= 8) {
            return [60, 20];
        }
        return [100, 33];
    }

    private function exam(string $name): ?Exam
    {
        return Exam::where('name', $name)->first();
    }
}
