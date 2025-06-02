<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentDocumentRequest;
use App\Http\Requests\UpdateStudentDocumentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StudentDocumentRepository;
use Illuminate\Http\Request;
use Flash;

class StudentDocumentController extends AppBaseController
{
    /** @var StudentDocumentRepository $studentDocumentRepository*/
    private $studentDocumentRepository;

    public function __construct(StudentDocumentRepository $studentDocumentRepo)
    {
        $this->studentDocumentRepository = $studentDocumentRepo;
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
        return view('student_documents.create');
    }

    /**
     * Store a newly created StudentDocument in storage.
     */
    public function store(CreateStudentDocumentRequest $request)
    {
        $input = $request->all();

        $studentDocument = $this->studentDocumentRepository->create($input);

        Flash::success('Student Document saved successfully.');

        return redirect(route('studentDocuments.index'));
    }

    /**
     * Display the specified StudentDocument.
     */
    public function show($id)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument)) {
            Flash::error('Student Document not found');

            return redirect(route('studentDocuments.index'));
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

            return redirect(route('studentDocuments.index'));
        }

        return view('student_documents.edit')->with('studentDocument', $studentDocument);
    }

    /**
     * Update the specified StudentDocument in storage.
     */
    public function update($id, UpdateStudentDocumentRequest $request)
    {
        $studentDocument = $this->studentDocumentRepository->find($id);

        if (empty($studentDocument)) {
            Flash::error('Student Document not found');

            return redirect(route('studentDocuments.index'));
        }

        $studentDocument = $this->studentDocumentRepository->update($request->all(), $id);

        Flash::success('Student Document updated successfully.');

        return redirect(route('studentDocuments.index'));
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

            return redirect(route('studentDocuments.index'));
        }

        $this->studentDocumentRepository->delete($id);

        Flash::success('Student Document deleted successfully.');

        return redirect(route('studentDocuments.index'));
    }
}
