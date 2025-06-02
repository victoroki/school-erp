<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentParentRelationship extends Model
{
    public $table = 'student_parent_relationship';

    public $fillable = [
        'student_id',
        'parent_id',
        'is_primary_contact'
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean'
    ];

    public static array $rules = [
        'student_id' => 'nullable',
        'parent_id' => 'nullable',
        'is_primary_contact' => 'nullable|boolean'
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Parent::class, 'parent_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }
}
