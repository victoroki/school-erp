<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Department;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        // CBC learning areas (per stage) + KCSE-senior subjects, named the
        // Kenyan way rather than generic "Math101/Science101" placeholders.
        $subjects = [
            // Languages
            ['subject_code' => 'ENG', 'name' => 'English / Literacy', 'description' => 'English language, grammar, composition and literature across CBC stages', 'is_elective' => false, 'grade_level' => 3, 'department' => 'Languages Department'],
            ['subject_code' => 'KIS', 'name' => 'Kiswahili', 'description' => 'Lugha ya Kiswahili, sarufi, kusoma na kuandika (CBC learning area)', 'is_elective' => false, 'grade_level' => 3, 'department' => 'Languages Department'],
            ['subject_code' => 'KSL', 'name' => 'Kenya Sign Language', 'description' => 'Kenya Sign Language for hearing-impaired learners', 'is_elective' => true, 'grade_level' => 3, 'department' => 'Languages Department'],
            ['subject_code' => 'FRE', 'name' => 'French', 'description' => 'French as a foreign language in upper primary and senior school', 'is_elective' => true, 'grade_level' => 6, 'department' => 'Languages Department'],
            ['subject_code' => 'GER', 'name' => 'German', 'description' => 'German as a foreign language in senior school', 'is_elective' => true, 'grade_level' => 12, 'department' => 'Languages Department'],

            // Mathematics
            ['subject_code' => 'MAT', 'name' => 'Mathematics', 'description' => 'Mathematics across CBC: numbers, algebra, measurement, geometry and data handling', 'is_elective' => false, 'grade_level' => 3, 'department' => 'Mathematics Department'],

            // Sciences
            ['subject_code' => 'ISC', 'name' => 'Integrated Science', 'description' => 'Integrated Science & Health Education for Junior School (CBC)', 'is_elective' => false, 'grade_level' => 9, 'department' => 'Science Department'],
            ['subject_code' => 'SCI', 'name' => 'Science & Technology', 'description' => 'Science and Technology for Upper Primary (CBC)', 'is_elective' => false, 'grade_level' => 6, 'department' => 'Science Department'],
            ['subject_code' => 'BIO', 'name' => 'Biology', 'description' => 'Biology for Senior School (KCSE subject)', 'is_elective' => false, 'grade_level' => 12, 'department' => 'Science Department'],
            ['subject_code' => 'CHE', 'name' => 'Chemistry', 'description' => 'Chemistry for Senior School (KCSE subject)', 'is_elective' => false, 'grade_level' => 12, 'department' => 'Science Department'],
            ['subject_code' => 'PHY', 'name' => 'Physics', 'description' => 'Physics for Senior School (KCSE subject)', 'is_elective' => false, 'grade_level' => 12, 'department' => 'Science Department'],

            // Humanities
            ['subject_code' => 'SST', 'name' => 'Social Studies', 'description' => 'Social Studies & Life Skills for Upper Primary (CBC)', 'is_elective' => false, 'grade_level' => 4, 'department' => 'Humanities Department'],
            ['subject_code' => 'GEO', 'name' => 'Geography', 'description' => 'Geography for Senior School (KCSE subject)', 'is_elective' => true, 'grade_level' => 12, 'department' => 'Humanities Department'],
            ['subject_code' => 'HIS', 'name' => 'History & Government', 'description' => 'History and Government for Senior School (KCSE subject)', 'is_elective' => true, 'grade_level' => 12, 'department' => 'Humanities Department'],
            ['subject_code' => 'CRE', 'name' => 'Christian Religious Education', 'description' => 'CRE across CBC stages and KCSE', 'is_elective' => true, 'grade_level' => 4, 'department' => 'Humanities Department'],
            ['subject_code' => 'IRE', 'name' => 'Islamic Religious Education', 'description' => 'IRE across CBC stages and KCSE', 'is_elective' => true, 'grade_level' => 4, 'department' => 'Humanities Department'],

            // Business, ICT & Pre-Technical
            ['subject_code' => 'BUS', 'name' => 'Business Studies', 'description' => 'Business Studies and entrepreneurship (KCSE subject)', 'is_elective' => true, 'grade_level' => 12, 'department' => 'Business, ICT & Pre-Technical'],
            ['subject_code' => 'COM', 'name' => 'Computer Studies / ICT', 'description' => 'ICT, digital literacy and computer studies', 'is_elective' => true, 'grade_level' => 6, 'department' => 'Business, ICT & Pre-Technical'],
            ['subject_code' => 'PTS', 'name' => 'Pre-Technical Studies', 'description' => 'Pre-Technical Studies for Junior School (CBC)', 'is_elective' => false, 'grade_level' => 9, 'department' => 'Business, ICT & Pre-Technical'],

            // Creative Arts & Sports
            ['subject_code' => 'ART', 'name' => 'Art & Design', 'description' => 'Visual arts, creative arts and expression', 'is_elective' => true, 'grade_level' => 4, 'department' => 'Creative Arts & Sports'],
            ['subject_code' => 'MUS', 'name' => 'Music', 'description' => 'Music theory, performance and appreciation', 'is_elective' => true, 'grade_level' => 4, 'department' => 'Creative Arts & Sports'],
            ['subject_code' => 'PES', 'name' => 'Physical Education & Sports', 'description' => 'Physical education, sports and athletics', 'is_elective' => false, 'grade_level' => 3, 'department' => 'Creative Arts & Sports'],

            // Agriculture & Technical
            ['subject_code' => 'AGR', 'name' => 'Agriculture & Nutrition', 'description' => 'Agriculture, nutrition and food security (CBC learning area + KCSE)', 'is_elective' => false, 'grade_level' => 4, 'department' => 'Agriculture & Technical'],
            ['subject_code' => 'HMS', 'name' => 'Home Science', 'description' => 'Home science and family life (KCSE subject)', 'is_elective' => true, 'grade_level' => 12, 'department' => 'Agriculture & Technical'],
        ];

        foreach ($subjects as $data) {
            $department = Department::where('name', $data['department'])->first();
            unset($data['department']);

            $subject = Subject::firstOrCreate(
                ['subject_code' => $data['subject_code']],
                $data
            );

            if ($department && $subject->department_id !== $department->department_id) {
                $subject->department_id = $department->department_id;
                $subject->save();
            }
        }
    }
}
