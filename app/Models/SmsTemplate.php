<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    public $table = 'sms_templates';

    public $fillable = [
        'title',
        'content',
        'variables'
    ];

    protected $casts = [
        'title' => 'string',
        'content' => 'string',
        'variables' => 'string'
    ];

    public static array $rules = [
        'title' => 'required|string|max:100',
        'content' => 'required|string|max:65535',
        'variables' => 'nullable|string|max:65535',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    
}
