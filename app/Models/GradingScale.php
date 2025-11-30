<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    public $table = 'grading_scales';
    
    protected $primaryKey = 'grade_id';

    public $fillable = [
        'name',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description'
    ];

    protected $casts = [
        'name' => 'string',
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'description' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:20',
        'min_percentage' => 'required|numeric',
        'max_percentage' => 'required|numeric',
        'grade_point' => 'nullable|numeric',
        'description' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'grade_id');
    }
}
