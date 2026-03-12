<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    protected $table = 'leave_applications';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'working_days',
        'reason',
        'relief_staff_id',
        'handover_notes',
        'supporting_document',
        'application_status',
        'submitted_date',
        'hod_approval_status',
        'hod_approved_by',
        'hod_approval_date',
        'hod_comments',
        'hr_approval_status',
        'hr_approved_by',
        'hr_approval_date',
        'hr_comments',
        'final_status',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_date' => 'datetime',
        'hod_approval_date' => 'datetime',
        'hr_approval_date' => 'datetime',
        'working_days' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id', 'leave_type_id');
    }

    public function reliefStaff()
    {
        return $this->belongsTo(Staff::class, 'relief_staff_id', 'staff_id');
    }
}
