<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'max_marks',
        'weightage',
        'is_cbc',
        'description',
        'status'
    ];

    protected $casts = [
        'is_cbc' => 'boolean',
        'status' => 'boolean',
        'max_marks' => 'decimal:2',
        'weightage' => 'decimal:2'
    ];

    public static $rules = [
        'name' => 'required|string|max:100',
        'code' => 'nullable|string|max:20',
        'max_marks' => 'required|numeric|min:1',
        'weightage' => 'required|numeric|min:0|max:100',
        'is_cbc' => 'nullable|boolean',
        'status' => 'nullable|boolean'
    ];
}
