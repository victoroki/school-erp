<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeStructure extends Model
{
    public $table = 'fee_structures';
    protected $primaryKey = 'fee_structure_id';

    public $fillable = [
        'academic_year_id',
        'term',
        'class_id',
        'category_id',
        'amount',
        'payment_frequency',
        'due_date',
        'late_fee_amount',
        'late_fee_type',
        'pro_rata_enabled',
        'status',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'academic_year_id' => 'integer',
        'class_id' => 'integer',
        'category_id' => 'integer',
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'late_fee_amount' => 'decimal:2',
        'pro_rata_enabled' => 'boolean'
    ];

    public static array $rules = [
        'academic_year_id' => 'required',
        'class_id' => 'required',
        'category_id' => 'required',
        'amount' => 'required|numeric|min:0',
        'payment_frequency' => 'required|in:one-time,termly,monthly,custom',
        'status' => 'required|in:active,inactive,draft,archived'
    ];

    /**
     * $rules plus the rules that need a closure (and so cannot live in a static
     * property initializer).
     */
    public static function validationRules(): array
    {
        return array_merge(self::$rules, [
            'term' => ['nullable', self::termBelongsToSelectedYear()],
        ]);
    }

    /**
     * A term code is only unique within an academic year, so a term belonging to a
     * year other than the selected Academic Year must be rejected. Without this the
     * stored code cannot be resolved back to a term for the fee's own year.
     */
    public static function termBelongsToSelectedYear(): \Closure
    {
        return function (string $attribute, $value, \Closure $fail) {
            if (blank($value)) {
                return;
            }

            $belongsToYear = Term::where('academic_year_id', request()->input('academic_year_id'))
                ->where('code', $value)
                ->exists();

            if (! $belongsToYear) {
                $fail('The selected term does not belong to the chosen academic year.');
            }
        };
    }

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\FeeCategory::class, 'category_id', 'category_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function schoolClass(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id', 'class_id');
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentFeeAssignment::class, 'fee_structure_id');
    }
}
