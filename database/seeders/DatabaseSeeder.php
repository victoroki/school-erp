<?php

namespace Database\Seeders;

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
            OwnerSeeder::class,
            AuditAndCommunicationPermissionsSeeder::class,
            ModuleSeeder::class,
            // SuperAdminSeeder::class,
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
            TimetableSeeder::class,
            // LibrarySeeder::class,
            // StudentsSeeder::class,
            // ParentsSeeder::class,
            // StudentParentRelationshipSeeder::class,
            // StudentClassEnrollmentSeeder::class,
            // StudentDocumentSeeder::class,
            // GradingScalesSeeder::class,
            // ExamTypesSeeder::class,
            // ExamsSeeder::class,
            // ExamSchedulesSeeder::class,
            // SimpleExamResultsSeeder::class,
            // FeeSeeder::class,
            // InventorySeeder::class,
            // HostelSeeder::class,
            // TransportSeeder::class,
            // CommunicationSeeder::class,
            CommunicationTriggerSeeder::class,
            CommunicationTemplateSeeder::class,
        ]);
    }
}
