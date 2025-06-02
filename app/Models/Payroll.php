<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    public $table = 'payroll';

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
        'status'
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
        'status' => 'string'
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
