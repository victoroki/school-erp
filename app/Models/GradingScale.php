<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingScale extends Model
{
    public $table = 'grading_scales';
    
    protected $primaryKey = 'grade_id';

    public $fillable = [
        'name',
        'education_system',
        'min_percentage',
        'max_percentage',
        'grade_point',
        'description'
    ];

    protected $casts = [
        'name' => 'string',
        'education_system' => 'string',
        'min_percentage' => 'decimal:2',
        'max_percentage' => 'decimal:2',
        'grade_point' => 'decimal:2',
        'description' => 'string'
    ];

    /** Curriculum values the column accepts. NULL means "applies to any system". */
    public const SYSTEMS = ['8-4-4', 'CBC'];

    public static array $rules = [
        'name' => 'required|string|max:20',
        'education_system' => 'nullable|in:8-4-4,CBC',
        'min_percentage' => 'required|numeric|min:0|max:100',
        'max_percentage' => 'required|numeric|min:0|max:100|gt:min_percentage',
        'grade_point' => 'nullable|numeric',
        'description' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Scales that apply to a curriculum: rows tagged for that system, plus
     * untagged rows (which apply to every system).
     */
    public function scopeForSystem($query, ?string $system)
    {
        return $query->where(function ($q) use ($system) {
            $q->whereNull('education_system');

            if ($system !== null && $system !== '') {
                $q->orWhere('education_system', $system);
            }
        });
    }

    /**
     * Resolve the grade for a percentage.
     *
     * Deterministic by construction — bands are considered highest-minimum
     * first, so an overlapping pair always resolves the same way — and only
     * scales applying to the learner's curriculum are considered. The previous
     * un-ordered ->first() meant a percentage covered by two overlapping bands
     * (KCSE and CBC ranges overlap heavily) resolved by row order.
     */
    public static function resolveForPercentage(float $percentage, ?string $system = null): ?self
    {
        return static::query()
            ->forSystem($system)
            ->where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->orderByDesc('min_percentage')
            ->orderBy('grade_id')
            ->first();
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'grade_id');
    }
}
