<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Term;
use App\Models\User;
use App\Services\AcademicCalendarService;
use Carbon\Carbon;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Terms as a derived calendar rather than a fixed, hand-maintained set.
 *
 * This is what the system used to get wrong, in four separate ways:
 *
 *  1. AcademicYearSeeder hardcoded 2025 and 2026. Once 2026-11-20 had passed
 *     there was no 2027 to roll to, so the school stayed on 2026 indefinitely.
 *
 *  2. AcademicYearController::store() saved a year with no terms at all. That
 *     year could not be used: fee assignment refused it with "No terms are
 *     defined for the selected academic year" and the fee structure form had an
 *     empty term list. Adding a year silently created a broken one.
 *
 *  3. Term status was whatever an administrator last set by hand, so a finished
 *     term could still read 'active' and a new year could sit entirely
 *     'upcoming'. Fee assignment reads status to pick the term to charge, so a
 *     stale status means fees land on the wrong term.
 *
 *  4. is_current had no unique constraint and was never de-duplicated, so two
 *     years could both be current. Around fifteen call sites resolve the current
 *     year with where('is_current', true)->first().
 */
class AcademicCalendarTest extends TestCase
{
    use RefreshDatabase;

    private AcademicCalendarService $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, RbacSeeder::class]);

        $this->calendar = app(AcademicCalendarService::class);
    }

    private function admin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user->fresh();
    }

    // ---------------------------------------------------------------- creating

    public function test_creating_an_academic_year_creates_its_terms(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('academic-years.store'), [
            'name' => '2027',
            'start_date' => '2027-01-04',
            'end_date' => '2027-12-10',
            'is_current' => 0,
        ])->assertRedirect(route('academic-years.index'));

        $year = AcademicYear::where('name', '2027')->firstOrFail();

        // Previously this year was saved with zero terms and could not be used.
        $this->assertSame(
            AcademicCalendarService::DEFAULT_TERM_COUNT,
            $year->terms()->count(),
            'A new academic year must come with terms, not an empty year.'
        );

        foreach ($year->terms as $term) {
            $this->assertTrue(
                $term->start_date->greaterThanOrEqualTo($year->start_date),
                "{$term->name} starts before its year."
            );
            $this->assertTrue(
                $term->end_date->lessThanOrEqualTo($year->end_date),
                "{$term->name} ends after its year."
            );
        }
    }

    public function test_the_years_terms_are_ordered_and_cover_it_without_gaps(): void
    {
        $this->actingAs($this->admin());

        $this->post(route('academic-years.store'), [
            'name' => '2027',
            'start_date' => '2027-01-04',
            'end_date' => '2027-12-10',
            'is_current' => 0,
        ]);

        $year = AcademicYear::where('name', '2027')->firstOrFail();
        $terms = $year->terms()->ordered()->get();

        $this->assertSame(1, $terms->first()->display_order);

        for ($i = 1; $i < $terms->count(); $i++) {
            $this->assertTrue(
                $terms[$i]->start_date->greaterThan($terms[$i - 1]->end_date),
                'Terms must run in order and not overlap.'
            );
        }

        $this->assertTrue($terms->first()->start_date->equalTo($year->start_date));
        $this->assertTrue($terms->last()->end_date->equalTo($year->end_date));
    }

    public function test_generating_terms_twice_does_not_duplicate_them(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2027',
            'start_date' => '2027-01-04',
            'end_date' => '2027-12-10',
        ]);

        $this->calendar->createTermsFor($year);
        $this->calendar->createTermsFor($year);

        $this->assertSame(3, $year->terms()->count());
    }

    // ------------------------------------------------------------ pattern copy

    public function test_a_new_year_copies_the_previous_years_term_pattern(): void
    {
        // A school that has deliberately moved to four terms, with an uneven
        // split, rather than the three equal terms the fallback would produce.
        $previous = AcademicYear::factory()->archived()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-11-28',
        ]);

        foreach ([
            ['Term 1', 'T1', '2025-01-06', '2025-04-11', 1],
            ['Term 2', 'T2', '2025-04-28', '2025-08-01', 2],
            ['Term 3', 'T3', '2025-08-18', '2025-10-03', 3],
            ['Term 4', 'T4', '2025-10-20', '2025-11-28', 4],
        ] as [$name, $code, $start, $end, $order]) {
            Term::create([
                'academic_year_id' => $previous->academic_year_id,
                'name' => $name,
                'code' => $code,
                'start_date' => $start,
                'end_date' => $end,
                'status' => 'completed',
                'display_order' => $order,
            ]);
        }

        $next = AcademicYear::factory()->archived()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-27',
        ]);

        $terms = $this->calendar->createTermsFor($next);

        $this->assertCount(4, $terms, 'The four-term pattern should have carried across.');
        $this->assertSame(['T1', 'T2', 'T3', 'T4'], $terms->pluck('code')->all());
        $this->assertSame([1, 2, 3, 4], $terms->pluck('display_order')->all());

        foreach ($terms as $term) {
            $this->assertTrue($term->start_date->greaterThanOrEqualTo($next->start_date));
            $this->assertTrue($term->end_date->lessThanOrEqualTo($next->end_date));
        }

        // The last term should still finish on the last day of the new year,
        // since it did in the source year.
        $this->assertTrue($terms->last()->end_date->equalTo($next->end_date));
    }

    public function test_copied_terms_keep_their_shape_rather_than_being_reshaped(): void
    {
        $previous = AcademicYear::factory()->archived()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        // A single long term running most of the year.
        Term::create([
            'academic_year_id' => $previous->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2025-01-06',
            'end_date' => '2025-09-30',
            'status' => 'completed',
            'display_order' => 1,
        ]);
        Term::create([
            'academic_year_id' => $previous->academic_year_id,
            'name' => 'Term 2',
            'code' => 'T2',
            'start_date' => '2025-10-13',
            'end_date' => '2025-12-05',
            'status' => 'completed',
            'display_order' => 2,
        ]);

        $next = AcademicYear::factory()->archived()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-12-04',
        ]);

        $terms = $this->calendar->createTermsFor($next);

        $this->assertCount(2, $terms);

        // Term 1 covered roughly 3/4 of the source year, so it should still cover
        // roughly 3/4 of the new one rather than being forced to half.
        $longSpan = $terms[0]->start_date->diffInDays($terms[0]->end_date);
        $shortSpan = $terms[1]->start_date->diffInDays($terms[1]->end_date);

        $this->assertGreaterThan(
            $shortSpan * 2,
            $longSpan,
            'The long/short term ratio should survive the copy.'
        );
    }

    public function test_a_year_with_no_predecessor_is_split_into_equal_terms(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2027',
            'start_date' => '2027-09-01',
            'end_date' => '2028-06-30',
        ]);

        $terms = $this->calendar->createTermsFor($year);

        $this->assertCount(3, $terms);
        $this->assertSame(['T1', 'T2', 'T3'], $terms->pluck('code')->all());
        $this->assertTrue($terms->first()->start_date->equalTo($year->start_date));
        $this->assertTrue($terms->last()->end_date->equalTo($year->end_date));
    }

    public function test_copied_terms_do_not_inherit_the_previous_years_statuses(): void
    {
        $previous = AcademicYear::factory()->archived()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        Term::create([
            'academic_year_id' => $previous->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2025-01-06',
            'end_date' => '2025-04-11',
            'status' => 'active',
            'display_order' => 1,
        ]);

        // A future year: every term should come across as upcoming, not inherit
        // last year's 'active' and start the year already part-finished.
        $next = AcademicYear::factory()->archived()->create([
            'name' => '2026',
            'start_date' => now()->addYear()->startOfYear()->toDateString(),
            'end_date' => now()->addYear()->endOfYear()->toDateString(),
        ]);

        $terms = $this->calendar->createTermsFor($next);

        $this->assertSame('upcoming', $terms->first()->status);
    }

    // ------------------------------------------------------- status from dates

    public function test_status_is_derived_from_a_terms_dates(): void
    {
        $asOf = Carbon::parse('2026-06-15');

        $this->assertSame('completed', $this->calendar->statusFor('2026-01-05', '2026-04-03', $asOf));
        $this->assertSame('active', $this->calendar->statusFor('2026-05-04', '2026-08-07', $asOf));
        $this->assertSame('upcoming', $this->calendar->statusFor('2026-08-24', '2026-11-20', $asOf));

        // The boundaries count as inside the term, so a term does not flicker out
        // of active on its own first or last day.
        $this->assertSame('active', $this->calendar->statusFor('2026-05-04', '2026-08-07', Carbon::parse('2026-05-04')));
        $this->assertSame('active', $this->calendar->statusFor('2026-05-04', '2026-08-07', Carbon::parse('2026-08-07')));
    }

    public function test_syncing_advances_a_term_that_has_ended(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        $finished = Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-03',
            'status' => 'active',
            'display_order' => 1,
        ]);

        $this->calendar->syncTermStatuses($year->academic_year_id, Carbon::parse('2026-06-15'));

        // A term that ended two months ago was still reporting itself active,
        // which put fee assignment on the wrong term.
        $this->assertSame('completed', $finished->fresh()->status);
    }

    public function test_syncing_activates_the_term_containing_today(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        $current = Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 2',
            'code' => 'T2',
            'start_date' => '2026-05-04',
            'end_date' => '2026-08-07',
            'status' => 'upcoming',
            'display_order' => 2,
        ]);

        $this->calendar->syncTermStatuses($year->academic_year_id, Carbon::parse('2026-06-15'));

        $this->assertSame('active', $current->fresh()->status);
    }

    public function test_syncing_never_drags_a_status_backwards(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        // An administrator activated the last term early, before it started.
        // That is the one override sync must not undo — it is the whole point of
        // TermController::activate().
        $manual = Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 3',
            'code' => 'T3',
            'start_date' => '2026-08-24',
            'end_date' => '2026-11-20',
            'status' => 'active',
            'display_order' => 3,
        ]);

        $this->calendar->syncTermStatuses($year->academic_year_id, Carbon::parse('2026-06-15'));

        $this->assertSame('active', $manual->fresh()->status);
    }

    public function test_syncing_leaves_at_most_one_active_term_per_year(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        $one = Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-03',
            'status' => 'active',
            'display_order' => 1,
        ]);

        $two = Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 2',
            'code' => 'T2',
            'start_date' => '2026-05-04',
            'end_date' => '2026-08-07',
            'status' => 'active',
            'display_order' => 2,
        ]);

        $this->calendar->syncTermStatuses($year->academic_year_id, Carbon::parse('2026-06-15'));

        // "Current term" is read as Term::active()->first() in several places, so
        // two active terms made that answer arbitrary.
        $this->assertSame(1, $year->terms()->where('status', 'active')->count());
        $this->assertSame('active', $two->fresh()->status, 'The term today falls in should win.');
        $this->assertNotSame('active', $one->fresh()->status);
    }

    public function test_planned_changes_do_not_write_anything(): void
    {
        $year = AcademicYear::factory()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        $term = Term::create([
            'academic_year_id' => $year->academic_year_id,
            'name' => 'Term 1',
            'code' => 'T1',
            'start_date' => '2026-01-05',
            'end_date' => '2026-04-03',
            'status' => 'active',
            'display_order' => 1,
        ]);

        $planned = $this->calendar->plannedStatusChanges($year->academic_year_id, Carbon::parse('2026-06-15'));

        $this->assertCount(1, $planned);
        $this->assertSame('completed', $planned[0]['to']);
        $this->assertSame('active', $term->fresh()->status, 'Planning must not write.');
    }

    // --------------------------------------------------------- single current

    public function test_only_one_academic_year_is_current_at_a_time(): void
    {
        $old = AcademicYear::factory()->current()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        $new = AcademicYear::factory()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
            'is_current' => true,
        ]);

        $this->actingAs($this->admin());

        $this->post(route('academic-years.store'), [])->assertSessionHasErrors();

        // Saving a year flagged current used to leave the old one current too.
        $this->calendar->makeCurrent($new);

        $this->assertTrue($new->fresh()->is_current);
        $this->assertFalse($old->fresh()->is_current);
        $this->assertSame(1, AcademicYear::where('is_current', true)->count());
    }

    public function test_storing_a_current_year_clears_the_flag_on_the_old_one(): void
    {
        $old = AcademicYear::factory()->current()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        $this->actingAs($this->admin());

        $this->post(route('academic-years.store'), [
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
            'is_current' => 1,
        ])->assertRedirect(route('academic-years.index'));

        $this->assertSame(1, AcademicYear::where('is_current', true)->count());
        $this->assertFalse($old->fresh()->is_current);
    }

    public function test_the_current_year_falls_back_to_dates_when_no_flag_is_set(): void
    {
        AcademicYear::factory()->archived()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        // A year whose dates actually contain today, but which was never flagged.
        $covering = AcademicYear::factory()->archived()->create([
            'name' => 'This Year',
            'start_date' => now()->startOfYear()->toDateString(),
            'end_date' => now()->endOfYear()->toDateString(),
        ]);

        // Nothing flagged current. Returning null here left callers with nothing
        // at all, which is how a school ended up with no usable year.
        $this->assertSame(
            $covering->academic_year_id,
            $this->calendar->currentYear()->academic_year_id
        );
    }

    // -------------------------------------------------------------- rollforward

    public function test_the_next_academic_year_can_be_set_up_in_one_step(): void
    {
        $current = AcademicYear::factory()->current()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        $this->calendar->createTermsFor($current);

        $this->actingAs($this->admin());

        $this->post(route('academic-years.roll-forward'))->assertRedirect(route('academic-years.index'));

        $next = AcademicYear::where('is_current', true)->firstOrFail();

        $this->assertNotSame($current->academic_year_id, $next->academic_year_id);
        $this->assertTrue($next->start_date->isAfter($current->end_date));
        $this->assertSame(3, $next->terms()->count(), 'The new year should arrive with its terms.');
        $this->assertFalse($current->fresh()->is_current);
    }

    public function test_each_roll_forward_advances_exactly_one_year(): void
    {
        $current = AcademicYear::factory()->current()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        $first = $this->calendar->rollForward();
        $second = $this->calendar->rollForward();

        // Each press is a deliberate, confirmed action to move on by one year, so
        // the calendar does advance again. What must hold every time is that the
        // year arrives complete and the previous one is stood down.
        $this->assertSame(3, AcademicYear::count());

        $this->assertTrue($first->start_date->isAfter($current->end_date));
        $this->assertTrue($second->start_date->isAfter($first->end_date));
        $this->assertSame(3, $second->terms()->count());
        $this->assertTrue($second->fresh()->is_current);
        $this->assertFalse($current->fresh()->is_current);
    }

    public function test_rolling_forward_adopts_a_later_year_that_already_exists(): void
    {
        $current = AcademicYear::factory()->current()->create([
            'name' => '2026',
            'start_date' => '2026-01-05',
            'end_date' => '2026-11-20',
        ]);

        // Somebody prepared the year ahead by hand, with no terms yet.
        $prepared = AcademicYear::factory()->archived()->create([
            'name' => '2027',
            'start_date' => '2027-01-04',
            'end_date' => '2027-12-10',
        ]);

        $result = $this->calendar->rollForward();

        // The existing year should be completed rather than bypassed.
        $this->assertSame($prepared->academic_year_id, $result->academic_year_id);
        $this->assertSame(3, $prepared->terms()->count());
        $this->assertSame(2, AcademicYear::count());
        $this->assertTrue($result->fresh()->is_current);
        $this->assertFalse($current->fresh()->is_current);
    }

    public function test_rolling_forward_from_nothing_still_creates_a_usable_year(): void
    {
        $year = $this->calendar->rollForward();

        $this->assertNotNull($year->academic_year_id);
        $this->assertSame(3, $year->terms()->count());
        $this->assertTrue($year->is_current);
    }

    // ------------------------------------------------------------------ wiring

    public function test_rolling_forward_is_not_reachable_without_permission(): void
    {
        $plain = User::factory()->create();
        $plain->assignRole('Parent');

        $this->actingAs($plain)
            ->post(route('academic-years.roll-forward'))
            ->assertForbidden();
    }

    public function test_the_index_shows_which_years_still_need_terms(): void
    {
        $bare = AcademicYear::factory()->archived()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        $this->actingAs($this->admin())
            ->get(route('academic-years.index'))
            ->assertOk()
            ->assertSee('No terms set up');
    }

    public function test_the_create_form_explains_that_terms_are_generated(): void
    {
        $this->actingAs($this->admin())
            ->get(route('academic-years.create'))
            ->assertOk()
            ->assertSee('Terms are created automatically');
    }

    public function test_the_create_form_says_when_a_previous_pattern_will_be_copied(): void
    {
        $previous = AcademicYear::factory()->archived()->create([
            'name' => '2025',
            'start_date' => '2025-01-06',
            'end_date' => '2025-12-05',
        ]);

        $this->calendar->createTermsFor($previous);

        $this->actingAs($this->admin())
            ->get(route('academic-years.create'))
            ->assertOk()
            ->assertSee('Terms are created automatically')
            ->assertSee('2025');
    }
}
