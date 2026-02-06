<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemplateCategory extends Model
{
    public $table = 'template_categories';

    public $fillable = [
        'name',
        'description',
        'type',
        'icon',
        'color'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'type' => 'string',
        'icon' => 'string',
        'color' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:255',
        'type' => 'required|in:SMS,Email,Both',
        'icon' => 'nullable|string|max:100',
        'color' => 'nullable|string|max:7'
    ];
}
