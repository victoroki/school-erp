<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsTemplate extends Model
{
    public $table = 'sms_templates';
    
    protected $primaryKey = 'template_id';

    public $fillable = [
        'title',
        'category',
        'content',
        'variables',
        'usage_count',
        'status',
        'created_by'
    ];

    protected $casts = [
        'title' => 'string',
        'category' => 'string',
        'content' => 'string',
        'variables' => 'string',
        'usage_count' => 'integer',
        'status' => 'string',
        'created_by' => 'integer'
    ];

    public static array $rules = [
        'title' => 'required|string|max:100',
        'category' => 'nullable|string|max:100',
        'content' => 'required|string',
        'variables' => 'nullable|string',
        'status' => 'nullable|in:active,inactive,draft'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
