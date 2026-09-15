<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    // The migration creates `homeworks`; without this the inflector resolves
    // Homework::class to the uncountable table `homework`.
    protected $table = 'homeworks';

    protected $fillable = [
        'created_by',
        'title',
        'description',
        'subject',
        'class_name',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}