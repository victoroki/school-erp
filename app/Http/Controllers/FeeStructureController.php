<?php

namespace App\Http\Controllers;

use Flash;
use App\Models\SchoolClass;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use App\Http\Controllers\AppBaseController;
use App\Repositories\FeeStructureRepository;
use App\Http\Requests\CreateFeeStructureRequest;
use App\Http\Requests\UpdateFeeStructureRequest;
use App\Models\FeeCategory;
use App\Models\AuditTrail;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class FeeStructureController extends AppBaseController
{
    /** @var FeeStructureRepository $feeStructureRepository*/
    protected $financeService;

    public function __construct(FeeStructureRepository $feeStructureRepo, \App\Services\FinanceService $financeService)
    {
        $this->feeStructureRepository = $feeStructureRepo;
        $this->financeService = $financeService;
        $this->middleware('can:fees.view')->only(['index', 'show']);
        $this->middleware('can:fees.manage')->only(['create', 'store', 'edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the FeeStructure.
     */
    public function index(Request $request)
    {
        $currentYear = AcademicYear::where('is_current', true)->first();
        
        // Always ensure a year is selected. Default to current if none provided or if empty.
        $selectedYearId = $request->get('academic_year_id') ?: ($currentYear?->academic_year_id ?? null);

        $query = $this->feeStructureRepository->allQuery()
            ->with(['academicYear', 'schoolClass', 'category'])
            ->withCount('assignments');

        if ($selectedYearId) {
            $query->where('academic_year_id', $selectedYearId);
        }

        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }

        $feeStructures = $query->paginate(50);
        $classes = SchoolClass::pluck('name', 'class_id');

        $years = AcademicYear::orderBy('start_date', 'desc')->get();
        $academicYearsDropdown = $years->pluck('name', 'academic_year_id');
        
        // Important: find the specific year object for the UI
        $selectedYear = $years->find($selectedYearId);

        $yearNavigation = $this->getYearNavigation($years, $selectedYearId);

        return view('fee_structures.index')
            ->with('feeStructures', $feeStructures)
            ->with('classes', $classes)
            ->with('academicYears', $academicYearsDropdown)
            ->with('selectedYear', $selectedYear)
            ->with('yearNavigation', $yearNavigation);
    }

    /**
     * Get previous and next academic years for navigation.
     */
    private function getYearNavigation($academicYears, $selectedYearId)
    {
        $years = $academicYears->sortBy('start_date')->values();
        $currentIndex = $years->search(fn($year) => $year->academic_year_id == $selectedYearId);

        return [
            'previous' => $currentIndex > 0 ? $years[$currentIndex - 1] : null,
            'next' => $currentIndex < $years->count() - 1 ? $years[$currentIndex + 1] : null,
            'current' => $years[$currentIndex] ?? null,
        ];
    }

    private function getdropdownData(){
        return[
            'academicYear' => AcademicYear::pluck('name', 'academic_year_id'),
            'classes' => SchoolClass::pluck('name', 'class_id'),
            'category' => FeeCategory::pluck('name', 'category_id')
        ];
    }

    /**
     * Show the form for creating a new FeeStructure.
     */
    public function create()
    {
        $dropdownData = $this->getdropdownData();
        return view('fee_structures.create', $dropdownData);
    }

    /**
     * Store a newly created FeeStructure in storage.
     */
    public function store(CreateFeeStructureRequest $request)
    {
        $input = $request->all();

        DB::beginTransaction();
        try {
            \Log::info('Fee Structure Create - Input:', $input);

            $feeStructure = $this->feeStructureRepository->create($input);

            \Log::info('Fee Structure Created:', ['id' => $feeStructure->fee_structure_id, 'class_id' => $feeStructure->class_id]);

            // Audit Log
            AuditTrail::log('Fee Structure', 'CREATE', $feeStructure->fee_structure_id, null, $feeStructure->toArray());

            if ($request->has('auto_assign') && $request->boolean('auto_assign')) {
                \Log::info('Auto-assign checked, calling batchAssignFee');
                $count = $this->financeService->batchAssignFee($feeStructure->fee_structure_id, $feeStructure->class_id);
                \Log::info('Auto-assign completed:', ['count' => $count]);
                Flash::success("Fee Structure saved and assigned to $count students successfully.");
            } else {
                \Log::info('Auto-assign not checked');
                Flash::success('Fee Structure saved successfully.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error saving fee structure:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            Flash::error('Error saving fee structure: ' . $e->getMessage());
            return redirect()->back()->withInput();
        }

        return redirect(route('fee-structures.index'));
    }

    /**
     * Display the specified FeeStructure.
     */
    public function show($id)
    {
        $feeStructure = $this->feeStructureRepository->find($id);

        if (empty($feeStructure)) {
            Flash::error('Fee Structure not found');

            return redirect(route('fee-structures.index'));
        }

        $relatedFees = \App\Models\FeeStructure::where('class_id', $feeStructure->class_id)
            ->where('academic_year_id', $feeStructure->academic_year_id)
            ->with(['category'])
            ->get();

        return view('fee_structures.show', compact('feeStructure', 'relatedFees'));
    }

    /**
     * Show the form for editing the specified FeeStructure.
     */
    public function edit($id)
    {
        $feeStructure = $this->feeStructureRepository->find($id);
        $dropdownData = $this->getdropdownData();

        if (empty($feeStructure)) {
            Flash::error('Fee Structure not found');

            return redirect(route('fee-structures.index'));
        }

        return view('fee_structures.edit', array_merge(
            ['feeStructure' => $feeStructure],
            $dropdownData
        ));
    }

    /**
     * Update the specified FeeStructure in storage.
     */
    public function update($id, UpdateFeeStructureRequest $request)
    {
        $feeStructure = $this->feeStructureRepository->find($id);

        if (empty($feeStructure)) {
            Flash::error('Fee Structure not found');

            return redirect(route('fee-structures.index'));
        }

        $oldData = $feeStructure->toArray();
        $feeStructure = $this->feeStructureRepository->update($request->all(), $id);

        // Audit Log
        AuditTrail::log('Fee Structure', 'UPDATE', $feeStructure->fee_structure_id, $oldData, $feeStructure->toArray());

        Flash::success('Fee Structure updated successfully.');

        return redirect(route('fee-structures.index'));
    }

    /**
     * Remove the specified FeeStructure from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $feeStructure = $this->feeStructureRepository->find($id);

        if (empty($feeStructure)) {
            Flash::error('Fee Structure not found');

            return redirect(route('fee-structures.index'));
        }

        $oldData = $feeStructure->toArray();
        $this->feeStructureRepository->delete($id);

        // Audit Log
        AuditTrail::log('Fee Structure', 'DELETE', $id, $oldData, null);

        Flash::success('Fee Structure deleted successfully.');

        return redirect(route('fee-structures.index'));
    }
}
