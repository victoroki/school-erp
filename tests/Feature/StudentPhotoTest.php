<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Student photo uploads.
 * - Photos are stored in public/uploads/student_photos — a plain folder that
 *   is directly servable, so no `storage` symlink is needed (works on shared
 *   cPanel hosting without terminal access).
 * - The admission/edit form shows immediate client-side feedback (filename +
 *   thumbnail preview) and the existing photo when editing.
 */
class StudentPhotoTest extends TestCase
{
    use RefreshDatabase;

    private array $createdPhotoPaths = [];

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

    protected function tearDown(): void
    {
        foreach ($this->createdPhotoPaths as $path) {
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    private function studentPayload(array $overrides = []): array
    {
        return array_merge([
            'admission_no' => 'ADM-P'.bin2hex(random_bytes(4)),
            'first_name' => 'Kevin',
            'last_name' => 'Otieno',
            'date_of_birth' => '2012-05-05',
            'gender' => 'male',
            'city' => 'Kisumu',
            'country' => 'Kenya',
            'admission_date' => '2025-02-01',
            'emergency_contact' => '0722000000',
            'status' => 'active',
            'is_active' => true,
        ], $overrides);
    }

    private function trackPhoto(string $photoUrl): void
    {
        $this->createdPhotoPaths[] = public_path('uploads/'.$photoUrl);
    }

    public function test_photo_upload_is_stored_under_public_uploads_and_displayed_on_profile(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('students.store'), array_merge(
            $this->studentPayload(),
            ['photo' => UploadedFile::fake()->image('portrait.jpg')]
        ));

        $response->assertRedirect(route('students.index'));

        $student = Student::where('admission_no', 'like', 'ADM-P%')->first();
        $this->assertNotNull($student);
        $this->assertStringStartsWith('student_photos/', $student->photo_url);
        $this->assertFileExists(public_path('uploads/'.$student->photo_url));
        $this->trackPhoto($student->photo_url);

        // The profile page must render the photo from the uploads folder.
        $this->actingAs($this->superAdmin)
            ->get(route('students.show', $student->student_id))
            ->assertOk()
            ->assertSee('/uploads/'.$student->photo_url, false);
    }

    public function test_edit_form_shows_existing_photo_preview(): void
    {
        // The edit form only shows the saved photo when the file actually
        // exists on disk — create it so the preview is rendered.
        $uploadsDir = public_path('uploads/student_photos');
        if (! is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }
        file_put_contents($uploadsDir.'/existing.jpg', 'photo-bytes');
        $this->trackPhoto('student_photos/existing.jpg');

        $student = Student::create(array_merge($this->studentPayload(), [
            'photo_url' => 'student_photos/existing.jpg',
        ]));

        $this->actingAs($this->superAdmin)
            ->get(route('students.edit', $student->student_id))
            ->assertOk()
            ->assertSee('id="photo-preview"', false)
            ->assertSee('/uploads/student_photos/existing.jpg', false);
    }

    public function test_replacing_photo_on_update_removes_old_file(): void
    {
        $uploadsDir = public_path('uploads/student_photos');
        if (! is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0775, true);
        }
        file_put_contents($uploadsDir.'/old.jpg', 'old-bytes');
        $this->trackPhoto('student_photos/old.jpg');

        $student = Student::create(array_merge($this->studentPayload(), [
            'photo_url' => 'student_photos/old.jpg',
        ]));

        $payload = $this->studentPayload(['admission_no' => $student->admission_no]);
        $payload['photo'] = UploadedFile::fake()->image('new.jpg');

        $this->actingAs($this->superAdmin)
            ->patch(route('students.update', $student->student_id), $payload)
            ->assertRedirect(route('students.index'));

        $student->refresh();

        $this->assertNotSame('student_photos/old.jpg', $student->photo_url);
        $this->assertStringStartsWith('student_photos/', $student->photo_url);
        $this->assertFileDoesNotExist(public_path('uploads/student_photos/old.jpg'));
        $this->assertFileExists(public_path('uploads/'.$student->photo_url));
        $this->trackPhoto($student->photo_url);
    }
}
