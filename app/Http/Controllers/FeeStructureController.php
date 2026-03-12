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
use Illuminate\Support\Facades\DB;

class FeeStructureController extends AppBaseController
{
    /** @var FeeStructureRepository $feeStructureRepository*/
    protected $financeService;

    public function __construct(FeeStructureRepository $feeStructureRepo, \App\Services\FinanceService $financeService)
    {
        $this->feeStructureRepository = $feeStructureRepo;
        $this->financeService = $financeService;
    }

    /**
     * Display a listing of the FeeStructure.
     */
    public function index(Request $request)
    {
        $query = $this->feeStructureRepository->allQuery()
            ->with(['academicYear', 'schoolClass', 'category'])
            ->withCount('assignments');

        if ($request->has('class_id') && $request->class_id != '') {
            $query->where('class_id', $request->class_id);
        }

        if ($request->has('academic_year_id') && $request->academic_year_id != '') {
            $query->where('academic_year_id', $request->academic_year_id);
        }

        $feeStructures = $query->paginate(10);
        $classes = SchoolClass::pluck('name', 'class_id');
        $academicYears = AcademicYear::pluck('name', 'academic_year_id');

        return view('fee_structures.index')
            ->with('feeStructures', $feeStructures)
            ->with('classes', $classes)
            ->with('academicYears', $academicYears);
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
            $feeStructure = $this->feeStructureRepository->create($input);

            if ($request->has('auto_assign') && $request->auto_assign == 1) {
                $count = $this->financeService->batchAssignFee($feeStructure->fee_structure_id, $feeStructure->class_id);
                Flash::success("Fee Structure saved and assigned to $count students successfully.");
            } else {
                Flash::success('Fee Structure saved successfully.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
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

        return view('fee_structures.show')->with('feeStructure', $feeStructure);
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

        $feeStructure = $this->feeStructureRepository->update($request->all(), $id);

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

        $this->feeStructureRepository->delete($id);

        Flash::success('Fee Structure deleted successfully.');

        return redirect(route('fee-structures.index'));
    }
}
