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
        if (!$year) return;

        $periods = Period::orderBy('start_time')->get();
        if ($periods->isEmpty()) return;

        $teachers = Staff::where('staff_type', 'teaching')->get();
        if ($teachers->isEmpty()) return;

        $classrooms = Classroom::all();
        if ($classrooms->isEmpty()) return;

        $subjects = Subject::all();
        if ($subjects->isEmpty()) return;

        $days = ['monday', 'wednesday'];
        $classSections = ClassSection::all();
        $periodCount = min(2, $periods->count());
        $yearId = $year->academic_year_id;

        // Seed the slot guards from the rows already stored for this year.
        // Starting empty made a reseed re-insert slots that already existed and
        // violated one of the three composite uniques on this table
        // (uniq_teacher_slot, uniq_classroom_slot, uniq_class_section_slot), so
        // `php artisan db:seed` failed the second time it was run even though
        // DatabaseSeeder documents that a reseed is always safe.
        $usedTeacherSlots = [];
        $usedClassroomSlots = [];
        $usedClassSectionSlots = [];

        foreach (Timetable::where('academic_year_id', $yearId)->get() as $existing) {
            $usedTeacherSlots[$existing->teacher_id . '-' . $existing->day_of_week . '-' . $existing->period_id . '-' . $yearId] = true;
            $usedClassroomSlots[$existing->classroom_id . '-' . $existing->day_of_week . '-' . $existing->period_id . '-' . $yearId] = true;
            $usedClassSectionSlots[$existing->class_section_id . '-' . $existing->day_of_week . '-' . $existing->period_id . '-' . $yearId] = true;
        }

        foreach ($classSections as $classSection) {
            foreach ($days as $day) {
                for ($p = 0; $p < $periodCount; $p++) {
                    $period = $periods[$p];

                    $classSectionKey = $classSection->class_section_id . '-' . $day . '-' . $period->period_id . '-' . $yearId;

                    if (isset($usedClassSectionSlots[$classSectionKey])) {
                        continue;
                    }

                    $assigned = false;
                    foreach ($teachers as $teacher) {
                        foreach ($classrooms as $classroom) {
                            $teacherKey = $teacher->staff_id . '-' . $day . '-' . $period->period_id . '-' . $yearId;
                            $classroomKey = $classroom->classroom_id . '-' . $day . '-' . $period->period_id . '-' . $yearId;

                            if (isset($usedTeacherSlots[$teacherKey]) || isset($usedClassroomSlots[$classroomKey])) {
                                continue;
                            }

                            $subject = $subjects->random();

                            Timetable::create([
                                'class_section_id' => $classSection->class_section_id,
                                'day_of_week' => $day,
                                'period_id' => $period->period_id,
                                'subject_id' => $subject->subject_id,
                                'teacher_id' => $teacher->staff_id,
                                'classroom_id' => $classroom->classroom_id,
                                'academic_year_id' => $yearId,
                            ]);

                            $usedTeacherSlots[$teacherKey] = true;
                            $usedClassroomSlots[$classroomKey] = true;
                            $usedClassSectionSlots[$classSectionKey] = true;
                            $assigned = true;
                            break 2;
                        }
                    }

                    if (!$assigned) {
                        // Skip this slot if no teacher/classroom combo is available
                    }
                }
            }
        }
    }
}
