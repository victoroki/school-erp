<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentDocumentRequest;
use App\Http\Requests\UpdateStudentDocumentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StudentDocumentRepository;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Storage;
use App\Models\Student;

class StudentDocumentController extends AppBaseController
{
    /** @var StudentDocumentRepository $studentDocumentRepository*/
    private $studentDocumentRepository;

    public function __construct(StudentDocumentRepository $studentDocumentRepo)
    {
        $this->studentDocumentRepository = $studentDocumentRepo;
    }

    /**
     * Get dropdown data for forms
     */
    private function getDropdownData()
    {
        return [
            'students' => Student::selectRaw("student_id, CONCAT(first_name, ' ', last_name, ' - ', student_id) as full_name")
                ->pluck('full_name', 'student_id')
                ->toArray(),
            'documentTypes' => [
                'birth_certificate' => 'Birth Certificate',
                'passport' => 'Passport',
                'national_id' => 'National ID',
                'report_card' => 'Report Card',
                'medical_record' => 'Medical Record',
                'vaccination_record' => 'Vaccination Record',
                'transfer_certificate' => 'Transfer Certificate',
                'conduct_certificate' => 'Conduct Certificate',
                'student_photo' => 'Student Photo',
                'other' => 'Other'
            ]
        ];
    }

    /**
     * Display a listing of the StudentDocument.
     */
    public function index(Request $request)
    {
        $studentDocuments = $this->studentDocumentRepository->paginate(10);

        return view('student_documents.index')
            ->with('studentDocuments', $studentDocuments);
    }

    /**
     * Show the form for creating a new StudentDocument.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        
        return view('student_documents.create', $dropdownData);
    }

    /**
     * Store a newly created StudentDocument in storage.
     */
    public function store(CreateStudentDocumentRequest $request)
    {
        $input = $request->all();

        // Handle file upload
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('student_documents', $fileName, 'public');
            
            $input['file_path'] = $filePath;
            
            // If document name is empty, use the original filename
            if (empty($input['document_name'])) {
                $input['document_name'] = $file->getClientOriginalName();
            }
        }

        // Set uploaded_at to current timestamp if not provided
        if (empty($input['uploaded_at'])) {
            $input['uploaded_at'] = now();
        }

        $studentDocument = $this->studentDocumentRepository->create($input);

        Flash::success('Student Document saved successfully.');

        return redirect(route('student-documents.index'));
    }

    /**
     * Display the specified StudentDocument.
     */
    public function show($id)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument)) {
            Flash::error('Student Document not found');

            return redirect(route('student-documents.index'));
        }

        return view('student_documents.show')->with('studentDocument', $studentDocument);
    }

    /**
     * Show the form for editing the specified StudentDocument.
     */
    public function edit($id)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument)) {
            Flash::error('Student Document not found');

            return redirect(route('student-documents.index'));
        }

        $dropdownData = $this->getDropdownData();
        
        return view('student_documents.edit', array_merge(
            ['studentDocument' => $studentDocument],
            $dropdownData
        ));
    }

    /**
     * Update the specified StudentDocument in storage.
     */
    public function update($id, UpdateStudentDocumentRequest $request)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument)) {
            Flash::error('Student Document not found');

            return redirect(route('student-documents.index'));
        }

        $input = $request->all();

        // Handle file upload if new file is provided
        if ($request->hasFile('document_file')) {
            // Delete old file if it exists
            if ($studentDocument->file_path && Storage::disk('public')->exists($studentDocument->file_path)) {
                Storage::disk('public')->delete($studentDocument->file_path);
            }

            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('student_documents', $fileName, 'public');
            
            $input['file_path'] = $filePath;
            
            // Update document name if it was changed or if it's empty
            if (empty($input['document_name']) || $input['document_name'] == $studentDocument->document_name) {
                $input['document_name'] = $file->getClientOriginalName();
            }
        }

        $studentDocument = $this->studentDocumentRepository->update($input, $id);

        Flash::success('Student Document updated successfully.');

        return redirect(route('student-documents.index'));
    }

    /**
     * Remove the specified StudentDocument from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument)) {
            Flash::error('Student Document not found');

            return redirect(route('student-documents.index'));
        }

        // Delete the associated file
        if ($studentDocument->file_path && Storage::disk('public')->exists($studentDocument->file_path)) {
            Storage::disk('public')->delete($studentDocument->file_path);
        }

        $this->studentDocumentRepository->delete($id);

        Flash::success('Student Document deleted successfully.');

        return redirect(route('student-documents.index'));
    }

    /**
     * Download the document file
     */
    public function download($id)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument) || !$studentDocument->file_path) {
            Flash::error('Document file not found');
            return redirect(route('student-documents.index'));
        }

        if (!Storage::disk('public')->exists($studentDocument->file_path)) {
            Flash::error('Document file not found on server');
            return redirect(route('student-documents.index'));
        }

        return Storage::disk('public')->download($studentDocument->file_path, $studentDocument->document_name);
    }
}