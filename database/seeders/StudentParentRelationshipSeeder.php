<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Parents;
use App\Models\StudentParentRelationship;

class StudentParentRelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        $parents = Parents::all();

        if ($students->count() === 0 || $parents->count() === 0) {
            return;
        }

        // Define specific parent-student relationships for more realistic data
        $relationships = [
            // Emma Johnson - Both parents
            ['student_admission' => 'ADM2024001', 'parent_email' => 'david.johnson@email.com', 'is_primary' => true],
            ['student_admission' => 'ADM2024001', 'parent_email' => 'emily.johnson@email.com', 'is_primary' => false],
            
            // Liam Martinez - Both parents
            ['student_admission' => 'ADM2024002', 'parent_email' => 'carlos.martinez@email.com', 'is_primary' => true],
            ['student_admission' => 'ADM2024002', 'parent_email' => 'maria.martinez@email.com', 'is_primary' => false],
            
            // Olivia Brown - Father only
            ['student_admission' => 'ADM2024003', 'parent_email' => 'robert.brown@email.com', 'is_primary' => true],
            
            // Noah Davis - Mother only
            ['student_admission' => 'ADM2024004', 'parent_email' => 'jennifer.davis@email.com', 'is_primary' => true],
            
            // Ava Wilson - Father only
            ['student_admission' => 'ADM2024005', 'parent_email' => 'michael.wilson@email.com', 'is_primary' => true],
            
            // Ethan Garcia - Mother only
            ['student_admission' => 'ADM2024006', 'parent_email' => 'lisa.garcia@email.com', 'is_primary' => true],
            
            // Sophia Rodriguez - Father only
            ['student_admission' => 'ADM2024007', 'parent_email' => 'antonio.rodriguez@email.com', 'is_primary' => true],
            
            // Mason Lewis - Mother only
            ['student_admission' => 'ADM2024008', 'parent_email' => 'patricia.lewis@email.com', 'is_primary' => true],
            
            // Isabella Lee - Father only
            ['student_admission' => 'ADM2024009', 'parent_email' => 'kevin.lee@email.com', 'is_primary' => true],
            
            // William Walker - Mother only
            ['student_admission' => 'ADM2024010', 'parent_email' => 'sarah.walker@email.com', 'is_primary' => true],
            
            // Mia Hall - Father only
            ['student_admission' => 'ADM2024011', 'parent_email' => 'thomas.hall@email.com', 'is_primary' => true],
            
            // James Allen - Mother only
            ['student_admission' => 'ADM2024012', 'parent_email' => 'michelle.allen@email.com', 'is_primary' => true],
            
            // Charlotte Young - Father only
            ['student_admission' => 'ADM2024013', 'parent_email' => 'christopher.young@email.com', 'is_primary' => true],
            
            // Benjamin Hernandez - Mother only
            ['student_admission' => 'ADM2024014', 'parent_email' => 'amanda.hernandez@email.com', 'is_primary' => true],
            
            // Amelia King - Father only
            ['student_admission' => 'ADM2024015', 'parent_email' => 'daniel.king@email.com', 'is_primary' => true],
            
            // Alexander Wright (Alumni) - Mother only
            ['student_admission' => 'ADM2023001', 'parent_email' => 'jessica.wright@email.com', 'is_primary' => true],
            
            // Elizabeth Lopez (Alumni) - Father only
            ['student_admission' => 'ADM2023002', 'parent_email' => 'richard.lopez@email.com', 'is_primary' => true],
            
            // Michael Hill (Transferred) - Mother only
            ['student_admission' => 'ADM2024004', 'parent_email' => 'nicole.hill@email.com', 'is_primary' => true],
            
            // Sofia Green (Inactive) - Mother only
            ['student_admission' => 'ADM2024005', 'parent_email' => 'melissa.green@email.com', 'is_primary' => true],
        ];

        foreach ($relationships as $rel) {
            $student = $students->where('admission_no', $rel['student_admission'])->first();
            $parent = $parents->where('email', $rel['parent_email'])->first();
            
            if ($student && $parent) {
                StudentParentRelationship::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'parent_id' => $parent->parent_id,
                    ],
                    [
                        'is_primary_contact' => $rel['is_primary'],
                    ]
                );
            }
        }
    }
}