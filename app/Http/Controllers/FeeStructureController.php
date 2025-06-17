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

class FeeStructureController extends AppBaseController
{
    /** @var FeeStructureRepository $feeStructureRepository*/
    private $feeStructureRepository;

    public function __construct(FeeStructureRepository $feeStructureRepo)
    {
        $this->feeStructureRepository = $feeStructureRepo;
    }

    /**
     * Display a listing of the FeeStructure.
     */
    public function index(Request $request)
    {
        $feeStructures = $this->feeStructureRepository->paginate(10);

        return view('fee_structures.index')
            ->with('feeStructures', $feeStructures);
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

        $feeStructure = $this->feeStructureRepository->create($input);

        Flash::success('Fee Structure saved successfully.');

        return redirect(route('feeStructures.index'));
    }

    /**
     * Display the specified FeeStructure.
     */
    public function show($id)
    {
        $feeStructure = $this->feeStructureRepository->find($id);

        if (empty($feeStructure)) {
            Flash::error('Fee Structure not found');

            return redirect(route('feeStructures.index'));
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

            return redirect(route('feeStructures.index'));
        }

        return view('fee_structures.edit', array_merge([
            'feeStructure', $feeStructure,
            $dropdownData
        ]

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

            return redirect(route('feeStructures.index'));
        }

        $feeStructure = $this->feeStructureRepository->update($request->all(), $id);

        Flash::success('Fee Structure updated successfully.');

        return redirect(route('feeStructures.index'));
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

            return redirect(route('feeStructures.index'));
        }

        $this->feeStructureRepository->delete($id);

        Flash::success('Fee Structure deleted successfully.');

        return redirect(route('feeStructures.index'));
    }
}
