<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentFeeAssignment extends Model
{
    public $table = 'student_fee_assignments';

    public $fillable = [
        'student_id',
        'fee_structure_id',
        'academic_year_id',
        'term',
        'amount',
        'discount_id',
        'discount_amount',
        'final_amount',
        'assigned_by',
        'assigned_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'assigned_date' => 'datetime'
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function feeStructure()
    {
        return $this->belongsTo(\App\Models\FeeStructure::class, 'fee_structure_id', 'fee_structure_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function discount()
    {
        return $this->belongsTo(\App\Models\DiscountScheme::class, 'discount_id');
    }
}
