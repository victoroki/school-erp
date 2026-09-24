<?php

namespace App\Models;

use App\Services\CurriculumService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCardTemplate extends Model
{
    use HasFactory;

    public $table = 'report_card_templates';

    public $fillable = [
        'name',
        'education_system',
        'is_default',
        'layout_config',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'status' => 'boolean',
        'layout_config' => 'array',
    ];

    public const SYSTEMS = ['8-4-4', 'CBC'];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'education_system' => 'required|in:8-4-4,CBC',
        'is_default' => 'nullable|boolean',
        'layout_config' => 'nullable|array',
        'status' => 'nullable|boolean',
    ];

    protected static function booted(): void
    {
        // The column default is '8-4-4', which is wrong for every CBC school.
        // A report card template should default to the curriculum the school
        // actually runs unless the caller states otherwise.
        static::creating(function (self $template) {
            if (blank($template->education_system)) {
                $template->education_system = app(CurriculumService::class)->current();
            }
        });
    }

    /**
     * Templates that apply to a curriculum: rows tagged for that system, plus
     * untagged rows. Mirrors GradingScale::scopeForSystem().
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
}
