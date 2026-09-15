<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentNotice extends Model
{
    protected $fillable = [
        'student_id',
        'created_by',
        'title',
        'body',
        'notice_type',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}