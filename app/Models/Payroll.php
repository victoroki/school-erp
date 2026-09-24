<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A per-employee payslip row.
 *
 * The 2026-02-07 HR revamp renamed the legacy `payroll` table to
 * `payroll_details`; the columns kept their names, so this model now reads
 * from `payroll_details` with `payroll_id` as the primary key. Querying the
 * old `payroll` table name throws "table doesn't exist" on the live DB.
 *
 * Run-level totals for a whole month live in the separate `payrolls` table.
 */
class Payroll extends Model
{
    public $table = 'payroll_details';

    protected $primaryKey = 'payroll_id';

    public $fillable = [
        'staff_id',
        'salary_id',
        'month',
        'year',
        'working_days',
        'paid_days',
        'absent_days',
        'leave_days',
        'basic_salary',
        'allowances',
        'overtime',
        'gross_salary',
        'deductions',
        'net_salary',
        'payment_date',
        'payment_method',
        'reference_number',
        'remarks',
        'status',
        'payroll_id',
        'total_allowances',
        'paye_tax',
        'nhif_deduction',
        'nssf_deduction',
        'total_statutory_deductions',
        'total_other_deductions',
        'overtime_pay',
        'bonus',
        'arrears',
        'payslip_sent',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'overtime' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'payment_date' => 'date',
        'payment_method' => 'string',
        'reference_number' => 'string',
        'remarks' => 'string',
        'status' => 'string',
        'paye_tax' => 'decimal:2',
        'nhif_deduction' => 'decimal:2',
        'nssf_deduction' => 'decimal:2',
        'payslip_sent' => 'boolean',
    ];

    public static array $rules = [
        'staff_id' => 'nullable',
        'salary_id' => 'nullable',
        'month' => 'required',
        'year' => 'required',
        'working_days' => 'required',
        'paid_days' => 'required',
        'absent_days' => 'required',
        'leave_days' => 'required',
        'basic_salary' => 'required|numeric',
        'allowances' => 'nullable|numeric',
        'overtime' => 'nullable|numeric',
        'gross_salary' => 'required|numeric',
        'deductions' => 'nullable|numeric',
        'net_salary' => 'required|numeric',
        'payment_date' => 'nullable',
        'payment_method' => 'required|string',
        'reference_number' => 'nullable|string|max:100',
        'remarks' => 'nullable|string|max:65535',
        'status' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function salary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\StaffSalary::class, 'salary_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }
}
