<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public $table = 'email_templates';

    public $fillable = [
        'title',
        'subject',
        'content',
        'variables'
    ];

    protected $casts = [
        'title' => 'string',
        'subject' => 'string',
        'content' => 'string',
        'variables' => 'string'
    ];

    public static array $rules = [
        'title' => 'required|string|max:100',
        'subject' => 'required|string|max:255',
        'content' => 'required|string|max:65535',
        'variables' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    
}
