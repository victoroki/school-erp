<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exam;
use App\Models\ExamType;
use App\Models\AcademicYear;

class ExamsSeeder extends Seeder
{
    public function run(): void
    {
        $academicYear = AcademicYear::where('is_current', true)->first();
        $examTypes = ExamType::all();
        
        // Ensure required exam types exist
        $requiredTypes = [
            'First Term Examination',
            'Unit Test',
            'Monthly Test',
            'Practical Examination',
            'Oral Examination',
            'Olympiad',
            'Science Olympiad',
            'Diagnostic Test',
            'Second Term Examination',
            'Weekly Test'
        ];
        
        foreach ($requiredTypes as $typeName) {
            if (!$examTypes->where('name', $typeName)->first()) {
                // Create the exam type if it doesn't exist
                $newType = ExamType::create([
                    'name' => $typeName,
                    'description' => 'Examination type for ' . $typeName
                ]);
                $examTypes->push($newType);
            }
        }
        
        // Current academic year exams
        $currentYearExams = [
            // First Term Examinations
            [
                'exam_type_id' => $examTypes->where('name', 'First Term Examination')->first()->exam_type_id ?? 1,
                'name' => 'First Term Examination 2024',
                'description' => 'Comprehensive mid-year assessment covering all subjects from first semester syllabus',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-10-15',
                'end_date' => '2024-10-25',
                'publish_result' => true,
            ],
            
            // Unit Tests for First Term
            [
                'exam_type_id' => $examTypes->where('name', 'Unit Test')->first()->exam_type_id ?? 2,
                'name' => 'Unit Test 1 - Mathematics',
                'description' => 'Mathematics unit test covering algebra and basic arithmetic concepts',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-09-05',
                'end_date' => '2024-09-05',
                'publish_result' => true,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Unit Test')->first()->exam_type_id ?? 2,
                'name' => 'Unit Test 2 - Science',
                'description' => 'Science unit test covering basic physics and chemistry concepts',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-09-12',
                'end_date' => '2024-09-12',
                'publish_result' => true,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Unit Test')->first()->exam_type_id ?? 2,
                'name' => 'Unit Test 3 - English',
                'description' => 'English unit test covering grammar and composition skills',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-09-19',
                'end_date' => '2024-09-19',
                'publish_result' => true,
            ],
            
            // Monthly Tests
            [
                'exam_type_id' => $examTypes->where('name', 'Monthly Test')->first()->exam_type_id ?? 4,
                'name' => 'Monthly Test - September 2024',
                'description' => 'Comprehensive monthly assessment for all core subjects',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-09-28',
                'end_date' => '2024-09-30',
                'publish_result' => true,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Monthly Test')->first()->exam_type_id ?? 4,
                'name' => 'Monthly Test - November 2024',
                'description' => 'Comprehensive monthly assessment for all core subjects',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-11-28',
                'end_date' => '2024-11-30',
                'publish_result' => false,
            ],
            
            // Practical Examinations
            [
                'exam_type_id' => $examTypes->where('name', 'Practical Examination')->first()->exam_type_id ?? 7,
                'name' => 'Science Practical Examination',
                'description' => 'Hands-on practical assessment for physics, chemistry, and biology',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-11-10',
                'end_date' => '2024-11-12',
                'publish_result' => true,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Practical Examination')->first()->exam_type_id ?? 7,
                'name' => 'Computer Science Practical',
                'description' => 'Programming and computer applications practical assessment',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-11-15',
                'end_date' => '2024-11-15',
                'publish_result' => true,
            ],
            
            // Oral Examinations
            [
                'exam_type_id' => $examTypes->where('name', 'Oral Examination')->first()->exam_type_id ?? 8,
                'name' => 'English Oral Examination',
                'description' => 'Verbal assessment of English speaking and communication skills',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-10-08',
                'end_date' => '2024-10-10',
                'publish_result' => true,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Oral Examination')->first()->exam_type_id ?? 8,
                'name' => 'Hindi Oral Examination',
                'description' => 'Verbal assessment of Hindi speaking and communication skills',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-10-11',
                'end_date' => '2024-10-13',
                'publish_result' => true,
            ],
            
            // Competitive Examinations
            [
                'exam_type_id' => $examTypes->where('name', 'Olympiad')->first()->exam_type_id ?? 9,
                'name' => 'Mathematics Olympiad 2024',
                'description' => 'Advanced mathematics competition for talented students',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-12-05',
                'end_date' => '2024-12-05',
                'publish_result' => false,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Science Olympiad')->first()->exam_type_id ?? 10,
                'name' => 'Science Olympiad 2024',
                'description' => 'Advanced science competition for talented students',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-12-10',
                'end_date' => '2024-12-10',
                'publish_result' => false,
            ],
            
            // Diagnostic Tests
            [
                'exam_type_id' => $examTypes->where('name', 'Diagnostic Test')->first()->exam_type_id ?? 11,
                'name' => 'Beginning of Year Diagnostic Test',
                'description' => 'Assessment to identify student learning levels at start of academic year',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-07-01',
                'end_date' => '2024-07-03',
                'publish_result' => true,
            ],
            
            // Second Term Examinations (Upcoming)
            [
                'exam_type_id' => $examTypes->where('name', 'Second Term Examination')->first()->exam_type_id ?? 12,
                'name' => 'Second Term Examination 2025',
                'description' => 'End-of-year comprehensive examination covering full academic year syllabus',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2025-03-15',
                'end_date' => '2025-03-25',
                'publish_result' => false,
            ],
            
            // Weekly Tests
            [
                'exam_type_id' => $examTypes->where('name', 'Weekly Test')->first()->exam_type_id ?? 13,
                'name' => 'Weekly Test - Week 1',
                'description' => 'Short assessment to reinforce weekly learning objectives',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-09-02',
                'end_date' => '2024-09-02',
                'publish_result' => true,
            ],
            [
                'exam_type_id' => $examTypes->where('name', 'Weekly Test')->first()->exam_type_id ?? 13,
                'name' => 'Weekly Test - Week 2',
                'description' => 'Short assessment to reinforce weekly learning objectives',
                'academic_year_id' => $academicYear->academic_year_id,
                'start_date' => '2024-09-09',
                'end_date' => '2024-09-09',
                'publish_result' => true,
            ],
        ];

        foreach ($currentYearExams as $examData) {
            Exam::firstOrCreate(
                [
                    'name' => $examData['name'],
                    'academic_year_id' => $examData['academic_year_id'],
                ],
                $examData
            );
        }
    }
}