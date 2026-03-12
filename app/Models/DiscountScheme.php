<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscountScheme extends Model
{
    public $table = 'discount_schemes';

    public $fillable = [
        'name',
        'code',
        'type',
        'value',
        'applies_to',
        'applicable_fee_categories',
        'eligibility_criteria',
        'valid_from',
        'valid_to',
        'academic_year_id',
        'requires_approval',
        'auto_apply',
        'max_instances',
        'budget_allocated',
        'status',
        'created_by'
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'applicable_fee_categories' => 'array',
        'valid_from' => 'date',
        'valid_to' => 'date',
        'requires_approval' => 'boolean',
        'auto_apply' => 'boolean',
        'budget_allocated' => 'decimal:2'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'type' => 'required|in:percentage,fixed,full_waiver',
        'value' => 'nullable|numeric|min:0',
        'status' => 'required|in:active,inactive'
    ];

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function studentDiscounts()
    {
        return $this->hasMany(\App\Models\StudentDiscount::class, 'discount_scheme_id');
    }
}
