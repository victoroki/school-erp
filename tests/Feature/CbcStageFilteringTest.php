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
 * CBC stage vocabulary.
 *
 * cbc_learning_areas.level is a *stage* ('Pre-Primary', 'Lower Primary',
 * 'Upper Primary', 'Junior School') while subjects.grade_level is a single
 * grade, and nothing related the two. The competency-assessment screen therefore
 * offered every learning area in the school: a Grade 4 teacher was shown Junior
 * School areas, and a Grade 9 teacher Lower Primary ones.
 *
 * CbcStage is the one place that relates a class to its stage. The mapping is
 * easy to get wrong because classes.numeric_value is a sequential index rather
 * than the grade number — PP1 is 1 and Grade 1 is 3 — so treating it as the grade
 * would misclassify every class in the school, which is what most of these cases
 * are guarding.
 */
class CbcStageFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ClassSection $grade4;

    protected ClassSection $grade9;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'cbc-stage@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    private function classSection(string $name, int $numericValue): ClassSection
    {
        $year = AcademicYear::firstOrCreate(
            ['name' => 'AY-stage'],
            ['start_date' => '2026-01-01', 'end_date' => '2026-12-31', 'is_current' => true]
        );

        $class = SchoolClass::create(['name' => $name, 'numeric_value' => $numericValue]);
        $section = Section::create(['name' => 'A' . substr(uniqid(), -4)]);

        return ClassSection::create([
            'academic_year_id' => $year->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
    }

    private function learningArea(string $name, ?string $level): CbcLearningArea
    {
        return CbcLearningArea::create(['name' => $name, 'level' => $level, 'status' => true]);
    }

    public function test_the_numeric_value_is_not_the_grade_number(): void
    {
        // classes.numeric_value runs PP1 = 1 ... Grade 12 = 14, so Grade 1 sits at
        // 3. Reading 6 as "Grade 6" instead of "the class at position 6, which is
        // Grade 4" would put every class in the wrong stage.
        $this->assertSame(CbcStage::LOWER_PRIMARY, CbcStage::forNumericValue(3), 'numeric 3 is Grade 1');
        $this->assertSame(CbcStage::UPPER_PRIMARY, CbcStage::forNumericValue(6), 'numeric 6 is Grade 4');
        $this->assertSame(CbcStage::JUNIOR, CbcStage::forNumericValue(11), 'numeric 11 is Grade 9');
        $this->assertSame(CbcStage::SENIOR, CbcStage::forNumericValue(14), 'numeric 14 is Grade 12');
        $this->assertSame(CbcStage::PRE_PRIMARY, CbcStage::forNumericValue(1), 'numeric 1 is PP1');
    }

    public function test_the_class_name_is_preferred_over_the_numeric_value(): void
    {
        $this->assertSame(CbcStage::PRE_PRIMARY, CbcStage::forClass('PP1', 1));
        $this->assertSame(CbcStage::PRE_PRIMARY, CbcStage::forClass('PP2', 2));
        $this->assertSame(CbcStage::LOWER_PRIMARY, CbcStage::forClass('Grade 1', 3));
        $this->assertSame(CbcStage::UPPER_PRIMARY, CbcStage::forClass('Grade 4', 6));
        $this->assertSame(CbcStage::UPPER_PRIMARY, CbcStage::forClass('Grade 6', 8));
        $this->assertSame(CbcStage::JUNIOR, CbcStage::forClass('Grade 7', 9));
        $this->assertSame(CbcStage::JUNIOR, CbcStage::forClass('Grade 9', 11));
        $this->assertSame(CbcStage::SENIOR, CbcStage::forClass('Grade 10', 12));
        $this->assertSame(CbcStage::SENIOR, CbcStage::forClass('Grade 12', 14));

        // Falls back to the numeric value only when the name carries no grade.
        $this->assertSame(CbcStage::UPPER_PRIMARY, CbcStage::forClass('Blue Class', 6));
        $this->assertNull(CbcStage::forClass('Blue Class', null));
    }

    public function test_the_assessment_screen_only_offers_the_selected_class_stage(): void
    {
        $this->grade4 = $this->classSection('Grade 4', 6);
        $this->grade9 = $this->classSection('Grade 9', 11);

        $this->learningArea('PrePrimary Area', CbcStage::PRE_PRIMARY);
        $this->learningArea('LowerPrimary Area', CbcStage::LOWER_PRIMARY);
        $this->learningArea('UpperPrimary Area', CbcStage::UPPER_PRIMARY);
        $this->learningArea('Junior Area', CbcStage::JUNIOR);
        $this->learningArea('Unlevelled Area', null);

        $response = $this->actingAs($this->admin)
            ->get(route('cbc-assessments.index', [
                'class_section_id' => $this->grade4->class_section_id,
            ]))
            ->assertOk();

        $response->assertSee('UpperPrimary Area');
        // Areas with no level stay available, matching ClassSubjectController.
        $response->assertSee('Unlevelled Area');

        // The defect: these were all offered for a Grade 4 class.
        $response->assertDontSee('Junior Area');
        $response->assertDontSee('PrePrimary Area');
        $response->assertDontSee('LowerPrimary Area');
    }

    public function test_a_grade_nine_class_is_offered_junior_school_areas(): void
    {
        $this->grade4 = $this->classSection('Grade 4', 6);
        $this->grade9 = $this->classSection('Grade 9', 11);

        $this->learningArea('UpperPrimary Area', CbcStage::UPPER_PRIMARY);
        $this->learningArea('Junior Area', CbcStage::JUNIOR);

        $response = $this->actingAs($this->admin)
            ->get(route('cbc-assessments.index', [
                'class_section_id' => $this->grade9->class_section_id,
            ]))
            ->assertOk();

        $response->assertSee('Junior Area');
        $response->assertDontSee('UpperPrimary Area');
    }

    public function test_with_no_class_selected_every_area_is_offered(): void
    {
        $this->learningArea('UpperPrimary Area', CbcStage::UPPER_PRIMARY);
        $this->learningArea('Junior Area', CbcStage::JUNIOR);

        $response = $this->actingAs($this->admin)
            ->get(route('cbc-assessments.index'))
            ->assertOk();

        $response->assertSee('UpperPrimary Area');
        $response->assertSee('Junior Area');
    }
}
