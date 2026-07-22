<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentClassEnrollment;
use Illuminate\Http\Request;
use Flash;
use DB;

class StudentImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:students.import');
    }

    public function index()
    {
        return view('students.import');
    }

    public function store(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
            'academic_year_id' => 'required',
            'class_section_id' => 'required',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        
        // Skip header
        $header = fgetcsv($handle);
        
        $count = 0;
        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 4) continue;

                $student = Student::create([
                    'admission_no' => $row[0],
                    'first_name' => $row[1],
                    'last_name' => $row[2],
                    'gender' => strtolower($row[3] ?? 'male'),
                    'status' => 'active',
                    'admission_date' => now(),
                    'country' => 'Kenya',
                    'city' => 'Nairobi',
                    'emergency_contact' => $row[4] ?? '00000000',
                ]);

                StudentClassEnrollment::create([
                    'student_id' => $student->student_id,
                    'class_section_id' => $request->class_section_id,
                    'academic_year_id' => $request->academic_year_id,
                    'is_current' => true,
                    'enrollment_date' => now(),
                    'status' => 'active'
                ]);
                $count++;
            }
            DB::commit();
            Flash::success("$count students imported successfully.");
        } catch (\Exception $e) {
            DB::rollback();
            Flash::error("Import failed: " . $e->getMessage());
        }
        fclose($handle);

        return redirect()->route('students.index');
    }
}
