<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A per-employee payslip row (payroll_details table).
 *
 * The 2026-02-07 HR revamp renamed the legacy `payroll` table to
 * `payroll_details` and added the Kenyan statutory columns (PAYE, NHIF,
 * NSSF). Staff::payrollDetails() and Payroll::details() reference this
 * model; the class was missing, which would fatal on any query that
 * resolved those relations.
 */
class PayrollDetail extends Model
{
    public $table = 'payroll_details';

    protected $primaryKey = 'payroll_id';

    public $fillable = [
        'payroll_id',
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
        'paye_tax' => 'decimal:2',
        'nhif_deduction' => 'decimal:2',
        'nssf_deduction' => 'decimal:2',
        'total_statutory_deductions' => 'decimal:2',
        'total_other_deductions' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'bonus' => 'decimal:2',
        'arrears' => 'decimal:2',
        'payslip_sent' => 'boolean',
        'month' => 'integer',
        'year' => 'integer',
        'working_days' => 'integer',
        'paid_days' => 'integer',
        'absent_days' => 'integer',
        'leave_days' => 'integer',
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id', 'staff_id');
    }

    public function salary(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\StaffSalary::class, 'salary_id');
    }
}
