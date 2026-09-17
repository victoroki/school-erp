<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    protected $table = 'homework_submissions';

    protected $fillable = [
        'homework_id',
        'student_id',
        'status',
        'content',
        'attachment_path',
        'attachment_name',
        'attachment_mime',
        'submitted_at',
        'teacher_feedback',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    public function homework(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Homework::class, 'homework_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function reviewer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}