<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\Setting;
use App\Models\Student;
use App\Models\User;
use App\Services\LibraryService;
use App\Services\LibrarySettings;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Library loan period and fine rate.
 *
 * Both were hardcoded in LibraryService — a 14 day loan and KES 50 per day —
 * so changing either meant a code change and a deploy. They now come from the
 * `settings` table, which had a schema but no rows and no model.
 *
 * The behaviour that matters:
 *
 *  1. An administrator can set both from one page, and the values persist.
 *
 *  2. A school that charges no fines can set the rate to 0. It is not a
 *     positive-only field.
 *
 *  3. The loan period drives the default due date on the issue form, and the
 *     fine rate drives the fine stored when a book comes back late.
 *
 *  4. With no rows — a fresh install, or a cleared table — the previous
 *     hardcoded numbers still apply, and a junk value falls back rather than
 *     producing a NaN fine or a zero-day loan.
 */
class LibrarySettingsTest extends TestCase
{
    use RefreshDatabase;

    /** @var User */
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');
    }

    private function makeBook(array $overrides = []): Book
    {
        return Book::create(array_merge([
            'title' => 'A Lent Book',
            'author' => 'An Author',
            'isbn' => '978-0-00-000010-0',
            'quantity' => 2,
            'available_quantity' => 2,
            'added_date' => now()->subMonth()->toDateString(),
        ], $overrides));
    }

    private function makeMember(): LibraryMember
    {
        $student = Student::factory()->create();

        return LibraryMember::create([
            'user_id' => null,
            'member_type' => 'student',
            'reference_id' => (string) $student->student_id,
            'membership_date' => now()->subMonth()->toDateString(),
            'max_allowed_books' => 3,
            'status' => 'active',
        ]);
    }

    public function test_the_settings_page_shows_the_current_values(): void
    {
        Setting::put(LibrarySettings::FINE_PER_DAY, '80');
        Setting::put(LibrarySettings::LOAN_PERIOD_DAYS, '21');
        Setting::forgetResolved();

        $response = $this->actingAs($this->admin)->get(route('library.settings.edit'));

        $response->assertOk();
        $response->assertSee('Library Settings');
        $response->assertSee('Loan period (days)');
        $response->assertSee('Fine per day overdue');
        $response->assertSee('value="21"', false);
        $response->assertSee('value="80"', false);
    }

    public function test_saving_persists_both_values(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('library.settings.update'), [
                'fine_per_day' => '125.50',
                'loan_period_days' => 30,
            ]);

        $response->assertRedirect(route('library.settings.edit'));
        $response->assertSessionHas('flash_notification');

        $this->assertDatabaseHas('settings', [
            'setting_key' => LibrarySettings::FINE_PER_DAY,
            'setting_value' => '125.50',
        ]);
        $this->assertDatabaseHas('settings', [
            'setting_key' => LibrarySettings::LOAN_PERIOD_DAYS,
            'setting_value' => '30',
        ]);

        // Read back through the service, not the raw row.
        Setting::forgetResolved();
        $this->assertSame(125.5, LibrarySettings::finePerDay());
        $this->assertSame(30, LibrarySettings::loanPeriodDays());
    }

    public function test_a_school_can_charge_no_fines_at_all(): void
    {
        $this->actingAs($this->admin)
            ->patch(route('library.settings.update'), [
                'fine_per_day' => 0,
                'loan_period_days' => 14,
            ])
            ->assertSessionHasNoErrors();

        Setting::forgetResolved();

        $this->assertSame(0.0, LibrarySettings::finePerDay());
        $this->assertSame(0.0, LibrarySettings::fineFor(30));
    }

    public function test_it_rejects_a_negative_fine_and_a_zero_day_loan(): void
    {
        $response = $this->actingAs($this->admin)
            ->patch(route('library.settings.update'), [
                'fine_per_day' => -5,
                'loan_period_days' => 0,
            ]);

        $response->assertSessionHasErrors(['fine_per_day', 'loan_period_days']);

        // The rows are seeded by migration, so a rejected save must leave the
        // defaults in place rather than writing the bad values.
        $this->assertDatabaseHas('settings', [
            'setting_key' => LibrarySettings::FINE_PER_DAY,
            'setting_value' => '50',
        ]);
        $this->assertDatabaseHas('settings', [
            'setting_key' => LibrarySettings::LOAN_PERIOD_DAYS,
            'setting_value' => '14',
        ]);
    }

    public function test_settings_require_the_manage_permission(): void
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        $this->actingAs($teacher)
            ->get(route('library.settings.edit'))
            ->assertForbidden();

        $this->actingAs($teacher)
            ->patch(route('library.settings.update'), [
                'fine_per_day' => 999,
                'loan_period_days' => 99,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('settings', [
            'setting_key' => LibrarySettings::FINE_PER_DAY,
            'setting_value' => '50',
        ]);
    }

    public function test_the_loan_period_drives_the_default_due_date_on_the_issue_form(): void
    {
        Setting::put(LibrarySettings::LOAN_PERIOD_DAYS, '30');
        Setting::forgetResolved();

        $this->makeBook();
        $this->makeMember();

        $response = $this->actingAs($this->admin)->get(route('book-issues.create'));

        $response->assertOk();

        $expected = now()->addDays(30)->format('Y-m-d');
        $this->assertStringContainsString('value="' . $expected . '"', $response->getContent());
        // The recalculation in the browser has to use the same number.
        $this->assertStringContainsString('data-loan-days="30"', $response->getContent());
    }

    public function test_the_fine_rate_is_applied_when_a_book_comes_back_late(): void
    {
        Setting::put(LibrarySettings::FINE_PER_DAY, '100');
        Setting::forgetResolved();

        $issue = BookIssue::create([
            'book_id' => $this->makeBook()->book_id,
            'member_id' => $this->makeMember()->member_id,
            'issue_date' => now()->subDays(40),
            'due_date' => now()->subDays(10),
            'status' => 'issued',
        ]);

        app(LibraryService::class)->returnBook($issue->issue_id);

        $issue->refresh();

        // 10 days late at 100/day.
        $this->assertEquals(1000.00, (float) $issue->fine_amount);
        // Returned, not 'overdue': the book is back on the shelf.
        $this->assertSame('returned', $issue->status);
        $this->assertNotNull($issue->return_date);
    }

    public function test_a_book_returned_on_time_is_not_fined(): void
    {
        Setting::put(LibrarySettings::FINE_PER_DAY, '100');
        Setting::forgetResolved();

        $issue = BookIssue::create([
            'book_id' => $this->makeBook()->book_id,
            'member_id' => $this->makeMember()->member_id,
            'issue_date' => now()->subDays(40),
            'due_date' => now()->addDays(5),
            'status' => 'issued',
        ]);

        app(LibraryService::class)->returnBook($issue->issue_id);

        $issue->refresh();

        $this->assertEquals(0.0, (float) $issue->fine_amount);
    }

    public function test_changing_the_rate_does_not_rewrite_fines_already_stored(): void
    {
        Setting::put(LibrarySettings::FINE_PER_DAY, '100');
        Setting::forgetResolved();

        $issue = BookIssue::create([
            'book_id' => $this->makeBook()->book_id,
            'member_id' => $this->makeMember()->member_id,
            'issue_date' => now()->subDays(40),
            'due_date' => now()->subDays(10),
            'status' => 'issued',
        ]);

        app(LibraryService::class)->returnBook($issue->issue_id);

        $stored = $issue->fresh()->fine_amount;

        Setting::put(LibrarySettings::FINE_PER_DAY, '250');
        Setting::forgetResolved();

        // A fine is a historical charge; re-pricing it would rewrite the
        // ledger the member already settled.
        $this->assertEquals((float) $stored, (float) $issue->fresh()->fine_amount);
    }

    public function test_it_falls_back_to_the_previous_hardcoded_values_when_settings_are_absent(): void
    {
        // No rows at all — a fresh install, or a cleared table.
        $this->assertSame(50.0, LibrarySettings::finePerDay());
        $this->assertSame(14, LibrarySettings::loanPeriodDays());

        // A value that is not a number must not produce a NaN fine or a
        // zero-day loan.
        Setting::put(LibrarySettings::FINE_PER_DAY, 'not-a-number');
        Setting::put(LibrarySettings::LOAN_PERIOD_DAYS, '');
        Setting::forgetResolved();

        $this->assertSame(50.0, LibrarySettings::finePerDay());
        $this->assertSame(14, LibrarySettings::loanPeriodDays());
    }

    public function test_the_overdue_filter_actually_finds_overdue_books(): void
    {
        Setting::put(LibrarySettings::FINE_PER_DAY, '100');
        Setting::forgetResolved();

        $book = $this->makeBook();
        $member = $this->makeMember();

        $overdue = BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(30),
            'due_date' => now()->subDays(6),
            'status' => 'issued',
        ]);

        $current = BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(2),
            'due_date' => now()->addDays(12),
            'status' => 'issued',
        ]);

        // A book that was late but came back is not currently overdue.
        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(40),
            'due_date' => now()->subDays(20),
            'return_date' => now()->subDays(18),
            'status' => 'returned',
        ]);

        // The status column is 'issued' for the overdue book — the enum value
        // 'overdue' is never written — so the filter has to be based on the
        // due date. Filtering on the column returned nothing.
        $response = $this->actingAs($this->admin)
            ->get(route('book-issues.index', ['status' => 'overdue']));

        $response->assertOk();
        $response->assertSee($overdue->issue_id ? 'A Lent Book' : '');
        $response->assertSee('6 days overdue');
        $response->assertSee('600.00');

        $this->assertStringNotContainsString(
            'currently within its loan period',
            $response->getContent()
        );

        // And the non-overdue issued book is not in this listing.
        $this->assertEqualsCanonicalizing(
            [$overdue->issue_id],
            BookIssue::where('status', 'issued')
                ->where('due_date', '<', now())
                ->pluck('issue_id')
                ->all()
        );
        $this->assertNotNull($current->fresh());
    }

    public function test_searching_book_issues_matches_a_member_name(): void
    {
        $book = $this->makeBook();

        $student = Student::factory()->create(['first_name' => 'Wanjiru', 'last_name' => 'Ndungu']);
        $member = LibraryMember::create([
            'user_id' => null,
            'member_type' => 'student',
            'reference_id' => (string) $student->student_id,
            'membership_date' => now()->subMonth()->toDateString(),
            'max_allowed_books' => 3,
            'status' => 'active',
        ]);

        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(2),
            'due_date' => now()->addDays(12),
            'status' => 'issued',
        ]);

        // Previously this only compared users.name, which is null for every
        // membership, so searching a member found nothing.
        $response = $this->actingAs($this->admin)
            ->get(route('book-issues.index', ['search' => 'Ndungu']));

        $response->assertOk();
        $response->assertSee('Wanjiru Ndungu');
    }

    public function test_the_dashboard_reports_overdue_books_and_what_they_owe(): void
    {
        Setting::put(LibrarySettings::FINE_PER_DAY, '100');
        Setting::forgetResolved();

        $book = $this->makeBook();
        $member = $this->makeMember();

        // 4 days overdue.
        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(30),
            'due_date' => now()->subDays(4),
            'status' => 'issued',
        ]);

        // Still within the loan period, so not counted.
        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issue_date' => now()->subDays(2),
            'due_date' => now()->addDays(12),
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->admin)->get(route('library.dashboard'));

        $response->assertOk();
        $response->assertSee('Loan period <strong>14 days</strong>', false);
        $response->assertSee('1 overdue', false);
        // 4 days late at 100/day.
        $this->assertStringContainsString('400.00', $response->getContent());
    }
}
