<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    public $table = 'email_templates';
    
    protected $primaryKey = 'template_id';

    public $fillable = [
        'title',
        'category',
        'subject',
        'content',
        'variables',
        'usage_count',
        'status',
        'created_by'
    ];

    protected $casts = [
        'title' => 'string',
        'category' => 'string',
        'subject' => 'string',
        'content' => 'string',
        'variables' => 'string',
        'usage_count' => 'integer',
        'status' => 'string',
        'created_by' => 'integer'
    ];

    public static array $rules = [
        'title' => 'required|string|max:100',
        'category' => 'nullable|string|max:100',
        'subject' => 'required|string|max:255',
        'content' => 'required|string',
        'variables' => 'nullable|string',
        'status' => 'nullable|in:active,inactive,draft'
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
