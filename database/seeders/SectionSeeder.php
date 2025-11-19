<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SchoolClass;
use App\Models\Section;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = ['A', 'B'];
        $classes = SchoolClass::all();

        foreach ($classes as $class) {
            foreach ($sections as $name) {
                Section::firstOrCreate(
                    ['class_id' => $class->class_id, 'name' => $name],
                    ['capacity' => 40]
                );
            }
        }
    }
}