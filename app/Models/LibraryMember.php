<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    public $table = 'library_members';

    public $fillable = [
        'user_id',
        'member_type',
        'reference_id',
        'membership_date',
        'max_allowed_books',
        'status'
    ];

    protected $casts = [
        'member_type' => 'string',
        'membership_date' => 'date',
        'status' => 'string'
    ];

    public static array $rules = [
        'user_id' => 'nullable',
        'member_type' => 'required|string',
        'reference_id' => 'required',
        'membership_date' => 'required',
        'max_allowed_books' => 'required',
        'status' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function bookIssues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookIssue::class, 'member_id');
    }
}
