<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffExitClearance extends Model
{
    protected $table = 'staff_exit_clearances';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'exit_type',
        'exit_date',
        'reason',
        'notice_period_days',
        'final_working_date',
        'clearance_status',
        'final_settlement_amount',
        'settlement_paid',
        'settlement_date',
    ];

    protected $casts = [
        'exit_date' => 'date',
        'final_working_date' => 'date',
        'settlement_date' => 'date',
        'notice_period_days' => 'integer',
        'final_settlement_amount' => 'decimal:2',
        'settlement_paid' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
