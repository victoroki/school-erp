<?php

namespace Tests\Feature;

use App\Http\Controllers\GradingScaleController;
use App\Models\GradingScale;
use App\Services\CbeGradingService;
use App\Support\GradeBadge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * One authoritative CBE scale.
 *
 * The application previously carried four conflicting percentage-to-level
 * mappings, all reachable by a teacher:
 *
 *   1. GradingScaleController::seedGrades('cbc')  EE 76-100 / ME 51-75.99 / AE 26-50.99 / BE 0-25.99
 *   2. CbeGradingService::LEVELS (8 point)        EE1 90-100 ... BE2 0-10.99
 *   3. grade_book legend (hardcoded)              EE >=75 / ME 41-74 / AE 21-40 / BE <=20
 *   4. exam_results badge map                     only EE/ME/AE/BE -> everything else red
 *
 * Plus KCSE bands in the same global table, overlapping the CBE bands.
 */
class CbeGradingConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_cbe_scale_is_derived_from_the_achievement_service(): void
    {
        app(GradingScaleController::class)->seedCbc();

        $expected = collect(CbeGradingService::LEVELS)
            ->mapWithKeys(fn (array $level) => [
                $level['code'] => [(float) $level['min'], (float) $level['max'], (float) $level['points']],
            ])
            ->all();

        $actual = GradingScale::where('education_system', 'CBC')
            ->get()
            ->mapWithKeys(fn (GradingScale $scale) => [
                $scale->name => [(float) $scale->min_percentage, (float) $scale->max_percentage, (float) $scale->grade_point],
            ])
            ->all();

        $this->assertSame(
            $expected,
            $actual,
            'The seeded CBE scale must match CbeGradingService::LEVELS exactly.'
        );

        // The old contradictory boundaries must not reappear.
        $this->assertArrayNotHasKey('EE', $actual);
        $this->assertArrayNotHasKey('ME', $actual);
    }

    public function test_seeded_kcse_scale_is_tagged_8_4_4(): void
    {
        app(GradingScaleController::class)->seedKcse();

        $this->assertGreaterThan(0, GradingScale::where('education_system', '8-4-4')->count());

        $this->assertSame(
            0,
            GradingScale::where('education_system', 'CBC')->count(),
            'Seeding KCSE must not create CBC-tagged rows.'
        );
    }

    public function test_a_learner_is_graded_on_their_own_curriculum(): void
    {
        // 55% sits inside BOTH of these bands.
        $kcse = GradingScale::create([
            'name' => 'C+', 'education_system' => '8-4-4',
            'min_percentage' => 55, 'max_percentage' => 59.99, 'grade_point' => 7,
        ]);
        $cbe = GradingScale::create([
            'name' => 'ME2', 'education_system' => 'CBC',
            'min_percentage' => 41, 'max_percentage' => 57.99, 'grade_point' => 5,
        ]);

        $this->assertSame(
            $cbe->grade_id,
            GradingScale::resolveForPercentage(55.0, 'CBC')?->grade_id,
            'A CBC learner at 55% must be graded on the CBE scale.'
        );

        $this->assertSame(
            $kcse->grade_id,
            GradingScale::resolveForPercentage(55.0, '8-4-4')?->grade_id,
            'An 8-4-4 learner at 55% must be graded on the KCSE scale.'
        );
    }

    public function test_untagged_scales_still_apply_to_every_curriculum(): void
    {
        $generic = GradingScale::create([
            'name' => 'A', 'education_system' => null,
            'min_percentage' => 80, 'max_percentage' => 100, 'grade_point' => 12,
        ]);

        $this->assertSame($generic->grade_id, GradingScale::resolveForPercentage(85.0, 'CBC')?->grade_id);
        $this->assertSame($generic->grade_id, GradingScale::resolveForPercentage(85.0, '8-4-4')?->grade_id);
        $this->assertSame($generic->grade_id, GradingScale::resolveForPercentage(85.0, null)?->grade_id);
    }

    public function test_grade_resolution_is_deterministic_when_bands_overlap(): void
    {
        $wide = GradingScale::create([
            'name' => 'WIDE', 'education_system' => null,
            'min_percentage' => 0, 'max_percentage' => 100, 'grade_point' => 1,
        ]);
        $narrow = GradingScale::create([
            'name' => 'NARROW', 'education_system' => null,
            'min_percentage' => 40, 'max_percentage' => 50, 'grade_point' => 2,
        ]);

        // Repeated resolution must always pick the same band, and the more
        // specific one (highest minimum) rather than an arbitrary row.
        for ($i = 0; $i < 5; $i++) {
            $this->assertSame(
                $narrow->grade_id,
                GradingScale::resolveForPercentage(45.0, 'CBC')?->grade_id
            );
        }

        $this->assertNotSame($wide->grade_id, $narrow->grade_id);
    }

    public function test_top_achievement_codes_are_not_rendered_as_failures(): void
    {
        // The concrete bug: the 8-point codes the app stores were not in the
        // view's list, so every one of them fell through to badge-danger.
        $this->assertSame('badge-success', GradeBadge::for('EE1'));
        $this->assertSame('badge-success', GradeBadge::for('EE2'));
        $this->assertSame('badge-success', GradeBadge::for('EE'));
        $this->assertSame('badge-primary', GradeBadge::for('ME1'));
        $this->assertSame('badge-primary', GradeBadge::for('ME'));
        $this->assertSame('badge-warning', GradeBadge::for('AE2'));
        $this->assertSame('badge-danger', GradeBadge::for('BE2'));
        $this->assertSame('badge-secondary', GradeBadge::for(null));

        // KCSE letters still work.
        $this->assertSame('badge-success', GradeBadge::for('A'));
        $this->assertSame('badge-danger', GradeBadge::for('E'));
        $this->assertSame('badge-danger', GradeBadge::for('F'));
    }

    public function test_the_grade_book_legend_no_longer_hardcodes_a_fourth_scale(): void
    {
        $blade = file_get_contents(resource_path('views/grade_book/index.blade.php'));

        $this->assertStringNotContainsString('KJSEA scale', $blade);
        $this->assertStringNotContainsString('EE ≥75', $blade);
        $this->assertStringContainsString('CbeGradingService::LEVELS', $blade);
    }

    public function test_exam_result_grades_against_the_students_curriculum(): void
    {
        $source = file_get_contents(app_path('Models/ExamResult.php'));

        $this->assertStringContainsString('resolveForPercentage', $source);
        $this->assertStringContainsString("value('education_system')", $source);
        $this->assertStringNotContainsString(
            "GradingScale::where('min_percentage', '<=', \$percentage)\n                ->where('max_percentage', '>=', \$percentage)\n                ->first()",
            $source
        );
    }
}
