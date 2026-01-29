<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamType extends Model
{
    public $table = 'exam_types';
    protected $primaryKey = 'exam_type_id';

    public $fillable = [
        'name',
        'short_name',
        'description'
    ];

    protected $casts = [
        'name' => 'string',
        'short_name' => 'string',
        'description' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'short_name' => 'nullable|string|max:20',
        'description' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function academicYears(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\AcademicYear::class, 'exams');
    }
}
