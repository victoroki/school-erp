<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExamType;

class ExamTypesSeeder extends Seeder
{
    public function run(): void
    {
        $examTypes = [
            // Primary Assessment Types
            ['name' => 'Unit Test', 'description' => 'Short assessments covering specific topics or chapters, typically 20-30 minutes'],
            ['name' => 'Quiz', 'description' => 'Quick assessments to check understanding, usually 10-15 minutes'],
            ['name' => 'Class Test', 'description' => 'Informal classroom assessments conducted by teachers'],
            
            // Term Examinations
            ['name' => 'First Term Examination', 'description' => 'Mid-year comprehensive examination covering first semester syllabus'],
            ['name' => 'Second Term Examination', 'description' => 'End-of-year comprehensive examination covering full academic year'],
            ['name' => 'Half Yearly Examination', 'description' => 'Mid-academic year assessment covering first half of syllabus'],
            ['name' => 'Annual Examination', 'description' => 'Final examination covering complete academic year syllabus'],
            
            // Periodic Assessments
            ['name' => 'Monthly Test', 'description' => 'Regular monthly assessments to track student progress'],
            ['name' => 'Weekly Test', 'description' => 'Short weekly assessments to reinforce learning'],
            ['name' => 'Fortnightly Test', 'description' => 'Bi-weekly assessments for continuous evaluation'],
            
            // Specialized Examinations
            ['name' => 'Practical Examination', 'description' => 'Hands-on assessment for science, computer, and vocational subjects'],
            ['name' => 'Oral Examination', 'description' => 'Verbal assessment for language and communication skills'],
            ['name' => 'Project Assessment', 'description' => 'Evaluation of student projects and practical assignments'],
            ['name' => 'Viva Voce', 'description' => 'Oral defense of projects and research work'],
            
            // Competitive Examinations
            ['name' => 'Olympiad', 'description' => 'Competitive examination for talented students'],
            ['name' => 'Scholarship Test', 'description' => 'Examination for scholarship and merit-based awards'],
            ['name' => 'Entrance Examination', 'description' => 'Admission test for higher classes or specialized programs'],
            
            // Remedial Assessments
            ['name' => 'Remedial Test', 'description' => 'Special test for students who need additional support'],
            ['name' => 'Improvement Test', 'description' => 'Second chance examination for performance improvement'],
            ['name' => 'Re-examination', 'description' => 'Supplementary examination for students who missed or failed'],
            
            // Skill-Based Assessments
            ['name' => 'Co-curricular Assessment', 'description' => 'Evaluation of extracurricular activities and talents'],
            ['name' => 'Sports Assessment', 'description' => 'Physical education and sports performance evaluation'],
            ['name' => 'Art Assessment', 'description' => 'Creative arts, music, and cultural activities evaluation'],
            
            // Modern Assessment Types
            ['name' => 'Online Test', 'description' => 'Computer-based assessment and digital evaluation'],
            ['name' => 'Open Book Test', 'description' => 'Assessment allowing reference materials'],
            ['name' => 'Take Home Test', 'description' => 'Assignment-based assessment completed at home'],
            
            // Diagnostic Assessments
            ['name' => 'Diagnostic Test', 'description' => 'Assessment to identify learning gaps and strengths'],
            ['name' => 'Placement Test', 'description' => 'Test to determine appropriate class or level placement'],
            ['name' => 'Aptitude Test', 'description' => 'Assessment of natural abilities and potential'],
            
            // Certification Examinations
            ['name' => 'Board Examination', 'description' => 'External board-conducted examination for certification'],
            ['name' => 'Internal Assessment', 'description' => 'School-based continuous evaluation component'],
            ['name' => 'External Assessment', 'description' => 'Third-party evaluation and certification'],
        ];

        foreach ($examTypes as $data) {
            ExamType::firstOrCreate(
                ['name' => $data['name']],
                $data
            );
        }
    }
}