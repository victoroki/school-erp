<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use App\Models\Staff;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Laracasts\Flash\Flash;

class PayrollProcessingController extends Controller
{
    public function __construct(private \App\Services\PayrollCalculator $calculator)
    {
        $this->middleware('can:hr.view')->only(['index', 'show']);
        $this->middleware('can:hr.manage')->only(['create', 'calculate', 'store', 'review', 'finalize']);
    }

    public function index()
    {
        // Payroll wizard plus the list of already-processed staff payslips.
        $payrolls = Payroll::with('staff')->latest('payroll_id')->paginate(15);

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
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        $period = Carbon::create(
            $validated['year'],
            $validated['month'],
            1
        )->startOfMonth();

        $staff = Staff::where('employment_status', 'active')
            ->with(['allowances', 'deductions'])
            ->get();

        $payrollData = [];
        $totals = [
            'staff' => $staff->count(),
            'basic_salary' => 0.0,
            'allowances' => 0.0,
            'gross_salary' => 0.0,
            'paye' => 0.0,
            'shif_employee' => 0.0,
            'shif_employer' => 0.0,
            'nssf_employee' => 0.0,
            'nssf_employer' => 0.0,
            'other_deductions' => 0.0,
            'total_deductions' => 0.0,
            'net_salary' => 0.0,
        ];

        foreach ($staff as $employee) {
            // basic_salary is cast decimal:2, so it arrives as a string. The old
            // code relied on PHP coercing it in arithmetic, which left the
            // running total as a float and lost the currency-scale rounding.
            $basicSalary = (float) ($employee->basic_salary ?? 0);
            $totalAllowances = (float) $employee->allowances->sum('amount');
            $grossSalary = $basicSalary + $totalAllowances;

            $statutory = $this->calculator->statutory($grossSalary);

            $otherDeductions = (float) $employee->deductions->sum('monthly_amount');

            $totalDeductions = round(
                $statutory['paye']
                + $statutory['shif_employee']
                + $statutory['nssf_employee']
                + $otherDeductions,
                2
            );

            $payrollData[] = [
                'staff_id' => $employee->staff_id,
                'staff_name' => $employee->full_name,
                'employee_number' => $employee->employee_number,
                'basic_salary' => $basicSalary,
                'allowances' => $totalAllowances,
                'gross_salary' => $grossSalary,
                'paye' => $statutory['paye'],
                // NHIF became SHIF under the Social Health Insurance Act 2023,
                // administered by the Social Health Authority. The old figure was
                // a flat KES 150-1,700 assessment with no relation to earnings;
                // SHIF is a percentage of gross, so this column is not the same
                // number renamed.
                'shif_employee' => $statutory['shif_employee'],
                'shif_employer' => $statutory['shif_employer'],
                'nssf_employee' => $statutory['nssf_employee'],
                'nssf_employer' => $statutory['nssf_employer'],
                'other_deductions' => $otherDeductions,
                'total_deductions' => $totalDeductions,
                'net_salary' => round($grossSalary - $totalDeductions, 2),
            ];

            $totals['basic_salary'] += $basicSalary;
            $totals['allowances'] += $totalAllowances;
            $totals['gross_salary'] += $grossSalary;
            $totals['paye'] += $statutory['paye'];
            $totals['shif_employee'] += $statutory['shif_employee'];
            $totals['shif_employer'] += $statutory['shif_employer'];
            $totals['nssf_employee'] += $statutory['nssf_employee'];
            $totals['nssf_employer'] += $statutory['nssf_employer'];
            $totals['other_deductions'] += $otherDeductions;
            $totals['total_deductions'] += $totalDeductions;
            $totals['net_salary'] += $grossSalary - $totalDeductions;
        }

        // Money totals are rounded once, at the end, rather than relying on the
        // sum of individually rounded rows landing on the same figure.
        foreach ($totals as $key => $value) {
            if ($key !== 'staff') {
                $totals[$key] = round($value, 2);
            }
        }

        $totals['statutory_employer'] = round(
            $totals['shif_employer'] + $totals['nssf_employer'],
            2
        );

        return view('hr.payroll.review', [
            'payrollData' => $payrollData,
            'totals' => $totals,
            'period' => $period,
            'rates' => $this->calculator->ratesSummary(),
        ]);
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
}
