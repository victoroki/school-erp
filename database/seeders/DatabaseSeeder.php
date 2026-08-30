<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * The seeders run in strict dependency order: reference/lookup rows are
     * always created before the rows that point at them, and every foreign
     * key is resolved by lookup (never a hardcoded id), so a reseed is always
     * safe and idempotent.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::statement('SET SESSION sql_mode=""');

        $this->call([
            // 1. RBAC, platform owner, modules
            PermissionSeeder::class,
            RbacSeeder::class,
            ModuleSeeder::class,
            OwnerSeeder::class,

            // 2. Academic calendar (academic years + Kenyan 3-term calendar)
            AcademicYearSeeder::class,
            DepartmentSeeder::class,

            // 3. Academic structure (CBC classes, sections, rooms, periods, subjects)
            SchoolClassSeeder::class,
            SectionSeeder::class,
            ClassroomSeeder::class,
            PeriodSeeder::class,
            SubjectSeeder::class,

            // 4. People
            StaffSeeder::class,

            // 5. CBC learning-area curriculum
            CbeCurriculumSeeder::class,

            // 6. Academic linking (class-sections, class-subjects, teachers, timetable)
            ClassSectionSeeder::class,
            ClassSubjectSeeder::class,
            TeacherSubjectSeeder::class,
            TimetableSeeder::class,

            // 7. Students, parents, relationships
            StudentsSeeder::class,
            ParentsSeeder::class,
            StudentParentRelationshipSeeder::class,
            StudentClassEnrollmentSeeder::class,
            StudentDocumentSeeder::class,

            // 8. Exams
            GradingScalesSeeder::class,
            ExamTypesSeeder::class,
            ExamsSeeder::class,
            ExamSchedulesSeeder::class,
            ExamResultsSeeder::class,

            // 9. Fees + finance
            FinancialManagementSeeder::class,
            FeeSeeder::class,

            // 10. Facilities: hostels, transport, library, inventory
            HostelSeeder::class,
            TransportSeeder::class,
            LibrarySeeder::class,
            InventorySeeder::class,

            // 11. Communication
            CommunicationTriggerSeeder::class,
            CommunicationTemplateSeeder::class,
            CommunicationSeeder::class,
        ]);
    }
}
