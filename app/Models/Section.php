<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    public $table = 'sections';

    public $fillable = [
        'class_id',
        'name',
        'capacity'
    ];

    protected $casts = [
        'name' => 'string'
    ];

    public static array $rules = [
        'class_id' => 'nullable',
        'name' => 'required|string|max:20',
        'capacity' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function class(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Class::class, 'class_id');
    }

    public function classSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSection::class, 'section_id');
    }
}
