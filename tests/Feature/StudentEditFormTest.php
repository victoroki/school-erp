<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Task 2: Date of Birth (and admission date / country) must be preserved
 * when editing a student. Regression: Form::date with Form::model rendered
 * the Carbon value as "Y-m-d H:i:s", which `<input type="date">` rejects and
 * the browser displayed as blank.
 */
class StudentEditFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');
    }

    private function makeStudent(array $overrides = []): Student
    {
        // admission_no is varchar(20) — keep the suffix short but unique.
        $suffix = substr(str_replace(['.', ' '], '', uniqid('', true)), -9);
        return Student::create(array_merge([
            'admission_no' => 'ADM-E'.$suffix,
            'first_name' => 'Grace',
            'last_name' => 'Njeri',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'city' => 'Nairobi',
            'country' => 'Tanzania',
            'admission_date' => '2024-01-15',
            'emergency_contact' => '0722000000',
            'status' => 'active',
            'is_active' => true,
        ], $overrides));
    }

    public function test_students_index_route_renders_for_authorized_user(): void
    {
        // Task 4 regression: GET /students must route to the controller and
        // render (the historical 404 was caused by the public/students
        // directory shadowing the route, not by the route list itself).
        $this->actingAs($this->superAdmin)
            ->get(route('students.index'))
            ->assertOk();
    }

    public function test_edit_form_renders_saved_dob_as_plain_y_m_d(): void
    {
        $student = $this->makeStudent();

        $response = $this->actingAs($this->superAdmin)
            ->get(route('students.edit', $student->student_id));

        $response->assertOk();
        // DOB must be a plain Y-m-d value the date input accepts.
        $response->assertSee('value="2010-01-01"', false);
        $response->assertDontSee('2010-01-01 00:00:00', false);
        // Admission date must show the saved date, not today's date.
        $response->assertSee('value="2024-01-15"', false);
        $response->assertDontSee(date('Y-m-d'), false);
        // Country must show the saved value, not the default "Kenya".
        $response->assertSee('value="Tanzania"', false);
    }

    public function test_student_without_photo_renders_initials_avatar_not_broken_image(): void
    {
        // A student with no photo must show a local initials circle — no
        // broken <img> and no dependency on an external avatar service.
        $noPhoto = $this->makeStudent(['photo_url' => null]);
        $this->assertSame('GN', $noPhoto->initials);
        $this->assertFalse($noPhoto->has_photo);
        $this->assertNull($noPhoto->avatar_url);

        // The profile header renders the initials circle for this student.
        $this->actingAs($this->superAdmin)
            ->get(route('students.show', $noPhoto->student_id))
            ->assertOk()
            ->assertSee('GN', false)
            ->assertDontSee('ui-avatars.com', false);

        // A photo_url pointing at a file that does not exist must ALSO fall
        // back to initials (a dangling row otherwise renders a broken image).
        $dangling = $this->makeStudent(['photo_url' => 'student_photos/does-not-exist.jpg']);
        $this->assertFalse($dangling->has_photo);
        $this->assertNull($dangling->avatar_url);
    }

    public function test_student_with_photo_renders_image_avatar(): void
    {
        $withPhoto = $this->makeStudent(['photo_url' => 'student_photos/exists.jpg']);

        // Create the actual file so the disk check passes.
        \Illuminate\Support\Facades\File::ensureDirectoryExists(public_path('uploads/student_photos'));
        \Illuminate\Support\Facades\File::put(public_path('uploads/student_photos/exists.jpg'), 'fake');

        try {
            $this->assertTrue($withPhoto->has_photo);
            $this->assertStringContainsString('uploads/student_photos/exists.jpg', $withPhoto->avatar_url);
        } finally {
            \Illuminate\Support\Facades\File::delete(public_path('uploads/student_photos/exists.jpg'));
        }
    }

    public function test_photo_field_renders_upload_feedback_markup_and_vanilla_js(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('students.create'));

        $response->assertOk();
        // The selected-file feedback must exist and not depend on jQuery
        // (the bundle is a deferred module, so a classic inline script cannot
        // rely on $(document).ready — the handler must be plain JS bound to
        // the input element directly).
        $response->assertSee('id="photo-filename"', false);
        $response->assertSee('id="photo-preview-wrap"', false);
        $response->assertSee("document.getElementById('photo')", false);
        $response->assertSee("input.addEventListener('change'", false);
        $response->assertSee("reader.readAsDataURL(file)", false);
        // The form must carry the multipart enctype or the upload never sends.
        $response->assertSee('multipart/form-data', false);
    }

    public function test_saving_without_touching_dob_preserves_existing_values(): void
    {
        $student = $this->makeStudent();

        // Mirror exactly what the fixed edit form submits.
        $payload = [
            'admission_no' => $student->admission_no,
            'nemis_number' => '',
            'education_system' => 'CBC',
            'status' => 'active',
            'student_category' => '',
            'first_name' => 'Grace',
            'middle_name' => '',
            'last_name' => 'Njeri',
            'date_of_birth' => '2010-01-01',
            'gender' => 'female',
            'blood_group' => 'O+',
            'nationality' => 'Kenyan',
            'religion' => '',
            'city' => 'Nairobi',
            'county' => 'Nairobi',
            'sub_county' => '',
            'country' => 'Tanzania',
            'emergency_contact_name' => 'John Njeri',
            'emergency_contact_relationship' => 'Father',
            'emergency_contact' => '0722000000',
            'medical_conditions' => '',
            'allergies' => '',
            'medications' => '',
            'admission_date' => '2024-01-15',
            'enrollment_status' => 'enrolled',
            'uses_transport' => 0,
            'is_hosteller' => 0,
            'is_scholarship_holder' => 0,
            'previous_school' => '',
            'previous_class' => '',
            'transfer_certificate_no' => '',
        ];

        $this->actingAs($this->superAdmin)
            ->patch(route('students.update', $student->student_id), $payload)
            ->assertRedirect(route('students.index'));

        $student->refresh();

        $this->assertSame('2010-01-01', $student->date_of_birth->format('Y-m-d'));
        $this->assertSame('2024-01-15', $student->admission_date->format('Y-m-d'));
        $this->assertSame('Tanzania', $student->country);
    }
}
