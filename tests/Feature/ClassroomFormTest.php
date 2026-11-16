<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Classroom create/edit form.
 *
 * Regression coverage for the required-field asterisks rendering as literal
 * text ("<span class="text-danger">*</span> Room Number:") on the Create
 * Classrooms page. The bundled laravelcollective/html escapes the label value
 * unless the 4th argument is false, and `{!! !!}` cannot undo that because
 * the escaping happens inside FormBuilder::label().
 */
class ClassroomFormTest extends TestCase
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

    public function test_required_markers_render_as_markup_not_literal_text()
    {
        $response = $this->actingAs($this->admin)->get(route('classrooms.create'));

        $response->assertStatus(200);
        $response->assertDontSee('&lt;span', false);

        $response->assertSee(
            '<label for="room_number"><span class="text-danger">*</span> Room Number:</label>',
            false
        );
        $response->assertSee(
            '<label for="capacity"><span class="text-danger">*</span> Capacity:</label>',
            false
        );
    }

    public function test_create_page_exposes_every_documented_field()
    {
        $response = $this->actingAs($this->admin)->get(route('classrooms.create'));

        foreach ([
            'room_number',
            'building',
            'floor',
            'capacity',
            'has_sockets',
            'has_whiteboard',
        ] as $field) {
            $response->assertSee('name="' . $field . '"', false);
        }
    }

    public function test_store_creates_a_classroom()
    {
        $response = $this->actingAs($this->admin)->post(route('classrooms.store'), [
            'room_number' => 'R-101',
            'building' => 'Block A',
            'floor' => 1,
            'capacity' => 40,
            'has_sockets' => 1,
            'has_whiteboard' => 0,
        ]);

        $response->assertRedirect(route('classrooms.index'));

        $this->assertDatabaseHas('classrooms', ['room_number' => 'R-101', 'capacity' => 40]);

        $classroom = Classroom::where('room_number', 'R-101')->firstOrFail();
        $this->assertTrue((bool) $classroom->has_sockets);
        $this->assertFalse((bool) $classroom->has_whiteboard);
    }

    public function test_store_rejects_a_duplicate_room_number()
    {
        Classroom::create(['room_number' => 'R-101', 'capacity' => 40]);

        $this->actingAs($this->admin)
            ->post(route('classrooms.store'), [
                'room_number' => 'R-101',
                'capacity' => 30,
            ])
            ->assertSessionHasErrors('room_number');

        $this->assertSame(1, Classroom::where('room_number', 'R-101')->count());
    }
}
