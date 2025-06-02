<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    public $table = 'leave_types';

    public $fillable = [
        'name',
        'days_allowed',
        'description',
        'is_paid'
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'is_paid' => 'boolean'
    ];

    public static array $rules = [
        'name' => 'required|string|max:50',
        'days_allowed' => 'required',
        'description' => 'nullable|string|max:65535',
        'is_paid' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function staffLeaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffLeafe::class, 'leave_type_id');
    }
}
