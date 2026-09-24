<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\AcademicYear;
use App\Models\ClassSection;
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

        // Derived rather than hardcoded. This asserted 6, which predates the
        // seeder being widened to the full 14-class CBC set (PP1/PP2 plus Grades
        // 1-12) with an A and B section each, i.e. 28 class-sections. Reading the
        // expected count from the database keeps it honest as the seeder evolves,
        // and the label assertion below still proves real rows reached the payload.
        $expected = ClassSection::where('academic_year_id', $year->academic_year_id)->count();
        $this->assertGreaterThan(0, $expected, 'The seeder defined no class sections for the current year.');

        $response->assertJsonCount($expected);
        $response->assertJsonStructure([['id', 'label']]);

        // A real "Class - Section" label, proving the join resolved.
        $labels = array_column($response->json(), 'label');
        $this->assertNotEmpty(array_filter($labels, fn ($l) => str_contains((string) $l, ' - ')));
    }
}
