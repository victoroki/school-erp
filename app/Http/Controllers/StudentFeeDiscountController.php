<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateStudentFeeDiscountRequest;
use App\Http\Requests\UpdateStudentFeeDiscountRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\StudentFeeDiscountRepository;
use Illuminate\Http\Request;
use Flash;

class StudentFeeDiscountController extends AppBaseController
{
    /** @var StudentFeeDiscountRepository $studentFeeDiscountRepository*/
    private $studentFeeDiscountRepository;

    public function __construct(StudentFeeDiscountRepository $studentFeeDiscountRepo)
    {
        $this->studentFeeDiscountRepository = $studentFeeDiscountRepo;
    }

    /**
     * Display a listing of the StudentFeeDiscount.
     */
    public function index(Request $request)
    {
        $studentFeeDiscounts = $this->studentFeeDiscountRepository->paginate(10);

        return view('student_fee_discounts.index')
            ->with('studentFeeDiscounts', $studentFeeDiscounts);
    }

    /**
     * Show the form for creating a new StudentFeeDiscount.
     */
    public function create()
    {
        return view('student_fee_discounts.create');
    }

    /**
     * Store a newly created StudentFeeDiscount in storage.
     */
    public function store(CreateStudentFeeDiscountRequest $request)
    {
        $input = $request->all();

        $studentFeeDiscount = $this->studentFeeDiscountRepository->create($input);

        Flash::success('Student Fee Discount saved successfully.');

        return redirect(route('studentFeeDiscounts.index'));
    }

    /**
     * Display the specified StudentFeeDiscount.
     */
    public function show($id)
    {
        $studentFeeDiscount = $this->studentFeeDiscountRepository->find($id);

        if (empty($studentFeeDiscount)) {
            Flash::error('Student Fee Discount not found');

            return redirect(route('studentFeeDiscounts.index'));
        }

        return view('student_fee_discounts.show')->with('studentFeeDiscount', $studentFeeDiscount);
    }

    /**
     * Show the form for editing the specified StudentFeeDiscount.
     */
    public function edit($id)
    {
        $studentFeeDiscount = $this->studentFeeDiscountRepository->find($id);

        if (empty($studentFeeDiscount)) {
            Flash::error('Student Fee Discount not found');

            return redirect(route('studentFeeDiscounts.index'));
        }

        return view('student_fee_discounts.edit')->with('studentFeeDiscount', $studentFeeDiscount);
    }

    /**
     * Update the specified StudentFeeDiscount in storage.
     */
    public function update($id, UpdateStudentFeeDiscountRequest $request)
    {
        $studentFeeDiscount = $this->studentFeeDiscountRepository->find($id);

        if (empty($studentFeeDiscount)) {
            Flash::error('Student Fee Discount not found');

            return redirect(route('studentFeeDiscounts.index'));
        }

        $studentFeeDiscount = $this->studentFeeDiscountRepository->update($request->all(), $id);

        Flash::success('Student Fee Discount updated successfully.');

        return redirect(route('studentFeeDiscounts.index'));
    }

    /**
     * Remove the specified StudentFeeDiscount from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $studentFeeDiscount = $this->studentFeeDiscountRepository->find($id);

        if (empty($studentFeeDiscount)) {
            Flash::error('Student Fee Discount not found');

            return redirect(route('studentFeeDiscounts.index'));
        }

        $this->studentFeeDiscountRepository->delete($id);

        Flash::success('Student Fee Discount deleted successfully.');

        return redirect(route('studentFeeDiscounts.index'));
    }
}
