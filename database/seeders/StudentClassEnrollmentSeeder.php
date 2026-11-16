<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\ClassSection;
use App\Models\AcademicYear;
use App\Models\StudentClassEnrollment;

class StudentClassEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        $lastYear = AcademicYear::where('is_current', false)->orderByDesc('academic_year_id')->first();

        if (!$currentYear) {
            return;
        }

        $students = Student::all();
        if ($students->count() === 0) {
            return;
        }

        // admission_no => ['class' => CBC class name, 'section' => A|B, 'status', 'year']
        $assignments = [
            // PP1
            'ADM2026/001' => ['PP1', 'A', 'active'], 'ADM2026/002' => ['PP1', 'A', 'active'], 'ADM2026/003' => ['PP1', 'B', 'active'],
            // PP2
            'ADM2026/004' => ['PP2', 'A', 'active'], 'ADM2026/005' => ['PP2', 'A', 'active'], 'ADM2026/006' => ['PP2', 'B', 'active'],
            // Grade 1
            'ADM2026/007' => ['Grade 1', 'A', 'active'], 'ADM2026/008' => ['Grade 1', 'A', 'active'], 'ADM2026/009' => ['Grade 1', 'B', 'active'],
            // Grade 2
            'ADM2026/010' => ['Grade 2', 'A', 'active'], 'ADM2026/011' => ['Grade 2', 'A', 'active'], 'ADM2026/012' => ['Grade 2', 'B', 'active'],
            // Grade 3
            'ADM2026/013' => ['Grade 3', 'A', 'active'], 'ADM2026/014' => ['Grade 3', 'A', 'active'], 'ADM2026/015' => ['Grade 3', 'B', 'active'],
            // Grade 4
            'ADM2026/016' => ['Grade 4', 'A', 'active'], 'ADM2026/017' => ['Grade 4', 'A', 'active'], 'ADM2026/018' => ['Grade 4', 'B', 'active'],
            // Grade 5
            'ADM2026/019' => ['Grade 5', 'A', 'active'], 'ADM2026/020' => ['Grade 5', 'A', 'active'], 'ADM2026/021' => ['Grade 5', 'B', 'active'],
            // Grade 6
            'ADM2026/022' => ['Grade 6', 'A', 'active'], 'ADM2026/023' => ['Grade 6', 'B', 'active'],
            // Grade 7
            'ADM2026/024' => ['Grade 7', 'A', 'active'], 'ADM2026/025' => ['Grade 7', 'B', 'active'],
            // Grade 8
            'ADM2026/026' => ['Grade 8', 'A', 'active'], 'ADM2026/027' => ['Grade 8', 'B', 'active'],
            // Grade 9
            'ADM2026/028' => ['Grade 9', 'A', 'active'], 'ADM2026/029' => ['Grade 9', 'B', 'active'],
            // Grade 10
            'ADM2026/030' => ['Grade 10', 'A', 'active'], 'ADM2026/031' => ['Grade 10', 'B', 'active'],
            // Grade 11
            'ADM2026/032' => ['Grade 11', 'A', 'active'], 'ADM2026/033' => ['Grade 11', 'B', 'active'],
            // Grade 12
            'ADM2026/034' => ['Grade 12', 'A', 'active'], 'ADM2026/035' => ['Grade 12', 'A', 'active'], 'ADM2026/036' => ['Grade 12', 'B', 'active'],
            // Historical records (placed in the completed 2025 academic year)
            'ADM2025/101' => ['Grade 12', 'A', 'completed'], 'ADM2025/102' => ['Grade 12', 'B', 'completed'],
            'ADM2025/103' => ['Grade 7', 'B', 'transferred'],
            'ADM2024/201' => ['Grade 4', 'A', 'dropped'],
        ];

        $rollCounters = [];

        foreach ($students as $student) {
            $key = $student->admission_no;

            if (!isset($assignments[$key])) {
                continue;
            }

            [$className, $sectionName, $status] = $assignments[$key];

            $year = $status === 'active' ? $currentYear : $lastYear;
            if (!$year) {
                continue;
            }

            $classSection = ClassSection::where('academic_year_id', $year->academic_year_id)
                ->whereHas('schoolClass', function ($q) use ($className) {
                    $q->where('name', $className);
                })
                ->whereHas('section', function ($q) use ($sectionName) {
                    $q->where('name', $sectionName);
                })
                ->first();

            if (!$classSection) {
                continue;
            }

            $rollKey = $className . '_' . $sectionName;
            if (!isset($rollCounters[$rollKey])) {
                $rollCounters[$rollKey] = 1;
            }
            $rollState = $rollCounters[$rollKey];
            $rollCounters[$rollKey]++;

            $rollPrefix = str_replace(' ', '', $className) . $sectionName;

            StudentClassEnrollment::firstOrCreate(
                [
                    'student_id' => $student->student_id,
                    'class_section_id' => $classSection->class_section_id,
                    'academic_year_id' => $year->academic_year_id,
                ],
                [
                    'roll_number' => $rollPrefix . '-' . str_pad((string)$rollState, 3, '0', STR_PAD_LEFT),
                    'enrollment_date' => $status === 'active' ? $student->admission_date : '2025-01-06',
                    'status' => $status,
                    'is_current' => $status === 'active',
                ]
            );
        }
    }
}
