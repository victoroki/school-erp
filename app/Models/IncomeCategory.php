<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IncomeCategory extends Model
{
    public $table = 'income_categories';
    protected $primaryKey = 'category_id';

    public $fillable = [
        'name',
        'status',
        'description',
        'color_code'
    ];

    protected $casts = [
        'name' => 'string',
        'status' => 'string',
        'description' => 'string',
        'color_code' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:100',
        'description' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Staff::class, 'income');
    }
}
