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
        $year = AcademicYear::where('is_current', true)->first() ?? AcademicYear::first();
        
        if (!$year) {
            $this->command->warn('No academic years found. Skipping TeacherSubject seeder.');
            return;
        }
        
        $subjects = Subject::all();
        $teachers = Staff::where('staff_type', 'teaching')->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No teaching staff found. Skipping TeacherSubject seeder.');
            return;
        }

        if ($subjects->isEmpty()) {
            $this->command->warn('No subjects found. Skipping TeacherSubject seeder.');
            return;
        }

        $classSections = ClassSection::all();
        if ($classSections->isEmpty()) {
            $this->command->warn('No class sections found. Skipping TeacherSubject seeder.');
            return;
        }

        foreach ($classSections as $classSection) {
            foreach ($subjects->random(min(2, $subjects->count())) as $subject) {
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
        
        $this->command->info('Teacher subjects seeded successfully.');
    }
}