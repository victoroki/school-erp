<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisciplinaryRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'disciplinary_record_id';

    protected $fillable = [
        'student_id',
        'incident_date',
        'incident_type',
        'description',
        'reported_by',
        'action_taken',
        'status'
    ];

    protected $casts = [
        'incident_date' => 'date'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
