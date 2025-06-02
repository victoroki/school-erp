<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosition extends Model
{
    public $table = 'job_positions';

    public $fillable = [
        'title',
        'department_id',
        'description',
        'responsibilities',
        'qualifications',
        'is_active'
    ];

    protected $casts = [
        'title' => 'string',
        'description' => 'string',
        'responsibilities' => 'string',
        'qualifications' => 'string',
        'is_active' => 'boolean'
    ];

    public static array $rules = [
        'title' => 'required|string|max:100',
        'department_id' => 'nullable',
        'description' => 'nullable|string|max:65535',
        'responsibilities' => 'nullable|string|max:65535',
        'qualifications' => 'nullable|string|max:65535',
        'is_active' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }
}
