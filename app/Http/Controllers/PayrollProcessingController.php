<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use App\Models\StaffAllowance;
use App\Models\StaffDeduction;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class PayrollProcessingController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:hr.view')->only(['index', 'show']);
        $this->middleware('can:hr.manage')->only(['create', 'calculate', 'store', 'review', 'finalize']);
    }

    public function index()
    {
        // Payroll wizard plus the list of already-processed staff payslips.
        $payrolls = \App\Models\Payroll::with('staff')->latest('payroll_id')->paginate(15);

        return view('hr.payroll.index', compact('payrolls'));
    }

    public function create()
    {
        $staff = Staff::where('employment_status', 'active')
            ->with(['department', 'jobPosition', 'allowances', 'deductions'])
            ->get();

        return view('hr.payroll.create', compact('staff'));
    }

    public function calculate(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        $staff = Staff::where('employment_status', 'active')
            ->with(['allowances', 'deductions'])
            ->get();

        $payrollData = [];

        foreach ($staff as $employee) {
            $basicSalary = $employee->basic_salary ?? 0;
            $totalAllowances = $employee->allowances->sum('amount');
            $grossSalary = $basicSalary + $totalAllowances;

            // Calculate PAYE (Kenya Tax Rates 2024)
            $paye = $this->calculatePAYE($grossSalary);
            
            // NHIF (Kenya Rates)
            $nhif = $this->calculateNHIF($grossSalary);
            
            // NSSF (Kenya Rates - Tier I & II)
            $nssf = $this->calculateNSSF($grossSalary);

            // Other deductions
            $otherDeductions = $employee->deductions->sum('monthly_amount');

            $totalDeductions = $paye + $nhif + $nssf + $otherDeductions;
            $netSalary = $grossSalary - $totalDeductions;

            $payrollData[] = [
                'staff_id' => $employee->staff_id,
                'staff_name' => $employee->full_name,
                'employee_number' => $employee->employee_number,
                'basic_salary' => $basicSalary,
                'allowances' => $totalAllowances,
                'gross_salary' => $grossSalary,
                'paye' => $paye,
                'nhif' => $nhif,
                'nssf' => $nssf,
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => $netSalary,
            ];
        }

        return view('hr.payroll.review', compact('payrollData', 'request'));
    }

    public function review($payrollId)
    {
        // This route never had the data the review view needs: the view
        // renders $payrollData and $request from the calculate() step, so
        // hitting /review/{id} directly threw an undefined-variable 500.
        // There is no persisted payroll to review yet (finalize writes
        // nothing), so send the user back to the wizard that produces the
        // breakdown instead of rendering a view that cannot work.
        Flash::info('The review screen only shows the wizard\'s calculation — start there to see the breakdown.');
        return redirect()->route('payroll-processing.create');
    }

    public function finalize(Request $request, $payrollId)
    {
        // The wizard's last step never did anything: it opened a transaction,
        // committed an empty body, and flashed "Payroll processed successfully"
        // — so staff appeared paid while no payroll row, expense or payslip
        // was ever written. It stays gated behind hr.manage (above) and now
        // says plainly that the step is not wired up yet, rather than
        // reporting a success that never happened.
        Flash::error('Payroll finalisation is not available yet — nothing was written. Process salaries from the HR payroll screen instead.');
        return redirect()->route('payroll-processing.index');
    }

    // Kenya PAYE Calculation (2024 Rates)
    private function calculatePAYE($grossSalary)
    {
        $taxableIncome = $grossSalary;
        $paye = 0;

        if ($taxableIncome <= 24000) {
            $paye = $taxableIncome * 0.10;
        } elseif ($taxableIncome <= 32333) {
            $paye = 2400 + (($taxableIncome - 24000) * 0.25);
        } elseif ($taxableIncome <= 500000) {
            $paye = 2400 + 2083.25 + (($taxableIncome - 32333) * 0.30);
        } elseif ($taxableIncome <= 800000) {
            $paye = 2400 + 2083.25 + 140300.10 + (($taxableIncome - 500000) * 0.325);
        } else {
            $paye = 2400 + 2083.25 + 140300.10 + 97500 + (($taxableIncome - 800000) * 0.35);
        }

        // Personal Relief
        $paye = max(0, $paye - 2400);

        return round($paye, 2);
    }

    // Kenya NHIF Calculation
    private function calculateNHIF($grossSalary)
    {
        if ($grossSalary <= 5999) return 150;
        if ($grossSalary <= 7999) return 300;
        if ($grossSalary <= 11999) return 400;
        if ($grossSalary <= 14999) return 500;
        if ($grossSalary <= 19999) return 600;
        if ($grossSalary <= 24999) return 750;
        if ($grossSalary <= 29999) return 850;
        if ($grossSalary <= 34999) return 900;
        if ($grossSalary <= 39999) return 950;
        if ($grossSalary <= 44999) return 1000;
        if ($grossSalary <= 49999) return 1100;
        if ($grossSalary <= 59999) return 1200;
        if ($grossSalary <= 69999) return 1300;
        if ($grossSalary <= 79999) return 1400;
        if ($grossSalary <= 89999) return 1500;
        if ($grossSalary <= 99999) return 1600;
        return 1700;
    }

    // Kenya NSSF Calculation (Tier I & II)
    private function calculateNSSF($grossSalary)
    {
        $tier1Limit = 7000;
        $tier2Limit = 36000;
        $rate = 0.06;

        $tier1 = min($grossSalary, $tier1Limit) * $rate;
        $tier2 = 0;

        if ($grossSalary > $tier1Limit) {
            $tier2 = min($grossSalary - $tier1Limit, $tier2Limit - $tier1Limit) * $rate;
        }

        return round($tier1 + $tier2, 2);
    }
}
