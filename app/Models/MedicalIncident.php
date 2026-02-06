<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Student;
use App\Models\User;

class MedicalIncident extends Model
{
    use HasFactory;

    protected $primaryKey = 'medical_incident_id';

    protected $fillable = [
        'student_id',
        'incident_date',
        'symptoms',
        'details',
        'treatment_given',
        'notified_parents',
        'marked_by'
    ];

    protected $casts = [
        'incident_date' => 'date',
        'notified_parents' => 'boolean'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function marker()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
