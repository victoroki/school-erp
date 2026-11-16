<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\DiscountScheme;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Discount scheme create/edit field parity.
 *
 * create.blade.php rendered five fields that edit.blade.php did not:
 * academic_year_id, valid_from, valid_to, requires_approval and auto_apply.
 * The show page displayed all five, so a scheme could be created with a validity
 * window and an approval requirement that the edit form could show the value of
 * but never change. The fields now live in the shared fields partial, which both
 * forms include, so they cannot drift apart again.
 *
 * Separately, an unticked checkbox is absent from the request, so the previous
 * boolean survived an edit: the flags could be turned on and never off.
 */
class DiscountSchemeFormParityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected DiscountScheme $scheme;

    protected AcademicYear $year;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'discount-parity@test.local']);
        $this->admin->roles()->sync(Role::where('role_name', 'Super Admin')->pluck('role_id'));
        $this->admin->load('roles.permissions');

        $this->year = AcademicYear::create([
            'name' => 'AY-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $this->scheme = DiscountScheme::create([
            'name' => 'Sibling Discount',
            'code' => 'SIB-' . substr(uniqid(), -5),
            'type' => 'percentage',
            'value' => 10,
            'applies_to' => 'all_fees',
            'eligibility_criteria' => 'sibling',
            'status' => 'active',
            'academic_year_id' => $this->year->academic_year_id,
            'valid_from' => '2026-01-05',
            'valid_to' => '2026-04-03',
            'requires_approval' => true,
            'auto_apply' => true,
        ]);
    }

    /**
     * Field names a form must expose, read from the rendered HTML.
     *
     * @return list<string>
     */
    private function renderedFieldNames(string $html): array
    {
        preg_match_all('/name="([a-z_]+)(?:\[\])?"/', $html, $matches);

        // _method and _token are emitted by Form::open/Form::model, not declared
        // by these forms. The edit form uses PATCH, so it emits _method and the
        // create form does not — a framework artefact, not field drift.
        $names = array_diff($matches[1], ['_method', '_token']);

        return array_values(array_unique($names));
    }

    /**
     * The whole opening tag of the input rendered for $name, so assertions do not
     * depend on the order Laravel Collective happens to emit attributes in.
     */
    private function inputTagFor(string $html, string $name): string
    {
        preg_match('/<input[^>]*name="' . preg_quote($name, '/') . '"[^>]*>/', $html, $match);

        return $match[0] ?? '';
    }

    public function test_edit_form_renders_the_same_fields_as_create(): void
    {
        $create = $this->actingAs($this->admin)->get(route('fees.discounts.create'))->assertOk()->getContent();
        $edit = $this->actingAs($this->admin)->get(route('fees.discounts.edit', $this->scheme->id))->assertOk()->getContent();

        $createFields = $this->renderedFieldNames($create);
        $editFields = $this->renderedFieldNames($edit);

        // The five fields that only create used to render.
        foreach (['academic_year_id', 'valid_from', 'valid_to', 'requires_approval', 'auto_apply'] as $field) {
            $this->assertContains($field, $createFields, "create is missing {$field}");
            $this->assertContains($field, $editFields, "edit is missing {$field}");
        }

        // Parity in both directions: neither form may expose a field the other hides.
        $this->assertSame([], array_values(array_diff($createFields, $editFields)), 'create renders fields edit does not');
        $this->assertSame([], array_values(array_diff($editFields, $createFields)), 'edit renders fields create does not');
    }

    public function test_edit_form_shows_the_stored_values_for_those_fields(): void
    {
        $edit = $this->actingAs($this->admin)
            ->get(route('fees.discounts.edit', $this->scheme->id))
            ->assertOk()
            ->getContent();

        // The bound model values must be present, not blank inputs.
        $this->assertStringContainsString('2026-01-05', $edit);
        $this->assertStringContainsString('2026-04-03', $edit);

        foreach (['requires_approval', 'auto_apply'] as $flag) {
            $tag = $this->inputTagFor($edit, $flag);

            $this->assertNotSame('', $tag, "The {$flag} checkbox was not rendered on the edit form.");
            $this->assertStringContainsString('checked', $tag, "The {$flag} checkbox should be ticked for this scheme.");
        }
    }

    public function test_a_flag_can_be_turned_off(): void
    {
        // An unticked checkbox is absent from the payload entirely.
        $this->actingAs($this->admin)
            ->put(route('fees.discounts.update', $this->scheme->id), [
                'name' => 'Sibling Discount',
                'code' => $this->scheme->code,
                'type' => 'percentage',
                'value' => 10,
                'applies_to' => 'all_fees',
                'eligibility_criteria' => 'sibling',
                'status' => 'active',
                'academic_year_id' => $this->year->academic_year_id,
                'valid_from' => '2026-01-05',
                'valid_to' => '2026-04-03',
            ])
            ->assertRedirect(route('fees.discounts.index'));

        $fresh = $this->scheme->fresh();

        $this->assertFalse((bool) $fresh->requires_approval, 'requires_approval must be cleared when unticked');
        $this->assertFalse((bool) $fresh->auto_apply, 'auto_apply must be cleared when unticked');

        // The editable fields must survive the same submit.
        $this->assertSame('2026-01-05', $fresh->valid_from?->toDateString());
        $this->assertSame('2026-04-03', $fresh->valid_to?->toDateString());
    }

    public function test_a_flag_can_be_turned_on(): void
    {
        $this->scheme->update(['requires_approval' => false, 'auto_apply' => false]);

        $this->actingAs($this->admin)
            ->put(route('fees.discounts.update', $this->scheme->id), [
                'name' => 'Sibling Discount',
                'code' => $this->scheme->code,
                'type' => 'percentage',
                'value' => 10,
                'applies_to' => 'all_fees',
                'eligibility_criteria' => 'sibling',
                'status' => 'active',
                'academic_year_id' => $this->year->academic_year_id,
                'requires_approval' => 1,
                'auto_apply' => 1,
            ])
            ->assertRedirect(route('fees.discounts.index'));

        $fresh = $this->scheme->fresh();

        $this->assertTrue((bool) $fresh->requires_approval);
        $this->assertTrue((bool) $fresh->auto_apply);
    }
}
