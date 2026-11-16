<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookCategory;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Filters must survive pagination.
 *
 * 26 web list screens paginated a filtered query without withQueryString(), so
 * page 2 was built from the route alone and every filter was dropped — a user
 * who filtered to one class and clicked "next" silently saw the whole school.
 * A handful more already preserved the query string via ->appends(); those were
 * false positives when the scans only looked for withQueryString().
 *
 * These tests assert the actual page-2 link rather than the presence of a string
 * anywhere on the page, so they cannot pass on the filter form re-rendering its
 * own values.
 */
class PaginationFilterPreservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'pagination@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');
    }

    /**
     * Every href in the response that points at the given page.
     *
     * @return list<string>
     */
    private function linksToPage(string $html, int $page): array
    {
        preg_match_all('/href="([^"]*[?&](?:amp;)?page=' . $page . '(?:&|"|$)[^"]*)"/', $html, $matches);

        return $matches[1];
    }

    public function test_book_index_keeps_the_search_filter_in_pagination_links(): void
    {
        $category = BookCategory::create(['name' => 'Fiction-' . uniqid()]);

        // 15 matches at 12 per page → a second page exists, so links render.
        for ($i = 1; $i <= 15; $i++) {
            Book::create([
                'title' => 'Zebrafish Studies ' . $i,
                'author' => 'A Author',
                'category_id' => $category->category_id,
                'quantity' => 1,
                'available_quantity' => 1,
                'added_date' => now()->toDateString(),
            ]);
        }

        $response = $this->actingAs($this->admin)
            ->get(route('books.index', ['search' => 'Zebrafish']))
            ->assertOk();

        $pageTwo = $this->linksToPage($response->getContent(), 2);

        $this->assertNotEmpty($pageTwo, 'Expected a page-2 pagination link.');

        $this->assertTrue(
            collect($pageTwo)->contains(fn ($url) => str_contains($url, 'search=Zebrafish')),
            'The page-2 link dropped the search filter. Links were: ' . implode(' | ', $pageTwo)
        );

        // NOTE: this screen was already covered — books/index.blade.php calls
        // $books->appends(request()->query())->links(), so this test passes
        // against the unfixed controller too. It is a regression guard, not
        // evidence for the controller change, and must not be counted as such.
        //
        // It does assert a real property: the controller's withQueryString() and
        // the view's appends() must not double the parameter.
        foreach ($pageTwo as $url) {
            $this->assertSame(
                1,
                substr_count($url, 'search='),
                "The filter was duplicated in the pagination link: {$url}"
            );
        }
    }

    public function test_supplier_index_keeps_the_status_filter_in_pagination_links(): void
    {
        // 13 active at 12 per page → a second page exists.
        for ($i = 1; $i <= 13; $i++) {
            Supplier::create([
                'name' => 'Active Supplier ' . $i,
                'phone' => '07000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'is_active' => true,
            ]);
        }

        Supplier::create([
            'name' => 'Dormant Supplier',
            'phone' => '0799999999',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('suppliers.index', ['status' => 'active']))
            ->assertOk();

        $pageTwo = $this->linksToPage($response->getContent(), 2);

        $this->assertNotEmpty($pageTwo, 'Expected a page-2 pagination link.');

        $this->assertTrue(
            collect($pageTwo)->contains(fn ($url) => str_contains($url, 'status=active')),
            'The page-2 link dropped the status filter. Links were: ' . implode(' | ', $pageTwo)
        );

        foreach ($pageTwo as $url) {
            $this->assertSame(1, substr_count($url, 'status='), "Duplicated filter in link: {$url}");
        }

        // And the filter itself must still be honoured on that page.
        $second = $this->actingAs($this->admin)
            ->get(route('suppliers.index', ['status' => 'active', 'page' => 2]))
            ->assertOk();

        $second->assertDontSee('Dormant Supplier');
        $second->assertSee('Active Supplier 13');
    }
}
