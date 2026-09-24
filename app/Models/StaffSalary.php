<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffSalary extends Model
{
    public $table = 'staff_salary';

    public $timestamps = false;

    protected $primaryKey = 'salary_id';

    public $fillable = [
        'staff_id',
        'basic_salary',
        'allowances',
        'deductions',
        'net_salary',
        'effective_from',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'decimal:2',
        'deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id', 'staff_id');
    }
}
