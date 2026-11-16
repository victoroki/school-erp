<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\CbcLearningArea;
use App\Models\ClassSection;
use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\User;
use App\Support\CbcStage;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * GET /api/mobile/exams/cbc/structure returned every learning area in the school
 * to every caller, the same defect fixed on the web assessment screen in P2-E:
 * cbc_learning_areas.level is a CBC *stage* while a class carries a numeric
 * level, and nothing related the two.
 *
 * The class_id and class_section_id parameters are OPTIONAL by design. The
 * response keys and types are unchanged in both cases; only which rows come back
 * differs. A caller that sends no class gets exactly the previous behaviour,
 * which the first test pins so this cannot quietly become a breaking change.
 */
class MobileCbcStructureStageTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;

    protected SchoolClass $grade4Class;

    protected ClassSection $grade4Section;

    protected ClassSection $grade9Section;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->teacher = User::factory()->create(['email' => 'cbc-mobile-' . uniqid() . '@test.local']);
        $this->teacher->roles()->sync(Role::where('role_name', 'Teacher')->pluck('role_id'));
        $this->teacher->load('roles.permissions');

        $year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        // numeric_value is a sequential index: Grade 4 sits at 6, Grade 9 at 11.
        $this->grade4Class = SchoolClass::create(['name' => 'Grade 4', 'numeric_value' => 6]);
        $grade9Class = SchoolClass::create(['name' => 'Grade 9', 'numeric_value' => 11]);

        $this->grade4Section = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $this->grade4Class->class_id,
            'section_id' => Section::create(['name' => 'A' . substr(uniqid(), -4)])->section_id,
        ]);

        $this->grade9Section = ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $grade9Class->class_id,
            'section_id' => Section::create(['name' => 'B' . substr(uniqid(), -4)])->section_id,
        ]);

        CbcLearningArea::create(['name' => 'UpperPrimary Area', 'level' => CbcStage::UPPER_PRIMARY, 'status' => true]);
        CbcLearningArea::create(['name' => 'Junior Area', 'level' => CbcStage::JUNIOR, 'status' => true]);
        CbcLearningArea::create(['name' => 'Unlevelled Area', 'level' => null, 'status' => true]);
    }

    private function token(): string
    {
        return $this->teacher->createToken('mobile', ['mobile:access'])->plainTextToken;
    }

    /**
     * @return list<string>
     */
    private function areaNames(string $query = ''): array
    {
        $response = $this->withToken($this->token())
            ->getJson('/api/mobile/exams/cbc/structure' . $query)
            ->assertOk();

        return collect($response->json('learning_areas'))->pluck('name')->all();
    }

    public function test_without_a_class_every_area_is_returned_as_before(): void
    {
        $names = $this->areaNames();

        // Backward compatibility: an existing client that sends no class must see
        // exactly what it saw before, or this would be a breaking change.
        $this->assertContains('UpperPrimary Area', $names);
        $this->assertContains('Junior Area', $names);
        $this->assertContains('Unlevelled Area', $names);
    }

    public function test_a_grade_four_class_narrows_to_upper_primary(): void
    {
        $names = $this->areaNames('?class_id=' . $this->grade4Class->class_id);

        $this->assertContains('UpperPrimary Area', $names);
        // Areas with no level stay available — narrowing must not hide unclassified data.
        $this->assertContains('Unlevelled Area', $names);
        $this->assertNotContains('Junior Area', $names);
    }

    public function test_a_grade_nine_section_narrows_to_junior_school(): void
    {
        $names = $this->areaNames('?class_section_id=' . $this->grade9Section->class_section_id);

        $this->assertContains('Junior Area', $names);
        $this->assertNotContains('UpperPrimary Area', $names);
    }

    public function test_the_class_section_agrees_with_the_class_it_belongs_to(): void
    {
        // Both routes to the stage must resolve the same way, since the web
        // screen uses the section and this endpoint accepts either.
        $bySection = $this->areaNames('?class_section_id=' . $this->grade4Section->class_section_id);
        $byClass = $this->areaNames('?class_id=' . $this->grade4Class->class_id);

        sort($bySection);
        sort($byClass);

        $this->assertSame($byClass, $bySection);
    }

    public function test_an_unknown_class_does_not_narrow_silently(): void
    {
        // A class that does not exist yields no stage, so the full list comes back
        // rather than an empty one — a client typo must not look like "no learning
        // areas exist".
        $names = $this->areaNames('?class_id=99999999');

        $this->assertContains('UpperPrimary Area', $names);
        $this->assertContains('Junior Area', $names);
    }
}
