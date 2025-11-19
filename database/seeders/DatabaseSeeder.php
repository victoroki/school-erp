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
            TimetableSeeder::class,
        ]);
    }
}
