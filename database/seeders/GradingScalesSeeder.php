<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradingScale;

class GradingScalesSeeder extends Seeder
{
    public function run(): void
    {
        $gradingScales = [
            ['name' => 'A+', 'min_percentage' => 90, 'max_percentage' => 100, 'grade_point' => 4.0, 'description' => 'Outstanding Performance'],
            ['name' => 'A', 'min_percentage' => 80, 'max_percentage' => 89, 'grade_point' => 3.7, 'description' => 'Excellent Performance'],
            ['name' => 'B+', 'min_percentage' => 70, 'max_percentage' => 79, 'grade_point' => 3.3, 'description' => 'Very Good Performance'],
            ['name' => 'B', 'min_percentage' => 60, 'max_percentage' => 69, 'grade_point' => 3.0, 'description' => 'Good Performance'],
            ['name' => 'C+', 'min_percentage' => 50, 'max_percentage' => 59, 'grade_point' => 2.3, 'description' => 'Satisfactory Performance'],
            ['name' => 'C', 'min_percentage' => 40, 'max_percentage' => 49, 'grade_point' => 2.0, 'description' => 'Average Performance'],
            ['name' => 'D', 'min_percentage' => 33, 'max_percentage' => 39, 'grade_point' => 1.0, 'description' => 'Below Average Performance'],
            ['name' => 'F', 'min_percentage' => 0, 'max_percentage' => 32, 'grade_point' => 0.0, 'description' => 'Fail'],
        ];

        foreach ($gradingScales as $scale) {
            GradingScale::firstOrCreate(
                ['name' => $scale['name']],
                $scale
            );
        }
    }
}