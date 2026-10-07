<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Library member names.
 *
 * Every screen that names a member used to read `$member->user->name`, but
 * `library_members.user_id` is nullable and is empty for every membership
 * created from a student or staff record — which is how all of them are made.
 * So the book issue form offered 36 identical "Unknown (N)" options, the
 * dashboard and member tables showed "N/A", and the members search filtered
 * on a `name` column that never had a value. A member's identity actually
 * lives in `member_type` + `reference_id`, so the name has to be resolved
 * from the student/staff record.
 *
 * These cover:
 *
 *  1. A member with no user_id still resolves a real name and admission no.
 *  2. The book issue form offers those names, sorted, instead of "Unknown".
 *  3. Staff members resolve through the staff table, and members with a linked
 *     login still work.
 *  4. A reference that points nowhere is labelled as unlinked rather than
 *     silently blank.
 *  5. Searching members matches a student's name, not just a login.
 */
class LibraryMemberNameTest extends TestCase
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

    private function memberFor(Student $student, ?string $type = 'student'): LibraryMember
    {
        return LibraryMember::create([
            'user_id' => null,
            'member_type' => $type,
            'reference_id' => (string) $student->student_id,
            'membership_date' => now()->subMonth()->toDateString(),
            'max_allowed_books' => 2,
            'status' => 'active',
        ]);
    }

    public function test_it_resolves_a_name_when_the_member_has_no_linked_user(): void
    {
        $student = Student::factory()->create([
            'first_name' => 'Wanjiku',
            'middle_name' => 'Njeri',
            'last_name' => 'Kamau',
            'admission_no' => 'ADM2026/001',
        ]);

        $member = $this->memberFor($student);

        // The premise of the bug: no login is attached.
        $this->assertNull($member->user_id);

        $this->assertSame('Wanjiku Njeri Kamau', $member->person_name);
        $this->assertSame('Wanjiku Njeri Kamau (ADM2026/001)', $member->display_name);
        $this->assertStringNotContainsString('Unknown', $member->display_name);
    }

    public function test_the_book_issue_form_offers_resolvable_names(): void
    {
        $zebra = Student::factory()->create(['first_name' => 'Zuri', 'last_name' => 'Zebra', 'admission_no' => 'ADM-Z']);
        $alpha = Student::factory()->create(['first_name' => 'Amara', 'last_name' => 'Asante', 'admission_no' => 'ADM-A']);

        $this->memberFor($zebra);
        $this->memberFor($alpha);

        Book::create([
            'title' => 'Things To Do', 'author' => 'A. Writer', 'isbn' => '978-0-00-000001-1',
            'quantity' => 3, 'available_quantity' => 3, 'added_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('book-issues.create'));

        $response->assertOk();
        $response->assertSee('Amara Asante (ADM-A)');
        $response->assertSee('Zuri Zebra (ADM-Z)');
        $response->assertDontSee('Unknown');

        // Sorted, so the first member offered is the alphabetical first.
        $html = $response->getContent();
        $this->assertLessThan(
            strpos($html, 'Zuri Zebra (ADM-Z)'),
            strpos($html, 'Amara Asante (ADM-A)'),
            'Member options should be sorted by name.'
        );
    }

    public function test_a_staff_member_resolves_through_the_staff_table(): void
    {
        $staff = new \App\Models\Staff();
        $staff->forceFill([
            'first_name' => 'Njeri',
            'last_name' => 'Mwangi',
            'employee_number' => 'EMP-77',
            'date_of_birth' => '1985-04-02',
            'gender' => 'female',
            'date_of_joining' => '2015-01-05',
            'work_email' => 'njeri.mwangi@example.test',
            'phone_primary' => '0700000001',
            'current_address' => 'Nairobi',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'staff_type' => 'teaching',
        ])->save();

        $member = LibraryMember::create([
            'user_id' => null,
            'member_type' => 'staff',
            'reference_id' => (string) $staff->staff_id,
            'membership_date' => now()->subMonth()->toDateString(),
            'max_allowed_books' => 3,
            'status' => 'active',
        ]);

        $this->assertSame('Njeri Mwangi (EMP-77)', $member->display_name);
    }

    public function test_a_linked_login_still_resolves_when_there_is_no_person_record(): void
    {
        $user = User::factory()->create(['name' => 'Library Desk']);

        $member = LibraryMember::create([
            'user_id' => $user->id,
            'member_type' => 'staff',
            'reference_id' => '999999', // no such staff record
            'membership_date' => now()->subMonth()->toDateString(),
            'max_allowed_books' => 2,
            'status' => 'active',
        ]);

        // Falls back to the login rather than rendering nothing.
        $this->assertSame('Library Desk (999999)', $member->display_name);
    }

    public function test_an_unresolvable_reference_is_labelled_instead_of_being_blank(): void
    {
        $member = LibraryMember::create([
            'user_id' => null,
            'member_type' => 'student',
            'reference_id' => '424242',
            'membership_date' => now()->subMonth()->toDateString(),
            'max_allowed_books' => 2,
            'status' => 'active',
        ]);

        $this->assertSame('Unlinked Student #424242', $member->display_name);
    }

    public function test_member_search_matches_a_student_name(): void
    {
        $match = Student::factory()->create(['first_name' => 'Wanjiru', 'last_name' => 'Ndungu']);
        $other = Student::factory()->create(['first_name' => 'Brian', 'last_name' => 'Otieno']);

        $this->memberFor($match);
        $this->memberFor($other);

        // Previously this hit a non-existent `membership_number` column and
        // only ever compared users.name, which is null for these members.
        $response = $this->withoutExceptionHandling()
            ->actingAs($this->admin)
            ->get(route('library-members.index', ['search' => 'Ndungu']));

        $response->assertOk();
        $response->assertSee('Wanjiru Ndungu');
        $response->assertDontSee('Brian Otieno');
    }

    public function test_the_dashboard_names_members_instead_of_n_a(): void
    {
        $student = Student::factory()->create(['first_name' => 'Achieng', 'last_name' => 'Omondi']);
        $member = $this->memberFor($student);

        $book = Book::create([
            'title' => 'Dated Volume', 'author' => 'Old Author', 'isbn' => '978-0-00-000009-9',
            'quantity' => 1, 'available_quantity' => 0, 'added_date' => now()->subYear()->toDateString(),
        ]);

        BookIssue::create([
            'book_id' => $book->book_id,
            'member_id' => $member->member_id,
            'issuer_id' => $this->admin->id,
            'issue_date' => now()->subMonth(),
            'due_date' => now()->subDays(2),
            'status' => 'issued',
        ]);

        $response = $this->actingAs($this->admin)->get(route('library.dashboard'));

        $response->assertOk();
        $response->assertSee('Achieng Omondi');
    }
}
