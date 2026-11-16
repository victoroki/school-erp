<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationTrigger extends Model
{
    protected $table = 'communication_triggers';

    protected $fillable = [
        'trigger_type',
        'name',
        'description',
        'is_enabled',
        'requires_confirmation',
        'default_template_id',
        'channel',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'requires_confirmation' => 'boolean',
    ];

    public function defaultTemplate()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'default_template_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeAutoSend($query)
    {
        return $query->where('is_enabled', true)->where('requires_confirmation', false);
    }

    public static function isEnabled(string $triggerType): bool
    {
        return static::where('trigger_type', $triggerType)->where('is_enabled', true)->exists();
    }

    public static function requiresConfirmation(string $triggerType): bool
    {
        $trigger = static::where('trigger_type', $triggerType)->first();
        return $trigger?->requires_confirmation ?? true;
    }
}
