<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['subject_code' => 'MATH', 'name' => 'Mathematics', 'description' => ''],
            ['subject_code' => 'ENG', 'name' => 'English', 'description' => ''],
            ['subject_code' => 'SCI', 'name' => 'Science', 'description' => ''],
        ];

        foreach ($subjects as $data) {
            Subject::firstOrCreate(
                ['subject_code' => $data['subject_code']],
                $data
            );
        }
    }
}