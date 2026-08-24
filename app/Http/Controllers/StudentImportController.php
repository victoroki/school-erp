<?php

namespace App\Http\Controllers;

use App\Models\AuditTrail;
use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Flash;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportController extends Controller
{
    /**
     * Trimmed template columns — only what's needed for a valid student record.
     * Optional fields that were removed are all fillable via the student edit form.
     */
    protected array $templateHeaders = [
        'admission_no'        => 'Unique admission number. Required. Max 20 characters.',
        'first_name'          => 'Student first name. Required. Max 50 characters.',
        'middle_name'         => 'Student middle name (if any). Max 50 characters.',
        'last_name'           => 'Student last name / surname. Required. Max 50 characters.',
        'date_of_birth'       => 'Date of birth. Required. Format: DD-MM-YYYY or YYYY-MM-DD.',
        'gender'              => 'Required. Must be exactly: male, female, or other.',
        'city'                => 'City or town. Optional — defaults to "N/A" if left blank.',
        'admission_date'      => 'Date the student was admitted. Required. Format: DD-MM-YYYY or YYYY-MM-DD.',
        'country'             => 'Country. Optional — defaults to Kenya if left blank.',
        'nemis_number'        => 'NEMIS number (Kenyan National Education Management Info System). Optional.',
        'phone'               => "Student's phone number (if applicable). Optional.",
        'emergency_contact'   => 'Primary emergency contact phone number. Optional.',
        'emergency_contact_name' => 'Name of the emergency contact person. Optional.',
        'previous_school'     => 'Name of school previously attended. Optional.',
        'medical_conditions'  => 'Any chronic or ongoing medical conditions. Optional.',
        'allergies'           => 'Known allergies (food, drug, environmental). Optional.',
    ];

    protected array $requiredFields = [
        'admission_no', 'first_name', 'last_name', 'date_of_birth',
        'gender', 'admission_date',
    ];

    public function __construct()
    {
        $this->middleware('can:students.import');
    }

    public function index()
    {
        return view('students.import');
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Students');

        $column = 1;
        foreach ($this->templateHeaders as $field => $description) {
            $cell = $sheet->getCell([$column, 1]);
            $cell->setValue($field);
            $cell->getStyle()->getFont()->setBold(true);

            if (in_array($field, $this->requiredFields)) {
                $cell->getStyle()->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFFFE699');
            }

            $sheet->getColumnDimensionByColumn($column)->setWidth(22);
            $column++;
        }

        $sheet->freezePane('A2');

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->setCellValue([1, 1], 'Field');
        $instructions->setCellValue([2, 1], 'Required');
        $instructions->setCellValue([3, 1], 'Description & Format');
        $instructions->setCellValue([4, 1], 'Example');
        $instructions->getStyle('A1:D1')->getFont()->setBold(true);

        $examples = [
            'admission_no'            => 'ADM-2025-001',
            'first_name'              => 'Jane',
            'middle_name'             => 'Marie',
            'last_name'               => 'Doe',
            'date_of_birth'           => '15/03/2015',
            'gender'                  => 'female',
            'city'                    => 'Nairobi',
            'admission_date'          => '10/01/2025',
            'country'                 => 'Kenya',
            'nemis_number'            => 'NEM-12345',
            'phone'                   => '0711000000',
            'emergency_contact'       => '0722000000',
            'emergency_contact_name'  => 'John Doe',
            'previous_school'         => 'ABC Academy',
            'medical_conditions'      => 'Asthma',
            'allergies'               => 'Peanuts',
        ];

        $row = 2;
        foreach ($this->templateHeaders as $field => $description) {
            $instructions->setCellValue([1, $row], $field);
            $instructions->setCellValue([2, $row], in_array($field, $this->requiredFields) ? 'Yes' : 'No');
            $instructions->setCellValue([3, $row], $description);
            $instructions->setCellValue([4, $row], $examples[$field] ?? '');
            $row++;
        }

        $row += 2;
        $instructions->setCellValue([1, $row], 'NOTES');
        $instructions->getStyle([1, $row])->getFont()->setBold(true);
        $row++;
        $instructions->setCellValue([1, $row], '1. Class/Section and Academic Year are selected on the upload form, not in the spreadsheet.');
        $row++;
        $instructions->setCellValue([1, $row], '2. Required columns are highlighted in yellow on the Students sheet.');
        $row++;
        $instructions->setCellValue([1, $row], '3. Rows with validation errors are skipped — valid rows are still imported.');
        $row++;
        $instructions->setCellValue([1, $row], '4. Additional fields (blood group, transport, scholarship, address, etc.) can be edited after import via the student edit form.');

        $instructions->getColumnDimension('A')->setWidth(30);
        $instructions->getColumnDimension('C')->setWidth(75);
        $instructions->getColumnDimension('D')->setWidth(20);

        $filename = 'student_import_template.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->stream(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls',
            'academic_year_id' => 'required',
            'class_section_id' => 'required',
        ]);

        $file = $request->file('excel_file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
        } catch (\Throwable $e) {
            Flash::error('Could not read the Excel file. Please ensure it is a valid .xlsx file.');

            return redirect()->back()->withInput();
        }

        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = $sheet->getHighestDataRow();
        $columnCount = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        $headers = [];
        for ($col = 1; $col <= $columnCount; $col++) {
            $value = $sheet->getCell([$col, 1])->getValue();
            if ($value !== null) {
                $headers[trim((string) $value)] = $col;
            }
        }

        $missingColumns = array_diff($this->requiredFields, array_keys($headers));
        if ($missingColumns) {
            Flash::error('The uploaded file is missing required columns: '.implode(', ', $missingColumns).'. Please download the template and fill it in.');

            return redirect()->back()->withInput();
        }

        $rows = [];
        $seenAdmissionNos = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            $data = [];
            $isEmpty = true;
            foreach ($headers as $field => $col) {
                $value = $sheet->getCell([$col, $row])->getValue();
                if (is_object($value)) {
                    $value = (string) $value;
                }
                $data[$field] = ($value === null) ? '' : trim((string) $value);
                if ($data[$field] !== '') {
                    $isEmpty = false;
                }
            }

            if ($isEmpty) {
                continue;
            }

            $rows[] = ['row' => $row, 'data' => $data];

            if ($data['admission_no'] !== '') {
                $seenAdmissionNos[strtoupper($data['admission_no'])] = $row;
            }
        }

        $existingAdmissionNos = Student::whereIn(
            'admission_no',
            array_keys($seenAdmissionNos)
        )->pluck('admission_no')->map(function ($admissionNo) {
            return strtoupper($admissionNo);
        })->flip()->toArray();

        $failures = [];
        $validRows = [];

        foreach ($rows as $entry) {
            $errors = $this->validateRow($entry['data'], $entry['row'], $existingAdmissionNos, $seenAdmissionNos);

            if ($errors) {
                $failures[] = ['row' => $entry['row'], 'errors' => $errors];
            } else {
                $validRows[] = $entry;
            }
        }

        $imported = 0;

        if ($validRows) {
            DB::beginTransaction();
            try {
                foreach ($validRows as $entry) {
                    $this->createStudent($entry['data'], $request->class_section_id, $request->academic_year_id);
                    $imported++;
                }
                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Flash::error('Import failed: '.$e->getMessage());

                return redirect()->back()->withInput();
            }

            AuditTrail::log('Student', 'IMPORT', null, null, [
                'academic_year_id' => $request->academic_year_id,
                'class_section_id' => $request->class_section_id,
                'students_imported' => $imported,
                'failed_rows' => count($failures),
            ]);
        }

        if ($failures) {
            Flash::warning("$imported student(s) imported. ".count($failures).' row(s) skipped. See the report below for details.');
        } else {
            Flash::success("$imported student(s) imported successfully.");
        }

        return redirect()->route('students.import')->with('import_report', [
            'total_rows' => count($rows),
            'imported' => $imported,
            'failures' => $failures,
        ]);
    }

    protected function validateRow(array $data, int $row, array $existingAdmissionNos, array $seenAdmissionNos): array
    {
        $errors = [];

        $admissionNo = $data['admission_no'];
        if ($admissionNo === '') {
            $errors[] = 'Missing "admission_no" field.';
        } else {
            $admissionKey = strtoupper($admissionNo);
            if (isset($existingAdmissionNos[$admissionKey])) {
                $errors[] = 'Admission number "'.$admissionNo.'" already exists in the system.';
            } elseif (isset($seenAdmissionNos[$admissionKey]) && $seenAdmissionNos[$admissionKey] !== $row) {
                $errors[] = 'Duplicate admission number "'.$admissionNo.'" (first seen on row '.$seenAdmissionNos[$admissionKey].').';
            }
        }

        foreach (['first_name', 'last_name'] as $field) {
            if ($data[$field] === '') {
                $errors[] = 'Missing "'.$field.'" field.';
            }
        }

        $dateOfBirth = $this->parseDate($data['date_of_birth']);
        if ($data['date_of_birth'] === '') {
            $errors[] = 'Missing "date_of_birth" field.';
        } elseif ($dateOfBirth === null) {
            $errors[] = 'Invalid "date_of_birth" value "'.$data['date_of_birth'].'".';
        } elseif ($dateOfBirth->isFuture()) {
            $errors[] = '"date_of_birth" cannot be a future date.';
        }

        $admissionDate = $this->parseDate($data['admission_date']);
        if ($data['admission_date'] === '') {
            $errors[] = 'Missing "admission_date" field.';
        } elseif ($admissionDate === null) {
            $errors[] = 'Invalid "admission_date" value "'.$data['admission_date'].'".';
        }

        $gender = strtolower($data['gender'] ?? '');
        if ($gender === '') {
            $errors[] = 'Missing "gender" field.';
        } elseif (!in_array($gender, ['male', 'female', 'other'], true)) {
            $errors[] = 'Invalid "gender" value "'.$data['gender'].'". Allowed: male, female, other.';
        }

        if (isset($data['phone']) && $data['phone'] !== '' && mb_strlen($data['phone']) > 20) {
            $errors[] = '"phone" must not exceed 20 characters.';
        }

        return $errors;
    }

    protected function createStudent(array $data, $classSectionId, $academicYearId): Student
    {
        $admissionDate = $this->parseDate($data['admission_date']);
        $data['date_of_birth'] = $this->parseDate($data['date_of_birth']);
        $data['admission_date'] = $admissionDate;

        $student = Student::create($this->buildStudentPayload($data));

        StudentClassEnrollment::create([
            'student_id' => $student->student_id,
            'class_section_id' => $classSectionId,
            'academic_year_id' => $academicYearId,
            'is_current' => true,
            'enrollment_date' => $admissionDate ?: now(),
            'status' => 'active',
        ]);

        return $student;
    }

    protected function buildStudentPayload(array $data): array
    {
        return [
            'admission_no' => $data['admission_no'],
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?: null,
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => strtolower($data['gender']),
            'city' => $data['city'] ?: 'N/A',
            'admission_date' => $data['admission_date'],
            'country' => $data['country'] ?: 'Kenya',
            'nemis_number' => $data['nemis_number'] ?: null,
            'phone' => $data['phone'] ?: null,
            'emergency_contact' => $data['emergency_contact'] ?: null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?: null,
            'previous_school' => $data['previous_school'] ?: null,
            'medical_conditions' => $data['medical_conditions'] ?: null,
            'allergies' => $data['allergies'] ?: null,
        ];
    }

    protected function parseDate($value): ?Carbon
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            $numeric = (float) $value;
            if ($numeric > 25569 && $numeric < 200000) {
                try {
                    return Carbon::instance(Date::excelToDateTimeObject($numeric));
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        $value = trim((string) $value);

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d', 'Y/m/d', 'd.m.Y'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                if ($parsed !== false && $parsed->format($format) === $value) {
                    return $parsed;
                }
            } catch (\Throwable $e) {
                // Try the next format.
            }
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
