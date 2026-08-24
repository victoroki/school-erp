<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AcademicYear;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\AcademicYearSeeder;
use Database\Seeders\SchoolClassSeeder;
use Database\Seeders\SectionSeeder;
use Database\Seeders\ClassroomSeeder;
use Database\Seeders\PeriodSeeder;
use Database\Seeders\SubjectSeeder;
use Database\Seeders\StaffSeeder;
use Database\Seeders\ClassSectionSeeder;
use Database\Seeders\ClassSubjectSeeder;
use Database\Seeders\TeacherSubjectSeeder;
use Database\Seeders\TimetableSeeder;

class TimetableAjaxSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_class_sections_by_year_returns_seeded_sections(): void
    {
        $this->seed([
            PermissionSeeder::class,
            RbacSeeder::class,
            ModuleSeeder::class,
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

        $user = User::factory()->create();
        $year = AcademicYear::where('is_current', true)->firstOrFail();

        $response = $this->actingAs($user)
            ->getJson("/api/academic-years/{$year->academic_year_id}/class-sections");

        $response->assertOk();
        $response->assertJsonCount(6);
        $response->assertJsonStructure([['id', 'label']]);
    }
}
