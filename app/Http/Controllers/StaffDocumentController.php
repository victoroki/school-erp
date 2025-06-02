<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffDocumentRequest;
use App\Http\Requests\UpdateStaffDocumentRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StaffDocumentRepository;
use Illuminate\Http\Request;
use Flash;

class StaffDocumentController extends AppBaseController
{
    /** @var StaffDocumentRepository $staffDocumentRepository*/
    private $staffDocumentRepository;

    public function __construct(StaffDocumentRepository $staffDocumentRepo)
    {
        $this->staffDocumentRepository = $staffDocumentRepo;
    }

    /**
     * Display a listing of the StaffDocument.
     */
    public function index(Request $request)
    {
        $staffDocuments = $this->staffDocumentRepository->paginate(10);

        return view('staff_documents.index')
            ->with('staffDocuments', $staffDocuments);
    }

    /**
     * Show the form for creating a new StaffDocument.
     */
    public function create()
    {
        return view('staff_documents.create');
    }

    /**
     * Store a newly created StaffDocument in storage.
     */
    public function store(CreateStaffDocumentRequest $request)
    {
        $input = $request->all();

        $staffDocument = $this->staffDocumentRepository->create($input);

        Flash::success('Staff Document saved successfully.');

        return redirect(route('staffDocuments.index'));
    }

    /**
     * Display the specified StaffDocument.
     */
    public function show($id)
    {
        $staffDocument = $this->staffDocumentRepository->find($id);

        if (empty($staffDocument)) {
            Flash::error('Staff Document not found');

            return redirect(route('staffDocuments.index'));
        }

        return view('staff_documents.show')->with('staffDocument', $staffDocument);
    }

    /**
     * Show the form for editing the specified StaffDocument.
     */
    public function edit($id)
    {
        $staffDocument = $this->staffDocumentRepository->find($id);

        if (empty($staffDocument)) {
            Flash::error('Staff Document not found');

            return redirect(route('staffDocuments.index'));
        }

        return view('staff_documents.edit')->with('staffDocument', $staffDocument);
    }

    /**
     * Update the specified StaffDocument in storage.
     */
    public function update($id, UpdateStaffDocumentRequest $request)
    {
        $staffDocument = $this->staffDocumentRepository->find($id);

        if (empty($staffDocument)) {
            Flash::error('Staff Document not found');

            return redirect(route('staffDocuments.index'));
        }

        $staffDocument = $this->staffDocumentRepository->update($request->all(), $id);

        Flash::success('Staff Document updated successfully.');

        return redirect(route('staffDocuments.index'));
    }

    /**
     * Remove the specified StaffDocument from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $staffDocument = $this->staffDocumentRepository->find($id);

        if (empty($staffDocument)) {
            Flash::error('Staff Document not found');

            return redirect(route('staffDocuments.index'));
        }

        $this->staffDocumentRepository->delete($id);

        Flash::success('Staff Document deleted successfully.');

        return redirect(route('staffDocuments.index'));
    }
}
