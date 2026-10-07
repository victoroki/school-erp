<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Staff;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Staff create form: the restructured page renders, and the fields it collects
 * are actually persisted (validated() previously dropped staff_type, user_id,
 * qualification, experience, current_address, city and country).
 */
class StaffCreateFormTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->department = new Department(['name' => 'Mathematics', 'description' => 'STEM']);
        $this->department->save();
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'employee_number' => 'T-2001',
            'first_name' => 'Amina',
            'last_name' => 'Wanjiru',
            'date_of_birth' => '1990-04-17',
            'date_of_joining' => '2024-01-08',
            'gender' => 'female',
            'staff_type' => 'teaching',
            'department_id' => $this->department->department_id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'work_email' => 'amina.wanjiru@school.org',
            'phone_primary' => '0712345678',
        ], $overrides);
    }

    public function test_create_page_renders_with_grouped_sections(): void
    {
        $response = $this->actingAs($this->admin)->get(route('staff.create'));

        $response->assertStatus(200);
        $response->assertSee('Create Staff');
        $response->assertSee('Identity');
        $response->assertSee('Employment');
        $response->assertSee('Contact');
        $response->assertSee('Back to Staff');
        // The photo field is useless without a multipart form.
        $response->assertSee('multipart/form-data', false);
        // The old AdminLTE scaffold footer is gone.
        $response->assertDontSee('value="Save"', false);
    }

    public function test_create_page_shows_validation_errors_with_anchor_links(): void
    {
        $response = $this->actingAs($this->admin)
            ->from(route('staff.create'))
            ->post(route('staff.store'), []);

        $response->assertRedirect(route('staff.create'));
        $response->assertSessionHasErrors(['first_name', 'last_name', 'work_email', 'staff_type', 'date_of_joining']);

        $page = $this->actingAs($this->admin)->get(route('staff.create'));
        $page->assertStatus(200);
        $page->assertSee('Fix these before saving');
        $page->assertSee('href="#first_name"', false);
    }

    public function test_edit_page_still_renders_the_shared_fields(): void
    {
        $staff = new Staff([
            'employee_number' => 'T-3001',
            'first_name' => 'Peter',
            'last_name' => 'Otieno',
            'date_of_birth' => '1988-02-02',
            'date_of_joining' => '2020-09-01',
            'gender' => 'male',
            'staff_type' => 'non-teaching',
            'department_id' => $this->department->department_id,
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'work_email' => 'peter.otieno@school.org',
            'phone_primary' => '0722000111',
            'current_address' => 'Karen, Nairobi',
            'city' => 'Nairobi',
            'country' => 'Kenya',
        ]);
        $staff->save();

        $response = $this->actingAs($this->admin)->get(route('staff.edit', $staff->staff_id));

        $response->assertStatus(200);
        $response->assertSee('Identity');
        $response->assertSee('T-3001');
        $response->assertSee('Peter');
        $response->assertSee('peter.otieno@school.org');
    }

    public function test_store_persists_optional_profile_fields(): void
    {
        $linked = User::factory()->create();

        $response = $this->actingAs($this->admin)->post(route('staff.store'), $this->payload([
            'staff_type' => 'administration',
            'user_id' => $linked->id,
            'qualification' => 'B.Sc. Mathematics',
            'experience' => 6.5,
            'current_address' => 'Kileleshwa, Nairobi',
            'city' => 'Nairobi',
            'country' => 'Kenya',
        ]));

        $response->assertSessionHasNoErrors();

        $staff = Staff::where('employee_number', 'T-2001')->first();
        $this->assertNotNull($staff, 'Staff row was not created.');
        $this->assertSame('administration', $staff->staff_type);
        $this->assertSame($linked->id, $staff->user_id);
        $this->assertSame('B.Sc. Mathematics', $staff->qualification);
        $this->assertEquals(6.5, (float) $staff->experience);
        $this->assertSame('Kileleshwa, Nairobi', $staff->current_address);
        $this->assertSame('Nairobi', $staff->city);
        $this->assertSame('Kenya', $staff->country);
        $this->assertSame($this->admin->id, $staff->created_by);
    }

    public function test_store_saves_profile_photo(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)->post(route('staff.store'), $this->payload([
            'photo' => UploadedFile::fake()->image('staff.png', 200, 200),
        ]));

        $response->assertSessionHasNoErrors();

        $staff = Staff::where('employee_number', 'T-2001')->first();
        $this->assertNotNull($staff);
        $this->assertNotEmpty($staff->photo_url, 'Photo was not stored.');
        Storage::disk('public')->assertExists(Str::after($staff->photo_url, 'storage/'));
    }

    public function test_staff_type_is_required_and_constrained(): void
    {
        $this->actingAs($this->admin)
            ->from(route('staff.create'))
            ->post(route('staff.store'), $this->payload(['staff_type' => 'contractor']))
            ->assertSessionHasErrors('staff_type');

        $this->actingAs($this->admin)
            ->from(route('staff.create'))
            ->post(route('staff.store'), $this->payload(['staff_type' => '']))
            ->assertSessionHasErrors('staff_type');

        $this->assertSame(0, DB::table('staff')->where('employee_number', 'T-2001')->count());
    }

    // ─── Teaching hires also get a portal login ──────────────────────────

    public function test_teaching_staff_gets_a_portal_login_and_setup_email_to_the_personal_mailbox(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)->post(route('staff.store'), $this->payload([
            'employee_number' => 'T-2002',
            'work_email' => 'grace.kiplagat@school.org',
            'personal_email' => 'grace.k@personalmail.com',
            'tsc_number' => 'TSC-8842',
            'basic_salary' => '85000',
            'experience' => 4,
        ]));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('staff.index'));

        $user = User::where('email', 'grace.k@personalmail.com')->first();
        $this->assertNotNull($user, 'A portal login was not created for the teacher.');

        $staff = Staff::where('employee_number', 'T-2002')->first();
        $this->assertNotNull($staff);
        $this->assertSame('teaching', $staff->staff_type);
        $this->assertSame($user->id, $staff->user_id, 'Staff was not linked to the new login.');
        $this->assertSame('TSC-8842', $staff->tsc_number);
        $this->assertSame('grace.k@personalmail.com', $staff->personal_email);
        $this->assertEquals(85000.0, (float) $staff->basic_salary);
        $this->assertEquals(4.0, (float) $staff->experience);

        // Teacher role assigned, and no usable password issued by the admin.
        $this->assertTrue($user->fresh()->hasRole('Teacher'));
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'grace.k@personalmail.com']);
    }

    public function test_teaching_staff_falls_back_to_the_work_email_for_the_setup_link(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('staff.store'), $this->payload([
            'employee_number' => 'T-2003',
            'work_email' => 'dan.otieno@school.org',
        ]))->assertSessionHasNoErrors();

        $this->assertNotNull(User::where('email', 'dan.otieno@school.org')->first());
        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'dan.otieno@school.org']);
    }

    public function test_teaching_staff_rejects_a_login_email_already_in_use(): void
    {
        Mail::fake();
        $existing = User::factory()->create(['email' => 'taken@personalmail.com']);

        $this->actingAs($this->admin)
            ->from(route('staff.create'))
            ->post(route('staff.store'), $this->payload([
                'employee_number' => 'T-2004',
                'personal_email' => 'taken@personalmail.com',
            ]))
            ->assertSessionHasErrors('personal_email');

        $this->assertSame(0, DB::table('staff')->where('employee_number', 'T-2004')->count());
        $this->assertSame(1, User::where('email', $existing->email)->count());
    }

    public function test_non_teaching_staff_cannot_mint_a_login_from_the_portal_fields(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)->post(route('staff.store'), $this->payload([
            'employee_number' => 'T-2005',
            'staff_type' => 'administration',
            'personal_email' => 'sneaky@personalmail.com',
            'tsc_number' => 'TSC-0000',
        ]))->assertSessionHasNoErrors();

        $this->assertNull(User::where('email', 'sneaky@personalmail.com')->first());
        $this->assertSame(0, DB::table('password_reset_tokens')->count());

        $staff = Staff::where('employee_number', 'T-2005')->first();
        $this->assertNotNull($staff);
        $this->assertSame('administration', $staff->staff_type);
        $this->assertNull($staff->user_id);
        $this->assertNull($staff->personal_email);
        $this->assertNull($staff->tsc_number);
    }
}
