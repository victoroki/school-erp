<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\ClassSubject;

class ClassSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_current', true)->first();
        $subjects = Subject::all();

        foreach (SchoolClass::all() as $class) {
            foreach ($subjects as $subject) {
                ClassSubject::firstOrCreate(
                    [
                        'class_id' => $class->class_id,
                        'subject_id' => $subject->subject_id,
                        'academic_year_id' => $year->academic_year_id,
                    ]
                );
            }
        }
    }
}