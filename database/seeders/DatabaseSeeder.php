<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RbacSeeder::class,
            SuperAdminSeeder::class,
            AcademicYearSeeder::class,
            SchoolClassSeeder::class,
            SectionSeeder::class,
            ClassroomSeeder::class,
            PeriodSeeder::class,
            SubjectSeeder::class,
            StaffSeeder::class,
            ClassSectionSeeder::class,
            ClassSubjectSeeder::class,
            TeacherSubjectSeeder::class,
            LibrarySeeder::class,
            TimetableSeeder::class,
            StudentsSeeder::class,
            ParentsSeeder::class,
            StudentParentRelationshipSeeder::class,
            StudentClassEnrollmentSeeder::class,
            StudentDocumentSeeder::class,
            GradingScalesSeeder::class,
            ExamTypesSeeder::class,
            ExamsSeeder::class,
            ExamSchedulesSeeder::class,
            SimpleExamResultsSeeder::class,
            FeeSeeder::class,
            LibrarySeeder::class,
            InventorySeeder::class,
            HostelSeeder::class,
            TransportSeeder::class,
            CommunicationSeeder::class,
        ]);
    }
}