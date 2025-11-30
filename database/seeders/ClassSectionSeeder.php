<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Classroom;
use App\Models\Staff;
use App\Models\ClassSection;

class ClassSectionSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_current', true)->first();
        $classrooms = Classroom::all();
        $teachers = Staff::where('staff_type', 'teaching')->get();

        foreach (SchoolClass::all() as $class) {
            $sections = Section::where('class_id', $class->class_id)->get();
            foreach ($sections as $section) {
                $classroom = $classrooms->random();
                $teacher = $teachers->random();
                ClassSection::firstOrCreate(
                    [
                        'academic_year_id' => $year->academic_year_id,
                        'class_id' => $class->class_id,
                        'section_id' => $section->section_id,
                    ],
                    [
                        'classroom_id' => $classroom->classroom_id,
                        'class_teacher_id' => $teacher->staff_id,
                    ]
                );
            }
        }
    }
}