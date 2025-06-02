<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeDiscount extends Model
{
    public $table = 'student_fee_discounts';

    public $fillable = [
        'student_id',
        'discount_id',
        'academic_year_id'
    ];

    protected $casts = [
        
    ];

    public static array $rules = [
        'student_id' => 'nullable',
        'discount_id' => 'nullable',
        'academic_year_id' => 'nullable'
    ];

    public function discount(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\FeeDiscount::class, 'discount_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }
}
