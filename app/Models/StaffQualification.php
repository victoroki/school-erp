<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffQualification extends Model
{
    protected $table = 'staff_qualifications';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'qualification_type',
        'qualification_name',
        'institution',
        'year_obtained',
        'grade_obtained',
        'certificate_url',
    ];

    protected $casts = [
        'year_obtained' => 'integer',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
