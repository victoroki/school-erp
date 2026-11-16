<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    public $table = 'modules';

    public $fillable = [
        'key',
        'name',
        'icon',
        'route_prefix',
        'is_core',
        'is_active',
        'order'
    ];

    protected $casts = [
        'key' => 'string',
        'name' => 'string',
        'icon' => 'string',
        'route_prefix' => 'string',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    public static array $rules = [
        'key' => 'required|string|max:100|unique:modules,key',
        'name' => 'required|string|max:150',
        'icon' => 'nullable|string|max:100',
        'route_prefix' => 'nullable|string|max:150',
        'is_core' => 'boolean',
        'is_active' => 'boolean',
        'order' => 'integer'
    ];

    /**
     * Route model binding uses the stable module key in URLs
     * (e.g. modules/fees/toggle) instead of the numeric id.
     */
    public function getRouteKeyName(): string
    {
        return 'key';
    }
}
