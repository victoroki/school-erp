<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    public $table = 'student_attendance';
    protected $primaryKey = 'attendance_id';

    public $fillable = [
        'student_id',
        'class_section_id',
        'date',
        'status',
        'remarks',
        'marked_by'
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'string',
        'remarks' => 'string'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id', 'class_section_id');
    }

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
