<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\Period;
use App\Models\Subject;
use App\Models\Staff;
use App\Models\Classroom;
use App\Models\Timetable;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $year = AcademicYear::where('is_current', true)->first();
        $periods = Period::orderBy('start_time')->get();
        $subjects = Subject::all();
        $teachers = Staff::where('staff_type', 'teaching')->get();
        $classrooms = Classroom::all();

        foreach (ClassSection::all() as $classSection) {
            $days = ['monday', 'wednesday'];
            foreach ($days as $day) {
                foreach ($periods->take(2) as $period) {
                    $subject = $subjects->random();
                    $teacher = $teachers->random();
                    $classroom = $classrooms->random();

                    Timetable::firstOrCreate(
                        [
                            'class_section_id' => $classSection->class_section_id,
                            'day_of_week' => $day,
                            'period_id' => $period->period_id,
                        ],
                        [
                            'subject_id' => $subject->subject_id,
                            'teacher_id' => $teacher->staff_id,
                            'classroom_id' => $classroom->classroom_id,
                            'academic_year_id' => $year->academic_year_id,
                        ]
                    );
                }
            }
        }
    }
}