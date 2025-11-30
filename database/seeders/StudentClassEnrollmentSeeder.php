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
        $year = AcademicYear::where('is_current', true)->first();
        $classSections = ClassSection::all();
        $students = Student::all();

        if (!$year || $classSections->count() === 0 || $students->count() === 0) {
            return;
        }

        // Assign students to classes based on their age/birth year
        $classAssignments = [
            // Grade 1 students (Age 6-7, born 2016-2017)
            'ADM2024001' => ['class' => 1, 'section' => 'A'], // Emma Johnson
            'ADM2024002' => ['class' => 1, 'section' => 'A'], // Liam Martinez
            'ADM2024003' => ['class' => 1, 'section' => 'B'], // Olivia Brown
            
            // Grade 2 students (Age 7-8, born 2015-2016)
            'ADM2024004' => ['class' => 2, 'section' => 'A'], // Noah Davis
            'ADM2024005' => ['class' => 2, 'section' => 'A'], // Ava Wilson
            'ADM2024006' => ['class' => 2, 'section' => 'B'], // Ethan Garcia
            
            // Grade 3 students (Age 8-9, born 2014-2015)
            'ADM2024007' => ['class' => 3, 'section' => 'A'], // Sophia Rodriguez
            'ADM2024008' => ['class' => 3, 'section' => 'A'], // Mason Lewis
            'ADM2024009' => ['class' => 3, 'section' => 'B'], // Isabella Lee
            
            // Grade 4 students (Age 9-10, born 2013-2014)
            'ADM2024010' => ['class' => 4, 'section' => 'A'], // William Walker
            'ADM2024011' => ['class' => 4, 'section' => 'A'], // Mia Hall
            'ADM2024012' => ['class' => 4, 'section' => 'B'], // James Allen
            
            // Grade 5 students (Age 10-11, born 2012-2013)
            'ADM2024013' => ['class' => 5, 'section' => 'A'], // Charlotte Young
            'ADM2024014' => ['class' => 5, 'section' => 'A'], // Benjamin Hernandez
            'ADM2024015' => ['class' => 5, 'section' => 'B'], // Amelia King
            
            // Alumni students (would have been in higher grades)
            'ADM2023001' => ['class' => 6, 'section' => 'A'], // Alexander Wright
            'ADM2023002' => ['class' => 6, 'section' => 'A'], // Elizabeth Lopez
            
            // Transferred/Inactive students (assign to appropriate grade)
            'ADM2024004' => ['class' => 2, 'section' => 'B'], // Michael Hill
            'ADM2024005' => ['class' => 4, 'section' => 'B'], // Sofia Green
        ];

        $rollCounters = [];
        
        foreach ($students as $student) {
            $admissionNo = $student->admission_no;
            
            if (!isset($classAssignments[$admissionNo])) {
                // Default assignment if not in mapping
                $classSection = $classSections->random();
            } else {
                $assignment = $classAssignments[$admissionNo];
                $classSection = $classSections->where('class_id', $assignment['class'])
                    ->where('section_id', function($query) use ($assignment) {
                        $query->select('section_id')
                            ->from('sections')
                            ->where('section_name', $assignment['section']);
                    })->first();
                    
                if (!$classSection) {
                    $classSection = $classSections->random();
                }
            }
            
            // Generate roll number based on class and section
            $classKey = $classSection->class_id . '_' . $classSection->section_id;
            if (!isset($rollCounters[$classKey])) {
                $rollCounters[$classKey] = 1;
            }
            
            $rollNumber = 'R' . $classSection->class_id . $classSection->section->section_name . 
                         str_pad((string)$rollCounters[$classKey], 2, '0', STR_PAD_LEFT);
            $rollCounters[$classKey]++;
            
            // Determine status based on student status
            $enrollmentStatus = 'active';
            if ($student->status === 'transferred' || $student->status === 'inactive') {
                $enrollmentStatus = $student->status;
            }
            
            StudentClassEnrollment::firstOrCreate(
                [
                    'student_id' => $student->student_id,
                    'class_section_id' => $classSection->class_section_id,
                    'academic_year_id' => $year->academic_year_id,
                ],
                [
                    'roll_number' => $rollNumber,
                    'enrollment_date' => $student->admission_date,
                    'status' => $enrollmentStatus,
                ]
            );
        }
    }
}