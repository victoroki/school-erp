<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\AcademicYear;
use App\Models\Term;

class ExamsSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_current', true)->first();
        if (!$year) {
            return;
        }

        $type = function (string $name) {
            return ExamType::where('name', $name)->value('exam_type_id');
        };

        $exams = [
            // Term 1 (2026)
            ['name' => 'Term 1 Opener Examination 2026', 'type' => 'Opener Examination', 'publish_result' => true, 'start' => '2026-01-12', 'end' => '2026-01-16'],
            ['name' => 'Term 1 Mid-Term Assessment 2026', 'type' => 'Mid-Term Assessment', 'publish_result' => true, 'start' => '2026-02-23', 'end' => '2026-02-27'],
            ['name' => 'Term 1 End of Term Examination 2026', 'type' => 'End of Term Examination', 'publish_result' => true, 'start' => '2026-03-23', 'end' => '2026-04-02'],

            // Term 2 (2026) — currently active (make results publishable)
            ['name' => 'Term 2 Opener Examination 2026', 'type' => 'Opener Examination', 'publish_result' => true, 'start' => '2026-05-11', 'end' => '2026-05-15'],
            ['name' => 'Term 2 Mid-Term Assessment 2026', 'type' => 'Mid-Term Assessment', 'publish_result' => true, 'start' => '2026-06-22', 'end' => '2026-06-26'],
            ['name' => 'Term 2 End of Term Examination 2026', 'type' => 'End of Term Examination', 'publish_result' => true, 'start' => '2026-07-27', 'end' => '2026-08-06'],

            // National & candidate assessments
            ['name' => 'KPSEA Trial Assessment 2026', 'type' => 'KPSEA', 'publish_result' => true, 'start' => '2026-07-13', 'end' => '2026-07-17'],
            ['name' => 'KCSE Mock Examination 2026', 'type' => 'KCSE Mock Examination', 'publish_result' => true, 'start' => '2026-06-08', 'end' => '2026-06-19'],
            ['name' => 'Grade 9 Junior School Assessment 2026', 'type' => 'Junior School Assessment', 'publish_result' => true, 'start' => '2026-07-20', 'end' => '2026-07-24'],

            // Continuous & practical assessments
            ['name' => 'CAT 2 Continuous Assessment 2026', 'type' => 'Continuous Assessment Test', 'publish_result' => true, 'start' => '2026-06-01', 'end' => '2026-06-05'],
            ['name' => 'CBC Project & Practical Assessment 2026', 'type' => 'CBC Project & Practical Assessment', 'publish_result' => true, 'start' => '2026-07-06', 'end' => '2026-07-10'],
            ['name' => 'Oral & Communication Assessment 2026', 'type' => 'Oral & Communication Assessment', 'publish_result' => true, 'start' => '2026-05-25', 'end' => '2026-05-29'],
        ];

        foreach ($exams as $data) {
            Exam::firstOrCreate(
                ['name' => $data['name'], 'academic_year_id' => $year->academic_year_id],
                [
                    'exam_type_id' => $type($data['type']),
                    'description' => $data['name'] . ' — ' . $year->name . ' academic year',
                    'academic_year_id' => $year->academic_year_id,
                    'start_date' => $data['start'],
                    'end_date' => $data['end'],
                    'publish_result' => $data['publish_result'],
                ]
            );
        }
    }
}
