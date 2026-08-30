<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;

class ExamTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            // Kenyan CBC / KCSE assessment types
            ['name' => 'Opener Examination', 'short_name' => 'OPENER', 'description' => 'Opener assessment sat at the start of each term covering the previous term\'s work'],
            ['name' => 'Mid-Term Assessment', 'short_name' => 'MIDTERM', 'description' => 'Continuous assessment held mid-way through a term (CBC school-based)'],
            ['name' => 'End of Term Examination', 'short_name' => 'EOT', 'description' => 'Comprehensive end-of-term examination covering the full term\'s syllabus'],
            ['name' => 'Continuous Assessment Test', 'short_name' => 'CAT', 'description' => 'Short continuous assessment tests used to track CBC competency progress'],
            ['name' => 'KPSEA', 'short_name' => 'KPSEA', 'description' => 'Kenya Primary School Education Assessment — national assessment for Grade 6'],
            ['name' => 'Junior School Assessment', 'short_name' => 'JSS', 'description' => 'Kenya Junior School Education Assessment for Grade 9 learners'],
            ['name' => 'KCSE Mock Examination', 'short_name' => 'MOCK', 'description' => 'School mock examination preparing Grade 12 candidates for the national KCSE'],
            ['name' => 'KCSE', 'short_name' => 'KCSE', 'description' => 'Kenya Certificate of Secondary Education national examination (Grade 12)'],
            ['name' => 'CBC Project & Practical Assessment', 'short_name' => 'PROJ', 'description' => 'Competency-based assessment of projects, practicals and psychomotor skills'],
            ['name' => 'Oral & Communication Assessment', 'short_name' => 'ORAL', 'description' => 'Assessment of speaking, listening and communication skills'],
            ['name' => 'Placement & Remedial Test', 'short_name' => 'REMED', 'description' => 'Diagnostic or remedial test to identify and close learning gaps'],
        ];

        foreach ($types as $data) {
            ExamType::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
