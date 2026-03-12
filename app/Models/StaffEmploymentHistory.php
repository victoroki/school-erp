<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffEmploymentHistory extends Model
{
    protected $table = 'staff_employment_history';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'employer_name',
        'job_title',
        'start_date',
        'end_date',
        'responsibilities',
        'reason_for_leaving',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
