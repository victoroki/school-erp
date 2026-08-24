<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\ClassSection;
use App\Models\SchoolClass;
use App\Models\Section;
use App\Models\Student;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RbacSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class StudentImportTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'admission_no', 'first_name', 'middle_name', 'last_name', 'date_of_birth',
        'gender', 'city', 'admission_date', 'country', 'nemis_number', 'phone',
        'emergency_contact', 'emergency_contact_name', 'previous_school',
        'medical_conditions', 'allergies',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        if (Schema::hasTable('roles')) {
            $this->seed(PermissionSeeder::class);
            $this->seed(RbacSeeder::class);
        }

        $this->superAdmin = $this->createUserWithRole('Super Admin');
        $this->teacher = $this->createUserWithRole('Teacher');

        $this->academicYear = AcademicYear::create([
            'name' => '2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
        ]);

        $class = SchoolClass::create(['name' => 'Form 1', 'numeric_value' => 1]);
        $section = Section::create(['name' => 'A']);

        $this->classSection = ClassSection::create([
            'academic_year_id' => $this->academicYear->academic_year_id,
            'class_id' => $class->class_id,
            'section_id' => $section->section_id,
        ]);
    }

    private function createUserWithRole(string $roleName): User
    {
        $user = User::factory()->create();
        $user->assignRole($roleName);

        return $user;
    }

    private function validRow(array $overrides = []): array
    {
        return array_merge([
            'admission_no' => 'ADM-'.uniqid(),
            'first_name' => 'Jane',
            'middle_name' => '',
            'last_name' => 'Doe',
            'date_of_birth' => '15/03/2015',
            'gender' => 'female',
            'city' => 'Nairobi',
            'admission_date' => '10/01/2025',
            'country' => 'Kenya',
            'nemis_number' => '',
            'phone' => '',
            'emergency_contact' => '',
            'emergency_contact_name' => '',
            'previous_school' => '',
            'medical_conditions' => '',
            'allergies' => '',
        ], $overrides);
    }

    private function makeXlsxUpload(array $rows, ?array $headers = null): UploadedFile
    {
        $headers = $headers ?? self::HEADERS;

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $field => $value) {
                $columnIndex = array_search($field, $headers, true);
                if ($columnIndex !== false) {
                    $sheet->setCellValue([$columnIndex + 1, $rowIndex + 2], $value);
                }
            }
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'imp');
        (new Xlsx($spreadsheet))->save($tmpPath);

        return new UploadedFile(
            $tmpPath,
            'students.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    public function test_import_template_downloads_workbook(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('students.import.template'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->assertHeader('Content-Disposition', 'attachment; filename="student_import_template.xlsx"');
    }

    public function test_valid_xlsx_imports_students_and_enrollments(): void
    {
        $file = $this->makeXlsxUpload([
            $this->validRow(['admission_no' => 'ADM-2025-001']),
            $this->validRow(['admission_no' => 'ADM-2025-002', 'gender' => 'male']),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $response->assertRedirect(route('students.import'));
        $response->assertSessionHas('import_report');

        $this->assertDatabaseHas('students', ['admission_no' => 'ADM-2025-001', 'first_name' => 'Jane']);
        $this->assertDatabaseHas('students', ['admission_no' => 'ADM-2025-002', 'gender' => 'male']);

        $this->assertDatabaseHas('student_class_enrollments', [
            'student_id' => Student::where('admission_no', 'ADM-2025-001')->value('student_id'),
            'class_section_id' => $this->classSection->class_section_id,
            'academic_year_id' => $this->academicYear->academic_year_id,
            'is_current' => true,
            'status' => 'active',
        ]);

        $report = session('import_report');
        $this->assertEquals(2, $report['imported']);
        $this->assertEmpty($report['failures']);
    }

    public function test_invalid_rows_are_skipped_and_reported(): void
    {
        $file = $this->makeXlsxUpload([
            $this->validRow(['admission_no' => 'ADM-OK-1']),
            $this->validRow(['admission_no' => 'ADM-BAD-1', 'last_name' => '']),
            $this->validRow(['admission_no' => 'ADM-BAD-2', 'gender' => 'unknown']),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $response->assertSessionHas('import_report');

        $this->assertDatabaseHas('students', ['admission_no' => 'ADM-OK-1']);
        $this->assertDatabaseMissing('students', ['admission_no' => 'ADM-BAD-1']);
        $this->assertDatabaseMissing('students', ['admission_no' => 'ADM-BAD-2']);

        $report = session('import_report');
        $this->assertEquals(3, $report['total_rows']);
        $this->assertEquals(1, $report['imported']);
        $this->assertCount(2, $report['failures']);

        $this->assertEquals([3, 4], collect($report['failures'])->pluck('row')->all());
    }

    public function test_existing_admission_number_is_skipped(): void
    {
        Student::create([
            'admission_no' => 'ADM-DUP-1',
            'first_name' => 'Existing',
            'last_name' => 'Student',
            'date_of_birth' => '2010-01-01',
            'gender' => 'male',
            'city' => 'Nairobi',
            'country' => 'Kenya',
            'admission_date' => now(),
            'status' => 'active',
        ]);

        $file = $this->makeXlsxUpload([
            $this->validRow(['admission_no' => 'ADM-DUP-1']),
            $this->validRow(['admission_no' => 'ADM-NEW-1']),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $report = session('import_report');
        $this->assertEquals(1, $report['imported']);
        $this->assertCount(1, $report['failures']);
        $this->assertStringContainsString('already exists', $report['failures'][0]['errors'][0]);
    }

    public function test_duplicate_admission_numbers_within_file_are_rejected(): void
    {
        $file = $this->makeXlsxUpload([
            $this->validRow(['admission_no' => 'ADM-SAME-1']),
            $this->validRow(['admission_no' => 'ADM-SAME-1']),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $report = session('import_report');
        $this->assertEquals(1, $report['imported']);
        $this->assertCount(1, $report['failures']);
        $this->assertStringContainsString('Duplicate admission number', $report['failures'][0]['errors'][0]);

        $this->assertEquals(1, Student::where('admission_no', 'ADM-SAME-1')->count());
    }

    public function test_numeric_excel_date_serial_is_accepted(): void
    {
        $file = $this->makeXlsxUpload([
            $this->validRow([
                'admission_no' => 'ADM-SERIAL-1',
                'date_of_birth' => 42078,
                'admission_date' => 45640,
            ]),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $report = session('import_report');
        $this->assertEquals(1, $report['imported']);

        $student = Student::where('admission_no', 'ADM-SERIAL-1')->first();
        $this->assertNotNull($student);
        $this->assertEquals('2015-03-15', $student->date_of_birth->format('Y-m-d'));
    }

    public function test_missing_required_column_is_rejected(): void
    {
        $headers = array_values(array_diff(self::HEADERS, ['gender']));
        $file = $this->makeXlsxUpload([
            $this->validRow(['admission_no' => 'ADM-NO-GENDER']),
        ], $headers);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $response->assertSessionHas('flash_notification');
        $this->assertEquals(0, Student::count());
    }

    public function test_teacher_cannot_access_import_routes(): void
    {
        $this->actingAs($this->teacher)
            ->get(route('students.import'))
            ->assertForbidden();

        $this->actingAs($this->teacher)
            ->get(route('students.import.template'))
            ->assertForbidden();

        $file = $this->makeXlsxUpload([$this->validRow()]);

        $this->actingAs($this->teacher)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ])
            ->assertForbidden();

        $this->assertEquals(0, Student::count());
    }

    public function test_optional_fields_are_saved_when_provided(): void
    {
        $file = $this->makeXlsxUpload([
            $this->validRow([
                'admission_no' => 'ADM-OPT-1',
                'middle_name' => 'Marie',
                'nemis_number' => 'NEM-99999',
                'phone' => '0711000000',
                'emergency_contact' => '0722000000',
                'emergency_contact_name' => 'John Doe',
                'previous_school' => 'ABC Academy',
                'medical_conditions' => 'Asthma',
                'allergies' => 'Peanuts',
                'country' => 'Tanzania',
            ]),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $report = session('import_report');
        $this->assertEquals(1, $report['imported']);

        $student = Student::where('admission_no', 'ADM-OPT-1')->first();
        $this->assertEquals('Marie', $student->middle_name);
        $this->assertEquals('NEM-99999', $student->nemis_number);
        $this->assertEquals('0711000000', $student->phone);
        $this->assertEquals('0722000000', $student->emergency_contact);
        $this->assertEquals('John Doe', $student->emergency_contact_name);
        $this->assertEquals('ABC Academy', $student->previous_school);
        $this->assertEquals('Asthma', $student->medical_conditions);
        $this->assertEquals('Peanuts', $student->allergies);
        $this->assertEquals('Tanzania', $student->country);
    }

    public function test_empty_optional_fields_default_correctly(): void
    {
        $file = $this->makeXlsxUpload([
            $this->validRow([
                'admission_no' => 'ADM-DEF-1',
                'country' => '',
            ]),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('students.import.store'), [
                'excel_file' => $file,
                'academic_year_id' => $this->academicYear->academic_year_id,
                'class_section_id' => $this->classSection->class_section_id,
            ]);

        $student = Student::where('admission_no', 'ADM-DEF-1')->first();
        $this->assertEquals('Kenya', $student->country);
        $this->assertNull($student->middle_name);
        $this->assertNull($student->nemis_number);
        $this->assertNull($student->phone);
        $this->assertNull($student->emergency_contact);
        $this->assertNull($student->emergency_contact_name);
        $this->assertNull($student->previous_school);
        $this->assertNull($student->medical_conditions);
        $this->assertNull($student->allergies);
    }
}
