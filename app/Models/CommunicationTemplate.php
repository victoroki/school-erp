<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTemplate extends Model
{
    protected $table = 'communication_templates';

    protected $fillable = [
        'name',
        'trigger_type',
        'channel',
        'subject',
        'body',
        'is_active',
        'is_critical',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_critical' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeForTrigger($query, string $triggerType)
    {
        return $query->where('trigger_type', $triggerType)->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
