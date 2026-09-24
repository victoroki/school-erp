<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The class form used to render `Form::hidden('numeric_value', 0)`.
 *
 * Laravel Collective's FormBuilder::getValueAttribute() returns an explicitly
 * passed value before it ever consults the bound model, so that hidden input
 * always posted 0 — meaning any ordinary edit silently reset the class level,
 * and subjects could never be matched to a grade level.
 */
class SchoolClassLevelPreservationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $this->seed(RbacSeeder::class);

        $this->admin = User::factory()->create(['email' => 'class-level-admin@test.local']);
        $this->admin->roles()->sync([Role::where('role_name', 'Super Admin')->firstOrFail()->role_id]);
        $this->admin->load('roles');
    }

    private function levelInputTag(string $html): string
    {
        preg_match('/<input[^>]*name="numeric_value"[^>]*>/', $html, $matches);

        return $matches[0] ?? '';
    }

    /**
     * The value attribute the level input would submit, or null when absent.
     */
    private function levelInputValue(string $html): ?string
    {
        $tag = $this->levelInputTag($html);

        if ($tag === '') {
            return null;
        }

        preg_match('/value="([^"]*)"/', $tag, $value);

        return $value[1] ?? null;
    }

    public function test_edit_form_prefills_the_stored_level(): void
    {
        $class = SchoolClass::create(['name' => 'Grade 3', 'numeric_value' => 3, 'description' => 'x']);

        $html = $this->actingAs($this->admin)
            ->get(route('school-classes.edit', $class->class_id))
            ->assertOk()
            ->getContent();

        $this->assertSame(
            '3',
            $this->levelInputValue($html),
            'The edit form must render the class level that is actually stored.'
        );
    }

    public function test_editing_a_class_does_not_reset_its_level(): void
    {
        $class = SchoolClass::create(['name' => 'Grade 5', 'numeric_value' => 5, 'description' => 'original']);

        $html = $this->actingAs($this->admin)
            ->get(route('school-classes.edit', $class->class_id))
            ->assertOk()
            ->getContent();

        // Submit exactly what the browser would submit from the rendered form.
        $this->actingAs($this->admin)
            ->put(route('school-classes.update', $class->class_id), [
                'name' => $class->name,
                'numeric_value' => $this->levelInputValue($html),
                'description' => 'edited description only',
            ])
            ->assertRedirect(route('school-classes.index'));

        $fresh = $class->fresh();

        $this->assertSame(5, (int) $fresh->numeric_value, 'An ordinary edit must not zero the class level.');
        $this->assertSame('edited description only', $fresh->description);
    }

    public function test_create_form_renders_a_real_field_not_a_hidden_zero(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('school-classes.create'))
            ->assertOk()
            ->getContent();

        $tag = $this->levelInputTag($html);

        $this->assertNotSame('', $tag, 'The class form must render a numeric_value input.');
        $this->assertStringContainsString('type="number"', $tag, 'The level must be a real editable field.');

        $value = $this->levelInputValue($html);
        $this->assertTrue(
            $value === null || $value === '',
            'A new class must not pre-fill the level; got ' . var_export($value, true)
        );
    }

    public function test_a_class_can_be_created_with_a_level(): void
    {
        $this->actingAs($this->admin)
            ->post(route('school-classes.store'), [
                'name' => 'Grade 7',
                'numeric_value' => 7,
                'description' => 'Junior secondary',
            ])
            ->assertRedirect(route('school-classes.index'));

        $this->assertSame(7, (int) SchoolClass::where('name', 'Grade 7')->firstOrFail()->numeric_value);
    }

    public function test_level_is_validated(): void
    {
        $this->actingAs($this->admin)
            ->post(route('school-classes.store'), [
                'name' => 'Impossible Level',
                'numeric_value' => 250,
            ])
            ->assertSessionHasErrors('numeric_value');
    }
}
