<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    public $table = 'ledger_entries';

    public $fillable = [
        'student_id',
        'academic_year_id',
        'term_id',
        'student_fee_assignment_id',
        'entry_date',
        'description',
        'entry_type',
        'debit',
        'credit',
        'balance_after',
        'reference_type',
        'reference_id',
        'created_by',
        'source',
        'reverses_entry_id',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function term()
    {
        return $this->belongsTo(\App\Models\Term::class, 'term_id');
    }

    public function studentFeeAssignment()
    {
        return $this->belongsTo(\App\Models\StudentFeeAssignment::class, 'student_fee_assignment_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    public function reverses()
    {
        return $this->belongsTo(\App\Models\LedgerEntry::class, 'reverses_entry_id');
    }

    public function getTypeLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->entry_type));
    }
}
