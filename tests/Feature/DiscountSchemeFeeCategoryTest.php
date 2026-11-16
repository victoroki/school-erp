<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\DiscountScheme;
use App\Models\FeeCategory;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The applicable fee categories on the discount scheme form, as checkboxes.
 *
 * It was a select2 multi-select bound with a null selected value, which had two
 * consequences a single-select never has: the edit form always rendered it
 * empty, so a stored selection was invisible and, because an unticked
 * multi-select drops the key from the payload entirely, it could never be
 * cleared once set. Boxes fix the first problem by carrying their own state;
 * the marker input the partial submits alongside them is what makes "ticked
 * none" a storable answer rather than an absent key.
 */
class DiscountSchemeFeeCategoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected AcademicYear $year;

    /** @var list<FeeCategory> */
    protected array $categories = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'discount-categories@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        foreach (['Tuition', 'Transport', 'Laboratory'] as $name) {
            $this->categories[] = FeeCategory::create(['name' => $name . ' ' . uniqid()]);
        }
    }

    private function scheme(array $attributes = []): DiscountScheme
    {
        return DiscountScheme::create(array_merge([
            'name' => 'Sibling Discount',
            'code' => 'SIB-' . substr(uniqid(), -5),
            'type' => 'percentage',
            'value' => 10,
            'applies_to' => 'specific_categories',
            'eligibility_criteria' => 'sibling',
            'status' => 'active',
            'academic_year_id' => $this->year->academic_year_id,
        ], $attributes));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Sibling Discount',
            'type' => 'percentage',
            'value' => 10,
            'applies_to' => 'specific_categories',
            'eligibility_criteria' => 'sibling',
            'status' => 'active',
            'applicable_fee_categories_submitted' => 1,
        ], $overrides);
    }

    /**
     * The input rendered for one category, matched on its value so the
     * assertions do not depend on attribute order.
     */
    private function checkboxFor(string $html, int $categoryId): string
    {
        preg_match_all('/<input[^>]*name="applicable_fee_categories\[\]"[^>]*>/', $html, $matches);

        foreach ($matches[0] as $tag) {
            if (str_contains($tag, 'value="' . $categoryId . '"')) {
                return $tag;
            }
        }

        return '';
    }

    public function test_the_create_form_offers_a_checkbox_per_fee_category(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('fees.discounts.create'))
            ->assertOk()
            ->getContent();

        foreach ($this->categories as $category) {
            $this->assertNotSame(
                '',
                $this->checkboxFor($html, $category->category_id),
                'No checkbox was rendered for the ' . $category->name . ' category.'
            );
        }

        // The multi-select it replaced is gone, along with the select2
        // initialiser that would otherwise have nothing to attach to.
        $this->assertStringNotContainsString('id="applicable_fee_categories"', $html);
        $this->assertSame(
            0,
            preg_match('/<select[^>]*applicable_fee_categories/', $html),
            'The category field is still a <select>, not a set of checkboxes.'
        );
    }

    public function test_the_edit_form_ticks_the_stored_categories(): void
    {
        $scheme = $this->scheme([
            'applicable_fee_categories' => [
                $this->categories[0]->category_id,
                $this->categories[2]->category_id,
            ],
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('fees.discounts.edit', $scheme->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('checked', $this->checkboxFor($html, $this->categories[0]->category_id));
        $this->assertStringContainsString('checked', $this->checkboxFor($html, $this->categories[2]->category_id));
        $this->assertStringNotContainsString('checked', $this->checkboxFor($html, $this->categories[1]->category_id));
    }

    public function test_the_ticked_categories_are_stored(): void
    {
        $ticked = [$this->categories[0]->category_id, $this->categories[1]->category_id];

        $this->actingAs($this->admin)
            ->post(route('fees.discounts.store'), $this->payload([
                'applicable_fee_categories' => $ticked,
            ]))
            ->assertRedirect(route('fees.discounts.index'));

        $stored = DiscountScheme::latest('id')->first();

        $this->assertSame($ticked, $stored->applicable_fee_categories);
    }

    /**
     * The regression the marker input exists for: a request that ticks nothing
     * carries no `applicable_fee_categories` key at all, and without the marker
     * that is indistinguishable from a request that never had the field, so the
     * stored selection survives the save it was meant to replace.
     */
    public function test_unticking_every_category_clears_the_stored_list(): void
    {
        $scheme = $this->scheme([
            'applicable_fee_categories' => [$this->categories[0]->category_id, $this->categories[1]->category_id],
        ]);

        $this->actingAs($this->admin)
            ->put(route('fees.discounts.update', $scheme->id), $this->payload([
                'code' => $scheme->code,
            ]))
            ->assertRedirect(route('fees.discounts.index'));

        $this->assertSame([], $scheme->fresh()->applicable_fee_categories);
    }

    public function test_a_category_that_does_not_exist_is_rejected(): void
    {
        $this->actingAs($this->admin)
            ->post(route('fees.discounts.store'), $this->payload([
                'applicable_fee_categories' => [$this->categories[0]->category_id, 999999],
            ]))
            ->assertSessionHasErrors('applicable_fee_categories.1');

        $this->assertSame(0, DiscountScheme::count());
    }

    public function test_a_rejected_form_shows_the_categories_that_were_ticked(): void
    {
        $ticked = [$this->categories[1]->category_id];

        $html = $this->actingAs($this->admin)
            ->withSession(['_old_input' => $this->payload(['applicable_fee_categories' => $ticked])])
            ->get(route('fees.discounts.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('checked', $this->checkboxFor($html, $this->categories[1]->category_id));
        $this->assertStringNotContainsString('checked', $this->checkboxFor($html, $this->categories[0]->category_id));
    }

    /**
     * The other half of that: a rejected submission that ticked nothing must
     * not fall back to the model's stored categories, or unticking a box would
     * silently re-tick it the moment another field was wrong.
     */
    public function test_a_rejected_form_does_not_fall_back_to_the_stored_categories(): void
    {
        $scheme = $this->scheme([
            'applicable_fee_categories' => [
                $this->categories[0]->category_id,
                $this->categories[1]->category_id,
            ],
        ]);

        $html = $this->actingAs($this->admin)
            ->withSession([
                '_old_input' => $this->payload(['code' => $scheme->code]),
            ])
            ->get(route('fees.discounts.edit', $scheme->id))
            ->assertOk()
            ->getContent();

        foreach ($this->categories as $category) {
            $this->assertStringNotContainsString(
                'checked',
                $this->checkboxFor($html, $category->category_id),
                'A rejected submission that ticked nothing re-ticked ' . $category->name . ' from the stored value.'
            );
        }
    }

    /**
     * The show page printed the stored ids as tags, which reads as "3" rather
     * than as a category an administrator recognises.
     */
    public function test_the_show_page_names_the_categories(): void
    {
        $scheme = $this->scheme([
            'applicable_fee_categories' => [$this->categories[0]->category_id],
        ]);

        $html = $this->actingAs($this->admin)
            ->get(route('fees.discounts.show', $scheme->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(e($this->categories[0]->name), $html);
        $this->assertStringNotContainsString('>' . $this->categories[0]->category_id . '<', $html);
    }
}
