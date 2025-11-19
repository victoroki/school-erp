<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;

class SchoolClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['name' => 'Grade 1', 'numeric_value' => 1, 'description' => ''],
            ['name' => 'Grade 2', 'numeric_value' => 2, 'description' => ''],
            ['name' => 'Grade 3', 'numeric_value' => 3, 'description' => ''],
        ];

        foreach ($classes as $data) {
            SchoolClass::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}