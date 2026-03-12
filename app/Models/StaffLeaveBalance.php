<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffLeaveBalance extends Model
{
    protected $table = 'staff_leave_balances';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'academic_year_id',
        'total_available',
        'used',
        'remaining',
        'carried_forward',
    ];

    protected $casts = [
        'total_available' => 'integer',
        'used' => 'integer',
        'remaining' => 'integer',
        'carried_forward' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }
}
