<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    // The migration creates `homeworks`; without this the inflector resolves
    // Homework::class to the uncountable table `homework`.
    protected $table = 'homeworks';

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'subject',
        'class_name',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class, 'homework_id');
    }

    public function isLateSubmitted(\Illuminate\Support\Carbon $submittedAt = null): bool
    {
        $at = $submittedAt ?? now();
        if ($this->due_date === null) {
            return false;
        }

        // due_date is date-only; grace runs to the end of that day (23:59:59).
        return $at->gt($this->due_date->copy()->endOfDay());
    }
}