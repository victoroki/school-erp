<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            // Core Subjects
            ['subject_code' => 'MATH', 'name' => 'Mathematics', 'description' => 'Mathematical concepts, algebra, geometry, and problem solving'],
            ['subject_code' => 'ENG', 'name' => 'English Language', 'description' => 'Grammar, composition, literature, and communication skills'],
            ['subject_code' => 'SCI', 'name' => 'General Science', 'description' => 'Physics, chemistry, and biology fundamentals'],
            
            // Additional Core Subjects
            ['subject_code' => 'SOC', 'name' => 'Social Studies', 'description' => 'History, geography, civics, and social sciences'],
            ['subject_code' => 'COMP', 'name' => 'Computer Science', 'description' => 'Programming, digital literacy, and technology skills'],
            ['subject_code' => 'ART', 'name' => 'Art & Craft', 'description' => 'Drawing, painting, sculpture, and creative expression'],
            
            // Language Options
            ['subject_code' => 'HINDI', 'name' => 'Hindi', 'description' => 'Hindi language, literature, and cultural studies'],
            ['subject_code' => 'FRENCH', 'name' => 'French', 'description' => 'French language and francophone culture'],
            ['subject_code' => 'SPAN', 'name' => 'Spanish', 'description' => 'Spanish language and hispanic culture'],
            
            // Science Specializations
            ['subject_code' => 'PHY', 'name' => 'Physics', 'description' => 'Advanced physics concepts and experiments'],
            ['subject_code' => 'CHEM', 'name' => 'Chemistry', 'description' => 'Chemical reactions, organic and inorganic chemistry'],
            ['subject_code' => 'BIO', 'name' => 'Biology', 'description' => 'Living organisms, genetics, and life sciences'],
            
            // Mathematics Specializations
            ['subject_code' => 'ALG', 'name' => 'Algebra', 'description' => 'Advanced algebraic concepts and equations'],
            ['subject_code' => 'GEO', 'name' => 'Geometry', 'description' => 'Geometric shapes, theorems, and spatial reasoning'],
            ['subject_code' => 'STAT', 'name' => 'Statistics', 'description' => 'Data analysis, probability, and statistical methods'],
            
            // Arts & Humanities
            ['subject_code' => 'LIT', 'name' => 'Literature', 'description' => 'Classic and contemporary literary works analysis'],
            ['subject_code' => 'MUSIC', 'name' => 'Music', 'description' => 'Music theory, performance, and appreciation'],
            ['subject_code' => 'DRAMA', 'name' => 'Drama', 'description' => 'Theater arts, acting, and dramatic performance'],
            
            // Physical Education
            ['subject_code' => 'PE', 'name' => 'Physical Education', 'description' => 'Sports, fitness, and physical development'],
            ['subject_code' => 'HEALTH', 'name' => 'Health Education', 'description' => 'Health awareness, nutrition, and wellness'],
            
            // Vocational Subjects
            ['subject_code' => 'ECON', 'name' => 'Economics', 'description' => 'Economic principles, markets, and financial literacy'],
            ['subject_code' => 'BUS', 'name' => 'Business Studies', 'description' => 'Business principles, management, and entrepreneurship'],
            ['subject_code' => 'ACC', 'name' => 'Accounting', 'description' => 'Financial accounting, bookkeeping, and financial statements'],
        ];

        foreach ($subjects as $data) {
            Subject::firstOrCreate(
                ['subject_code' => $data['subject_code']],
                $data
            );
        }
    }
}