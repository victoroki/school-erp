<?php

namespace Tests\Unit;

use App\Services\TimetableGeneratorService;
use App\Services\TimetableConflictService;
use Tests\TestCase;

class TimetableGeneratorServiceTest extends TestCase
{
    private function baseInput(): array
    {
        return [
            'academic_year_id' => 1,
            'days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'],
            'periods' => [
                ['id' => 1, 'start_time' => '08:00:00', 'name' => 'Period 1'],
                ['id' => 2, 'start_time' => '09:00:00', 'name' => 'Period 2'],
                ['id' => 3, 'start_time' => '10:00:00', 'name' => 'Period 3'],
                ['id' => 4, 'start_time' => '11:00:00', 'name' => 'Period 4'],
            ],
            'class_sections' => [
                ['id' => 1, 'class_id' => 1, 'classroom_id' => 10, 'label' => 'Grade 1 - A'],
                ['id' => 2, 'class_id' => 1, 'classroom_id' => 11, 'label' => 'Grade 1 - B'],
            ],
            'class_subjects' => [
                ['class_id' => 1, 'subject_id' => 100, 'academic_year_id' => 1],
                ['class_id' => 1, 'subject_id' => 101, 'academic_year_id' => 1],
            ],
            'teacher_subjects' => [
                ['staff_id' => 5, 'subject_id' => 100, 'class_section_id' => 1, 'academic_year_id' => 1],
                ['staff_id' => 5, 'subject_id' => 100, 'class_section_id' => 2, 'academic_year_id' => 1],
                ['staff_id' => 6, 'subject_id' => 101, 'class_section_id' => 1, 'academic_year_id' => 1],
                ['staff_id' => 6, 'subject_id' => 101, 'class_section_id' => 2, 'academic_year_id' => 1],
            ],
            'subjects' => [100 => 'Mathematics', 101 => 'English'],
            'teachers' => [5 => 'Alice Mwangi', 6 => 'Brian Otieno'],
            'classrooms' => [10 => 'Room A', 11 => 'Room B'],
        ];
    }

    public function test_generates_complete_collision_free_timetable()
    {
        $result = (new TimetableGeneratorService())->generate($this->baseInput());

        $this->assertTrue($result->isComplete());
        $this->assertSame(4, $result->placedCount());

        // Every placed row must satisfy every conflict rule (no double booking).
        $placed = $result->placements;
        foreach ($placed as $i => $row) {
            $others = $placed;
            unset($others[$i]);
            $others = array_values($others);
            $this->assertFalse(
                TimetableConflictService::classSlotTaken($others, $row['class_section_id'], $row['academic_year_id'], $row['day_of_week'], $row['period_id']),
                'Class slot double-booked'
            );
            $this->assertFalse(
                TimetableConflictService::teacherConflict($others, $row['teacher_id'], $row['academic_year_id'], $row['day_of_week'], $row['period_id']),
                'Teacher double-booked'
            );
            $this->assertFalse(
                TimetableConflictService::classroomConflict($others, $row['classroom_id'], $row['academic_year_id'], $row['day_of_week'], $row['period_id']),
                'Classroom double-booked'
            );
        }

        // Each class section has exactly one Mathematics and one English lesson.
        foreach ([1, 2] as $csId) {
            $subjects = collect($placed)
                ->where('class_section_id', $csId)
                ->pluck('subject_id')
                ->sort()
                ->values()
                ->toArray();
            $this->assertSame([100, 101], $subjects);
        }
    }

    public function test_reports_missing_teacher_assignment()
    {
        $input = $this->baseInput();
        // Remove English teacher for class section 2.
        $input['teacher_subjects'] = array_values(array_filter(
            $input['teacher_subjects'],
            fn($ts) => !($ts['subject_id'] === 101 && $ts['class_section_id'] === 2)
        ));

        $result = (new TimetableGeneratorService())->generate($input);

        $this->assertFalse($result->isComplete());
        $this->assertSame(1, $result->unplacedCount());
        $this->assertSame('Grade 1 - B', $result->unplaced[0]['class_section']);
        $this->assertSame('English', $result->unplaced[0]['subject']);
        $this->assertStringContainsString('No teacher is assigned', $result->unplaced[0]['reason']);
    }

    public function test_reports_when_fully_booked()
    {
        $input = $this->baseInput();
        // Only ONE teaching period and one day, but 4 requirements and only 2 teachers.
        $input['days'] = ['monday'];
        $input['periods'] = [['id' => 1, 'start_time' => '08:00:00', 'name' => 'Period 1']];

        $result = (new TimetableGeneratorService())->generate($input);

        $this->assertFalse($result->isComplete());
        // Monday has only one slot per teacher; with 2 teachers only 2 lessons fit.
        $this->assertSame(2, $result->placedCount());
        $this->assertSame(2, $result->unplacedCount());
        $this->assertStringContainsString('No free slot', $result->unplaced[0]['reason']);
    }

    public function test_respects_existing_lessons()
    {
        $input = $this->baseInput();
        $input['existing'] = [
            [
                'class_section_id' => 1,
                'day_of_week' => 'monday',
                'period_id' => 1,
                'teacher_id' => 5,
                'classroom_id' => 10,
                'academic_year_id' => 1,
            ],
        ];

        $result = (new TimetableGeneratorService())->generate($input);

        $this->assertTrue($result->isComplete());
        foreach ($result->placements as $row) {
            $this->assertFalse(
                $row['day_of_week'] === 'monday'
                && (int) $row['period_id'] === 1
                && (int) $row['class_section_id'] === 1,
                'New lesson collides with existing lesson'
            );
        }
    }

    public function test_places_periods_per_week_per_subject_spread_across_days()
    {
        $input = $this->baseInput();
        // Mathematics gets 4 periods/week for class 1; English stays at 1.
        $input['class_subjects'] = [
            ['class_id' => 1, 'subject_id' => 100, 'academic_year_id' => 1, 'periods_per_week' => 4],
            ['class_id' => 1, 'subject_id' => 101, 'academic_year_id' => 1, 'periods_per_week' => 1],
        ];

        $result = (new TimetableGeneratorService())->generate($input);

        $this->assertTrue($result->isComplete());
        // 4 Math + 1 English per class section, two sections = 10 lessons.
        $this->assertSame(10, $result->placedCount());

        $placed = $result->placements;

        // Each class section must have exactly 4 Mathematics and 1 English lesson.
        foreach ([1, 2] as $csId) {
            $subjects = collect($placed)
                ->where('class_section_id', $csId)
                ->pluck('subject_id')
                ->sort()
                ->values()
                ->toArray();
            $this->assertSame([100, 100, 100, 100, 101], $subjects);
        }

        // Math must be spread across days: max 1 lesson per day per class (5 days / 4 periods).
        foreach ([1, 2] as $csId) {
            $mathDays = collect($placed)
                ->where('class_section_id', $csId)
                ->where('subject_id', 100)
                ->pluck('day_of_week')
                ->unique()
                ->count();
            $this->assertSame(4, $mathDays, 'Math lessons should be spread across 4 different days');
        }

        // Still collision free.
        foreach ($placed as $i => $row) {
            $others = $placed;
            unset($others[$i]);
            $others = array_values($others);
            $this->assertFalse(TimetableConflictService::classSlotTaken($others, $row['class_section_id'], $row['academic_year_id'], $row['day_of_week'], $row['period_id']));
            $this->assertFalse(TimetableConflictService::teacherConflict($others, $row['teacher_id'], $row['academic_year_id'], $row['day_of_week'], $row['period_id']));
            $this->assertFalse(TimetableConflictService::classroomConflict($others, $row['classroom_id'], $row['academic_year_id'], $row['day_of_week'], $row['period_id']));
        }
    }

    public function test_reports_partial_placement_when_periods_exceed_capacity()
    {
        $input = $this->baseInput();
        // Ask for 40 Math periods/week but only 5 days x 4 periods = 20 slots exist.
        $input['class_subjects'] = [
            ['class_id' => 1, 'subject_id' => 100, 'academic_year_id' => 1, 'periods_per_week' => 40],
            ['class_id' => 1, 'subject_id' => 101, 'academic_year_id' => 1, 'periods_per_week' => 1],
        ];

        $result = (new TimetableGeneratorService())->generate($input);

        $this->assertFalse($result->isComplete());
        $this->assertGreaterThan(0, $result->unplacedCount());
        $this->assertStringContainsString('Placed', $result->unplaced[0]['reason']);
    }

    public function test_avoids_teacher_overload_when_possible()
    {
        $input = $this->baseInput();
        // Assign BOTH subjects of class section 1 to the same teacher, but give
        // teacher 6 the second subject for class section 2. Teacher 5 has 2 lessons
        // (100 for cs1 + cs2), teacher 6 has 2 lessons (101 for cs1 + cs2). Loads balance.
        $input['teacher_subjects'] = [
            ['staff_id' => 5, 'subject_id' => 100, 'class_section_id' => 1, 'academic_year_id' => 1],
            ['staff_id' => 5, 'subject_id' => 100, 'class_section_id' => 2, 'academic_year_id' => 1],
            ['staff_id' => 6, 'subject_id' => 101, 'class_section_id' => 1, 'academic_year_id' => 1],
            ['staff_id' => 6, 'subject_id' => 101, 'class_section_id' => 2, 'academic_year_id' => 1],
        ];

        $result = (new TimetableGeneratorService())->generate($input);

        $this->assertTrue($result->isComplete());
        $loads = collect($result->placements)->countBy('teacher_id');
        // With 4 lessons and 2 teachers, an even 2/2 split is achievable.
        $this->assertSame(2, $loads->get(5));
        $this->assertSame(2, $loads->get(6));
    }
}
