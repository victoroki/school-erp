<?php

namespace Tests\Feature;

use App\Models\Parents;
use App\Models\Staff;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Teacher onboarding (secure password setup instead of admin-chosen password)
 * and the guardian import path of the bulk student import.
 */
class OnboardingAndImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

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

    // ─── Teacher onboarding ──────────────────────────────────────────────

    private function validOnboardingPayload(array $overrides = []): array
    {
        static $seq = 0;
        $seq++;

        return array_merge([
            'first_name' => 'New',
            'last_name' => 'Teacher' . $seq,
            'date_of_birth' => '1992-04-04',
            'gender' => 'female',
            'phone_primary' => '0712' . str_pad((string) $seq, 6, '5', STR_PAD_LEFT),
            'work_email' => 'teacher' . $seq . '.' . uniqid() . '@school.ac.ke',
            'employment_type' => 'full_time',
            'employment_status' => 'active',
            'login_email' => 'login' . $seq . '.' . uniqid() . '@school.ac.ke',
        ], $overrides);
    }

    public function test_onboarding_sends_a_password_setup_email_and_redirects_to_all_teachers(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->admin)
            ->post(route('teacher-onboarding.store'), $this->validOnboardingPayload());

        $response->assertRedirect(route('teacher-management.index'));
        $response->assertSessionHas('flash_notification');

        // The password broker created a token for the new account.
        $user = User::where('email', 'like', '%@school.ac.ke')->latest('id')->first();
        $this->assertNotNull($user);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    public function test_onboarding_does_not_accept_an_admin_chosen_password(): void
    {
        Mail::fake();

        // The old flow required `password` + `password_confirmation` and stored
        // its hash. Those fields are gone from the rules; posting them must be
        // ignored, and the account must not be usable with the posted value.
        $payload = $this->validOnboardingPayload([
            'password' => 'SuperSecret123!',
            'password_confirmation' => 'SuperSecret123!',
        ]);

        $this->actingAs($this->admin)->post(route('teacher-onboarding.store'), $payload)
            ->assertRedirect(route('teacher-management.index'));

        $user = User::where('email', 'like', '%@school.ac.ke')->latest('id')->first();
        $this->assertNotNull($user);
        $this->assertNotSame('SuperSecret123!', $user->password);
        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('SuperSecret123!', $user->password));
    }

    public function test_onboarding_validates_required_fields(): void
    {
        Mail::fake();

        $this->actingAs($this->admin)
            ->post(route('teacher-onboarding.store'), [
                'first_name' => '',
                'login_email' => 'not-an-email',
            ])
            ->assertSessionHasErrors(['first_name', 'last_name', 'login_email']);
    }

    public function test_onboarding_rejects_a_duplicate_login_email(): void
    {
        Mail::fake();

        $existing = User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($this->admin)
            ->post(route('teacher-onboarding.store'), $this->validOnboardingPayload([
                'login_email' => 'taken@example.test',
            ]))
            ->assertSessionHasErrors('login_email');
    }

    // ─── Student import: guardians ───────────────────────────────────────

    /**
     * Build a real .xlsx workbook in memory from the project's own template
     * generator, then post it with the guardian columns filled.
     */
    private function uploadImport(string $content, string $filename = 'students.xlsx'): \Illuminate\Http\UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $content);

        return new \Illuminate\Http\UploadedFile(
            $path,
            $filename,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true // test mode: bypasses the is_uploaded_file check
        );
    }

    private function buildWorkbook(array $rows): string
    {
        $headers = [
            'admission_no', 'first_name', 'middle_name', 'last_name', 'date_of_birth', 'gender',
            'city', 'admission_date', 'country', 'nemis_number', 'phone', 'emergency_contact',
            'emergency_contact_name', 'previous_school', 'medical_conditions', 'allergies',
            'guardian_first_name', 'guardian_last_name', 'guardian_relationship', 'guardian_phone',
            'guardian_alternate_phone', 'guardian_email', 'guardian_occupation', 'guardian_is_primary',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $col = 1;
        foreach ($headers as $header) {
            $sheet->getCell([$col, 1])->setValue($header);
            $col++;
        }

        $rowNum = 2;
        foreach ($rows as $row) {
            $col = 1;
            foreach ($headers as $header) {
                $sheet->getCell([$col, $rowNum])->setValue($row[$header] ?? '');
                $col++;
            }
            $rowNum++;
        }

        $path = tempnam(sys_get_temp_dir(), 'import') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);
        $content = file_get_contents($path);
        unlink($path);
        $spreadsheet->disconnectWorksheets();

        return $content;
    }

    private function makeClassSection(): array
    {
        $class = \App\Models\SchoolClass::create(['name' => 'ImpForm-' . uniqid(), 'numeric_value' => 1]);
        $section = \App\Models\Section::create(['class_id' => $class->class_id, 'name' => 'I1', 'capacity' => 40]);
        $year = \App\Models\AcademicYear::create([
            'name' => 'AY-IMP-' . uniqid(),
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);
        $cs = \App\Models\ClassSection::create([
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
            'academic_year_id' => $year->academic_year_id,
            'classroom_id' => \App\Models\Classroom::create([
                'room_number' => 'IR-' . substr(uniqid(), -5),
                'building' => 'B',
                'floor' => 1,
                'capacity' => 40,
            ])->classroom_id,
        ])->class_section_id;

        return [$cs, $year->academic_year_id];
    }

    private function importRow(array $row, $classSectionId, $yearId): void
    {
        $file = $this->uploadImport($this->buildWorkbook([$row]));

        $this->actingAs($this->admin)->post(route('students.import.store'), [
            'excel_file' => $file,
            'class_section_id' => $classSectionId,
            'academic_year_id' => $yearId,
        ]);
    }

    public function test_import_creates_the_guardian_and_links_the_student(): void
    {
        [$cs, $year] = $this->makeClassSection();

        $this->importRow([
            'admission_no' => 'GIMP001',
            'first_name' => 'Child',
            'last_name' => 'One',
            'date_of_birth' => '2013-03-03',
            'gender' => 'male',
            'admission_date' => '10/01/2026',
            'guardian_first_name' => 'Guardian',
            'guardian_last_name' => 'Prime',
            'guardian_relationship' => 'mother',
            'guardian_phone' => '0799000111',
            'guardian_email' => 'prime@example.test',
            'guardian_is_primary' => '1',
        ], $cs, $year);

        $this->assertDatabaseHas('parents', [
            'first_name' => 'Guardian',
            'last_name' => 'Prime',
            'relationship' => 'mother',
            'phone' => '0799000111',
        ]);
        $this->assertDatabaseHas('student_parent_relationship', ['is_primary_contact' => 1]);

        $student = Student::where('admission_no', 'GIMP001')->first();
        $this->assertNotNull($student);
        $this->assertSame(1, $student->parents()->count());
    }

    public function test_import_reuses_an_existing_guardian_by_phone(): void
    {
        [$cs, $year] = $this->makeClassSection();

        $existing = Parents::create([
            'first_name' => 'Existing',
            'last_name' => 'Guardian',
            'relationship' => 'father',
            'phone' => '0799000222',
        ]);

        // A sibling of an already-enrolled child: same guardian phone.
        $this->importRow([
            'admission_no' => 'GIMP002',
            'first_name' => 'Child',
            'last_name' => 'Two',
            'date_of_birth' => '2011-02-02',
            'gender' => 'female',
            'admission_date' => '10/01/2026',
            'guardian_first_name' => 'Whatever',
            'guardian_last_name' => 'Typed',
            'guardian_relationship' => 'father',
            'guardian_phone' => '0799000222',
        ], $cs, $year);

        // Reused, not duplicated: still exactly one guardian with that phone.
        $this->assertSame(1, Parents::where('phone', '0799000222')->count());
        $this->assertDatabaseHas('student_parent_relationship', [
            'parent_id' => $existing->parent_id,
        ]);
    }

    public function test_import_links_one_guardian_to_multiple_students(): void
    {
        [$cs, $year] = $this->makeClassSection();

        $base = [
            'guardian_first_name' => 'Shared',
            'guardian_last_name' => 'Parent',
            'guardian_relationship' => 'guardian',
            'guardian_phone' => '0799000333',
        ];

        $this->importRow(array_merge($base, [
            'admission_no' => 'GIMP003',
            'first_name' => 'Kid',
            'last_name' => 'Alpha',
            'date_of_birth' => '2012-01-01',
            'gender' => 'male',
            'admission_date' => '10/01/2026',
        ]), $cs, $year);

        $this->importRow(array_merge($base, [
            'admission_no' => 'GIMP004',
            'first_name' => 'Kid',
            'last_name' => 'Beta',
            'date_of_birth' => '2014-01-01',
            'gender' => 'female',
            'admission_date' => '10/01/2026',
        ]), $cs, $year);

        $guardian = Parents::where('phone', '0799000333')->first();
        $this->assertNotNull($guardian);
        $this->assertSame(2, $guardian->students()->count());
    }

    public function test_import_rejects_an_incomplete_guardian_row(): void
    {
        [$cs, $year] = $this->makeClassSection();

        $this->importRow([
            'admission_no' => 'GIMP005',
            'first_name' => 'Child',
            'last_name' => 'Three',
            'date_of_birth' => '2013-01-01',
            'gender' => 'male',
            'admission_date' => '10/01/2026',
            'guardian_first_name' => 'Half',
            'guardian_last_name' => '',
            'guardian_phone' => '',
        ], $cs, $year);

        // The whole row is skipped — no half-imported student with a broken guardian.
        $this->assertDatabaseMissing('students', ['admission_no' => 'GIMP005']);
    }

    public function test_import_still_works_without_guardian_columns(): void
    {
        [$cs, $year] = $this->makeClassSection();

        $this->importRow([
            'admission_no' => 'GIMP006',
            'first_name' => 'Solo',
            'last_name' => 'Child',
            'date_of_birth' => '2013-06-06',
            'gender' => 'female',
            'admission_date' => '10/01/2026',
        ], $cs, $year);

        $student = Student::where('admission_no', 'GIMP006')->first();
        $this->assertNotNull($student);
        $this->assertSame(0, $student->parents()->count());
        $this->assertSame(1, $student->studentClassEnrollments()->where('is_current', true)->count());
    }
}
