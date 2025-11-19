<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parents extends Model
{
    public $table = 'parents';
    protected $primaryKey = 'parent_id';

    public $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'relationship',
        'email',
        'phone',
        'alternate_phone',
        'occupation'
    ];

    protected $casts = [
        'first_name' => 'string',
        'last_name' => 'string',
        'relationship' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'alternate_phone' => 'string',
        'occupation' => 'string'
    ];

    public static array $rules = [
        'user_id' => 'nullable',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'relationship' => 'required|string',
        'email' => 'nullable|string|max:100',
        'phone' => 'required|string|max:20',
        'alternate_phone' => 'nullable|string|max:20',
        'occupation' => 'nullable|string|max:100',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function students(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Student::class, 'student_parent_relationship');
    }
}
