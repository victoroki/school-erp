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
        'term_id',
        'amount',
        'discount_id',
        'discount_amount',
        'final_amount',
        'paid_amount',
        'assigned_by',
        'assigned_date',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
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

    public function termModel()
    {
        return $this->belongsTo(\App\Models\Term::class, 'term_id');
    }

    public function discount()
    {
        return $this->belongsTo(\App\Models\DiscountScheme::class, 'discount_id');
    }

    public function feeAdjustments()
    {
        return $this->hasMany(\App\Models\FeeAdjustment::class, 'student_fee_assignment_id');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\FeePayment::class, 'student_fee_assignment_id');
    }

    public function scopeForStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForAcademicYear($query, $academicYearId)
    {
        return $query->where('academic_year_id', $academicYearId);
    }

    public function scopeForTerm($query, $term)
    {
        return $query->where('term', $term);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    
    public function getBalanceAttribute()
    {
        return (float)($this->final_amount ?? 0) - (float)$this->paid_amount;
    }

    public function getPaymentStatusAttribute()
    {
        $paid = $this->paid_amount;
        $final = $this->final_amount;

        if ($paid >= $final) {
            return 'paid';
        }

        if ($paid > 0) {
            return 'partial';
        }

        return 'unpaid';
    }
}
