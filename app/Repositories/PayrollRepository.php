<?php

namespace App\Repositories;

use App\Models\Payroll;
use App\Repositories\BaseRepository;

class PayrollRepository extends BaseRepository
{
    protected $fieldSearchable = [
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

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Payroll::class;
    }
}
