<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeInvoice extends Model
{
    public $table = 'fee_invoices';

    public $fillable = [
        'student_id',
        'academic_year_id',
        'term',
        'invoice_number',
        'invoice_date',
        'due_date',
        'total_amount',
        'discount_amount',
        'net_amount',
        'payment_status',
        'generated_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,student_id',
        'academic_year_id' => 'required|exists:academic_years,academic_year_id',
        'term' => 'nullable|string|max:50',
        'invoice_date' => 'required|date',
        'due_date' => 'nullable|date|after_or_equal:invoice_date',
        'payment_status' => 'required|in:unpaid,partial,paid',
    ];

    public function student()
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function generatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'generated_by');
    }

    public function feeAssignments()
    {
        return $this->hasMany(\App\Models\StudentFeeAssignment::class, 'student_id', 'student_id')
            ->where('academic_year_id', $this->academic_year_id)
            ->where('term', $this->term);
    }

    public function payments()
    {
        return $this->hasManyThrough(
            \App\Models\FeePayment::class,
            \App\Models\StudentFeeAssignment::class,
            'student_id',
            'student_fee_assignment_id',
            'student_id',
            'student_fee_assignment_id'
        )->where('student_fee_assignments.academic_year_id', $this->academic_year_id)
          ->where('student_fee_assignments.term', $this->term);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('payment_status', 'unpaid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    public static function generateInvoiceNumber()
    {
        $prefix = 'INV';
        $date = now()->format('Ymd');
        $lastInvoice = self::where('invoice_number', 'like', "{$prefix}-{$date}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "{$prefix}-{$date}-{$newNumber}";
    }

    public function markAsPaid()
    {
        $this->update(['payment_status' => 'paid']);
    }

    public function markAsPartial()
    {
        $this->update(['payment_status' => 'partial']);
    }
}
