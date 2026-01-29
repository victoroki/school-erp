<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSibling extends Model
{
    protected $table = 'student_siblings';
    protected $primaryKey = 'sibling_id';

    protected $fillable = [
        'student_id',
        'sibling_student_id',
        'relationship_type',
        'is_twin',
        'notes'
    ];

    protected $casts = [
        'is_twin' => 'boolean',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function sibling()
    {
        return $this->belongsTo(Student::class, 'sibling_student_id', 'student_id');
    }
}
