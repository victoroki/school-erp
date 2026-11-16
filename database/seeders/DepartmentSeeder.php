<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Mathematics Department', 'description' => 'Mathematics, numeracy and statistics in line with the CBC curriculum'],
            ['name' => 'Languages Department', 'description' => 'English, Kiswahili, Kenya Sign Language and foreign languages'],
            ['name' => 'Science Department', 'description' => 'Integrated Science, Biology, Chemistry, Physics and Health Education'],
            ['name' => 'Humanities Department', 'description' => 'Social Studies, Geography, History and Government, Religious Education'],
            ['name' => 'Business, ICT & Pre-Technical', 'description' => 'Business Studies, Computer Studies and Pre-Technical Studies'],
            ['name' => 'Creative Arts & Sports', 'description' => 'Art & Design, Music, Drama and Physical Education & Sports'],
            ['name' => 'Agriculture & Technical', 'description' => 'Agriculture, Home Science, Nutrition and Technical subjects'],
            ['name' => 'Administration', 'description' => 'School administration, bursar and support staff'],
        ];

        foreach ($departments as $data) {
            Department::firstOrCreate(['name' => $data['name']], $data);
        }
    }
}
