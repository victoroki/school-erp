<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStaffDocumentRequest;
use App\Http\Requests\UpdateStaffDocumentRequest;
use App\Http\Controllers\AppBaseController;
use App\Models\Staff;
use App\Repositories\StaffDocumentRepository;
use App\Models\AuditTrail;
use Illuminate\Http\Request;
use Flash;
use Illuminate\Support\Facades\Storage;

class StaffDocumentController extends AppBaseController
{
    /** @var StaffDocumentRepository $staffDocumentRepository*/
    private $staffDocumentRepository;

    public function __construct(StaffDocumentRepository $staffDocumentRepo)
    {
        $this->staffDocumentRepository = $staffDocumentRepo;
        $this->middleware('can:hr.view')->only(['index', 'show']);
        $this->middleware('can:hr.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

        private function getDropdownData()
    {
        return [
            'staffs' => Staff::selectRaw("staff_id, CONCAT(first_name, ' ', last_name, ' - ', staff_id) as full_name")
                ->pluck('full_name', 'staff_id')
                ->toArray(),
        ];
    }

    /**
     * AJAX: Search staff for the Select2 dropdown (name + staff id).
     */
    public function searchStaff(Request $request)
    {
        $term = $request->input('q', '');
        $page = $request->input('page', 1);

        $query = Staff::select('staff_id', 'first_name', 'middle_name', 'last_name', 'employee_number');

        if ($term) {
            $query->where(function ($q) use ($term) {
                $q->where('first_name', 'like', "%{$term}%")
                    ->orWhere('middle_name', 'like', "%{$term}%")
                    ->orWhere('last_name', 'like', "%{$term}%")
                    ->orWhere('employee_number', 'like', "%{$term}%")
                    ->orWhere('staff_id', 'like', "%{$term}%");
            });
        }

        $results = $query->orderBy('first_name')
            ->paginate(15, ['*'], 'page', $page);

        $formatted = $results->getCollection()->map(function ($s) {
            $name = trim(implode(' ', array_filter([$s->first_name, $s->middle_name, $s->last_name])));
            $emp = $s->employee_number ?: ('ID ' . $s->staff_id);

            return [
                'id' => $s->staff_id,
                'text' => $name . ' (' . $emp . ' · #' . $s->staff_id . ')',
            ];
        });

        return response()->json([
            'results' => $formatted->toArray(),
            'pagination' => [
                'more' => $results->hasMorePages(),
            ],
        ]);
    }

    /**
     * Display a listing of the StaffDocument.
     */
    public function index(Request $request)
    {
        $staffDocuments = $this->staffDocumentRepository->with(['staff'])->paginate(10);

        return view('staff_documents.index')
            ->with('staffDocuments', $staffDocuments);
    }

    /**
     * Show the form for creating a new StaffDocument.
     */
    public function create()
    {
        $dropdownData = $this->getDropdownData();
        return view('staff_documents.create',  $dropdownData);
    }

    /**
     * Store a newly created StaffDocument in storage.
     */
    public function store(CreateStaffDocumentRequest $request)
    {
        $input = $request->all();

        // Handle file upload
        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $input['file_path'] = $file->storeAs('staff_documents', $fileName, 'public');

            // If document name is empty, use the original filename
            if (empty($input['document_name'])) {
                $input['document_name'] = $file->getClientOriginalName();
            }
        }

        // Set uploaded_at to current timestamp if not provided
        if (empty($input['uploaded_at'])) {
            $input['uploaded_at'] = now();
        }

        $staffDocument = $this->staffDocumentRepository->create($input);

        AuditTrail::log('Staff Document', 'CREATE', $staffDocument->document_id, null, $staffDocument->toArray());

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

        $dropdownData = $this->getDropdownData();
        return view('staff_documents.edit', array_merge([
            'staffDocument'=> $staffDocument,
            'selectedStaff'=> optional($staffDocument->staff),
        ], $dropdownData));
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

        $oldData = $staffDocument->toArray();

        $input = $request->all();

        // Handle file upload if a new file is provided
        if ($request->hasFile('document_file')) {
            // Delete old file if it exists
            if ($staffDocument->file_path && Storage::disk('public')->exists($staffDocument->file_path)) {
                Storage::disk('public')->delete($staffDocument->file_path);
            }

            $file = $request->file('document_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $input['file_path'] = $file->storeAs('staff_documents', $fileName, 'public');

            // Update document name if it was not provided
            if (empty($input['document_name'])) {
                $input['document_name'] = $file->getClientOriginalName();
            }
        }

        $staffDocument = $this->staffDocumentRepository->update($input, $id);

        AuditTrail::log('Staff Document', 'UPDATE', $staffDocument->document_id, $oldData, $staffDocument->toArray());

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

        $oldData = $staffDocument->toArray();

        // Delete the associated file
        if ($staffDocument->file_path && Storage::disk('public')->exists($staffDocument->file_path)) {
            Storage::disk('public')->delete($staffDocument->file_path);
        }

        $this->staffDocumentRepository->delete($id);

        AuditTrail::log('Staff Document', 'DELETE', $id, $oldData, null);

        Flash::success('Staff Document deleted successfully.');

        return redirect(route('staffDocuments.index'));
    }

    /**
     * Download the document file
     */
    public function download($id)
    {
        $staffDocument = $this->staffDocumentRepository->find($id);

        if (empty($staffDocument) || !$staffDocument->file_path) {
            Flash::error('Document file not found');

            return redirect(route('staffDocuments.index'));
        }

        if (!Storage::disk('public')->exists($staffDocument->file_path)) {
            Flash::error('Document file not found on server');

            return redirect(route('staffDocuments.index'));
        }

        return Storage::disk('public')->download($staffDocument->file_path, $staffDocument->document_name);
    }
}
