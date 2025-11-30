<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\TeacherSubject;

class TeacherSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_current', true)->first();
        $subjects = Subject::all();
        $teachers = Staff::where('staff_type', 'teaching')->get();

        foreach (ClassSection::all() as $classSection) {
            foreach ($subjects->random(2) as $subject) {
                $teacher = $teachers->random();
                TeacherSubject::firstOrCreate(
                    [
                        'staff_id' => $teacher->staff_id,
                        'subject_id' => $subject->subject_id,
                        'class_section_id' => $classSection->class_section_id,
                        'academic_year_id' => $year->academic_year_id,
                    ]
                );
            }
        }
    }
}