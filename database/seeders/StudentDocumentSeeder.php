<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\StudentDocument;

class StudentDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $students = Student::all();
        if ($students->count() === 0) {
            return;
        }

        $documents = [
            'birth_certificate' => 'Birth Certificate',
            'immunization_record' => 'Immunization Record',
            'previous_school_transcript' => 'Previous School Transcript',
            'report_card' => 'Report Card',
            'medical_record' => 'Medical Record',
            'photo_id' => 'Student Photo ID',
            'address_proof' => 'Address Proof',
            'parent_id' => 'Parent ID Proof',
        ];

        foreach ($students as $student) {
            // Create birth certificate for all students
            StudentDocument::firstOrCreate(
                [
                    'student_id' => $student->student_id,
                    'document_type' => 'birth_certificate',
                    'document_name' => 'Birth Certificate',
                ],
                [
                    'file_path' => 'student_documents/birth_cert_' . $student->admission_no . '.pdf',
                    'uploaded_at' => now()->subDays(rand(1, 30)),
                ]
            );

            // Create immunization record for most students (90%)
            if (rand(1, 100) <= 90) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'immunization_record',
                        'document_name' => 'Immunization Record',
                    ],
                    [
                        'file_path' => 'student_documents/immunization_' . $student->admission_no . '.pdf',
                        'uploaded_at' => now()->subDays(rand(5, 35)),
                    ]
                );
            }

            // Create previous school transcript for students who transferred (70%)
            if ($student->admission_date < '2024-09-01' && rand(1, 100) <= 70) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'previous_school_transcript',
                        'document_name' => 'Previous School Transcript',
                    ],
                    [
                        'file_path' => 'student_documents/transcript_' . $student->admission_no . '.pdf',
                        'uploaded_at' => now()->subDays(rand(10, 40)),
                    ]
                );
            }

            // Create report cards for current students (80%)
            if ($student->status === 'active' && rand(1, 100) <= 80) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'report_card',
                        'document_name' => 'First Quarter Report Card',
                    ],
                    [
                        'file_path' => 'student_documents/report_card_q1_' . $student->admission_no . '.pdf',
                        'uploaded_at' => now()->subDays(rand(15, 45)),
                    ]
                );
            }

            // Create medical records for some students (40%)
            if (rand(1, 100) <= 40) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'medical_record',
                        'document_name' => 'Medical Record',
                    ],
                    [
                        'file_path' => 'student_documents/medical_' . $student->admission_no . '.pdf',
                        'uploaded_at' => now()->subDays(rand(20, 50)),
                    ]
                );
            }

            // Create student photo for most students (85%)
            if (rand(1, 100) <= 85) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'photo_id',
                        'document_name' => 'Student Photo ID',
                    ],
                    [
                        'file_path' => 'student_documents/photo_' . $student->admission_no . '.jpg',
                        'uploaded_at' => now()->subDays(rand(1, 20)),
                    ]
                );
            }

            // Create address proof for some students (60%)
            if (rand(1, 100) <= 60) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'address_proof',
                        'document_name' => 'Address Proof',
                    ],
                    [
                        'file_path' => 'student_documents/address_proof_' . $student->admission_no . '.pdf',
                        'uploaded_at' => now()->subDays(rand(25, 55)),
                    ]
                );
            }

            // Create parent ID proof for some students (75%)
            if (rand(1, 100) <= 75) {
                StudentDocument::firstOrCreate(
                    [
                        'student_id' => $student->student_id,
                        'document_type' => 'parent_id',
                        'document_name' => 'Parent ID Proof',
                    ],
                    [
                        'file_path' => 'student_documents/parent_id_' . $student->admission_no . '.pdf',
                        'uploaded_at' => now()->subDays(rand(30, 60)),
                    ]
                );
            }
        }
    }
}