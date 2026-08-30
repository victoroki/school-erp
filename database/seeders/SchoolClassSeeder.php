<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            // Pre-Primary
            ['name' => 'PP1', 'numeric_value' => 1, 'description' => 'Pre-Primary 1 (CBEC pre-primary, ages 4-5)'],
            ['name' => 'PP2', 'numeric_value' => 2, 'description' => 'Pre-Primary 2 (CBEC pre-primary, ages 5-6)'],
            // Lower Primary
            ['name' => 'Grade 1', 'numeric_value' => 3, 'description' => 'Lower Primary Grade 1 (CBC)'],
            ['name' => 'Grade 2', 'numeric_value' => 4, 'description' => 'Lower Primary Grade 2 (CBC)'],
            ['name' => 'Grade 3', 'numeric_value' => 5, 'description' => 'Lower Primary Grade 3 (CBC)'],
            // Upper Primary
            ['name' => 'Grade 4', 'numeric_value' => 6, 'description' => 'Upper Primary Grade 4 (CBC)'],
            ['name' => 'Grade 5', 'numeric_value' => 7, 'description' => 'Upper Primary Grade 5 (CBC)'],
            ['name' => 'Grade 6', 'numeric_value' => 8, 'description' => 'Upper Primary Grade 6 (CBC) — KPSEA assessment year'],
            // Junior School
            ['name' => 'Grade 7', 'numeric_value' => 9, 'description' => 'Junior School Grade 7 (CBC)'],
            ['name' => 'Grade 8', 'numeric_value' => 10, 'description' => 'Junior School Grade 8 (CBC)'],
            ['name' => 'Grade 9', 'numeric_value' => 11, 'description' => 'Junior School Grade 9 (CBC) — JSS assessment year'],
            // Senior School
            ['name' => 'Grade 10', 'numeric_value' => 12, 'description' => 'Senior School Grade 10 (CBC)'],
            ['name' => 'Grade 11', 'numeric_value' => 13, 'description' => 'Senior School Grade 11 (CBC)'],
            ['name' => 'Grade 12', 'numeric_value' => 14, 'description' => 'Senior School Grade 12 (CBC) — KCSE assessment year'],
        ];

        foreach ($classes as $data) {
            SchoolClass::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
