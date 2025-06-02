<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\PayrollRepository;
use Illuminate\Http\Request;
use Flash;

class PayrollController extends AppBaseController
{
    /** @var PayrollRepository $payrollRepository*/
    private $payrollRepository;

    public function __construct(PayrollRepository $payrollRepo)
    {
        $this->payrollRepository = $payrollRepo;
    }

    /**
     * Display a listing of the Payroll.
     */
    public function index(Request $request)
    {
        $payrolls = $this->payrollRepository->paginate(10);

        return view('payrolls.index')
            ->with('payrolls', $payrolls);
    }

    /**
     * Show the form for creating a new Payroll.
     */
    public function create()
    {
        return view('payrolls.create');
    }

    /**
     * Store a newly created Payroll in storage.
     */
    public function store(CreatePayrollRequest $request)
    {
        $input = $request->all();

        $payroll = $this->payrollRepository->create($input);

        Flash::success('Payroll saved successfully.');

        return redirect(route('payrolls.index'));
    }

    /**
     * Display the specified Payroll.
     */
    public function show($id)
    {
        $payroll = $this->payrollRepository->find($id);

        if (empty($payroll)) {
            Flash::error('Payroll not found');

            return redirect(route('payrolls.index'));
        }

        return view('payrolls.show')->with('payroll', $payroll);
    }

    /**
     * Show the form for editing the specified Payroll.
     */
    public function edit($id)
    {
        $payroll = $this->payrollRepository->find($id);

        if (empty($payroll)) {
            Flash::error('Payroll not found');

            return redirect(route('payrolls.index'));
        }

        return view('payrolls.edit')->with('payroll', $payroll);
    }

    /**
     * Update the specified Payroll in storage.
     */
    public function update($id, UpdatePayrollRequest $request)
    {
        $payroll = $this->payrollRepository->find($id);

        if (empty($payroll)) {
            Flash::error('Payroll not found');

            return redirect(route('payrolls.index'));
        }

        $payroll = $this->payrollRepository->update($request->all(), $id);

        Flash::success('Payroll updated successfully.');

        return redirect(route('payrolls.index'));
    }

    /**
     * Remove the specified Payroll from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $payroll = $this->payrollRepository->find($id);

        if (empty($payroll)) {
            Flash::error('Payroll not found');

            return redirect(route('payrolls.index'));
        }

        $this->payrollRepository->delete($id);

        Flash::success('Payroll deleted successfully.');

        return redirect(route('payrolls.index'));
    }
}
