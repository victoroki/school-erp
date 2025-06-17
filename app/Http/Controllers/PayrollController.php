<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePayrollRequest;
use App\Http\Requests\UpdatePayrollRequest;
use App\Http\Controllers\AppBaseController;
use App\Repositories\PayrollRepository;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
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
        // Get data for dropdowns
        $staff = Staff::selectRaw("staff_id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, ' (', employee_id, ')') as full_name")
                     ->where('status', 'active')
                     ->pluck('full_name', 'staff_id')
                     ->prepend('Select Staff Member', '');
        
        $salaries = \DB::table('staff_salary')
                      ->join('staff', 'staff_salary.staff_id', '=', 'staff.staff_id')
                      ->selectRaw("staff_salary.salary_id, CONCAT(staff.first_name, ' ', staff.last_name, ' - ₹', staff_salary.basic_salary) as salary_info")
                      ->pluck('salary_info', 'salary_id')
                      ->prepend('Select Salary Structure', '');
        
        // Generate month options
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }
        
        // Generate year options (current year - 5 to current year + 2)
        $currentYear = date('Y');
        $years = [];
        for ($i = $currentYear - 5; $i <= $currentYear + 2; $i++) {
            $years[$i] = $i;
        }
        
        // Payment method options
        $paymentMethods = [
            '' => 'Select Payment Method',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'mobile_money' => 'Mobile Money'
        ];
        
        // Status options
        $statusOptions = [
            '' => 'Select Status',
            'pending' => 'Pending',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            'processing' => 'Processing'
        ];

        // Get staff with their current salaries for auto-population
        $staffWithSalaries = Staff::with(['currentSalary' => function($query) {
                                $query->latest('effective_from');
                            }])
                            ->where('status', 'active')
                            ->get()
                            ->map(function($staff) {
                                return [
                                    'staff_id' => $staff->staff_id,
                                    'name' => trim($staff->first_name . ' ' . ($staff->middle_name ?? '') . ' ' . $staff->last_name) . ' (' . $staff->employee_id . ')',
                                    'current_salary' => $staff->currentSalary ? [
                                        'salary_id' => $staff->currentSalary->salary_id,
                                        'basic_salary' => $staff->currentSalary->basic_salary,
                                        'allowances' => $staff->currentSalary->allowances,
                                        'deductions' => $staff->currentSalary->deductions,
                                    ] : null
                                ];
                            });

        return view('payrolls.create', compact('staff', 'salaries', 'months', 'years', 'paymentMethods', 'statusOptions', 'staffWithSalaries'));
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

        // Get data for dropdowns (same as create method)
        $staff = Staff::selectRaw("staff_id, CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name, ' (', employee_id, ')') as full_name")
                     ->where('status', 'active')
                     ->pluck('full_name', 'staff_id')
                     ->prepend('Select Staff Member', '');
        
        $salaries = \DB::table('staff_salaries')
                      ->join('staff', 'staff_salaries.staff_id', '=', 'staff.staff_id')
                      ->selectRaw("staff_salaries.salary_id, CONCAT(staff.first_name, ' ', staff.last_name, ' - ₹', staff_salaries.basic_salary) as salary_info")
                      ->pluck('salary_info', 'salary_id')
                      ->prepend('Select Salary Structure', '');
        
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = date('F', mktime(0, 0, 0, $i, 1));
        }
        
        $currentYear = date('Y');
        $years = [];
        for ($i = $currentYear - 5; $i <= $currentYear + 2; $i++) {
            $years[$i] = $i;
        }
        
        $paymentMethods = [
            '' => 'Select Payment Method',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'cheque' => 'Cheque',
            'mobile_money' => 'Mobile Money'
        ];
        
        $statusOptions = [
            '' => 'Select Status',
            'pending' => 'Pending',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            'processing' => 'Processing'
        ];

        // Get staff with their current salaries for auto-population
        $staffWithSalaries = Staff::with(['currentSalary' => function($query) {
                                $query->latest('effective_from');
                            }])
                            ->where('status', 'active')
                            ->get()
                            ->map(function($staff) {
                                return [
                                    'staff_id' => $staff->staff_id,
                                    'name' => trim($staff->first_name . ' ' . ($staff->middle_name ?? '') . ' ' . $staff->last_name) . ' (' . $staff->employee_id . ')',
                                    'current_salary' => $staff->currentSalary ? [
                                        'salary_id' => $staff->currentSalary->salary_id,
                                        'basic_salary' => $staff->currentSalary->basic_salary,
                                        'allowances' => $staff->currentSalary->allowances,
                                        'deductions' => $staff->currentSalary->deductions,
                                    ] : null
                                ];
                            });

        return view('payrolls.edit', compact('payroll', 'staff', 'salaries', 'months', 'years', 'paymentMethods', 'statusOptions', 'staffWithSalaries'));
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