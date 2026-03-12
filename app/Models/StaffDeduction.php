<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDeduction extends Model
{
    protected $table = 'staff_deductions';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'deduction_name',
        'deduction_type',
        'monthly_amount',
        'total_amount',
        'balance',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'monthly_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
