<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommunicationLog extends Model
{
    protected $table = 'communication_logs';

    protected $fillable = [
        'trigger_type',
        'trigger_id',
        'trigger_model',
        'template_id',
        'channel',
        'recipient_type',
        'recipient_id',
        'contact',
        'contact_masked',
        'subject',
        'body',
        'status',
        'provider_message_id',
        'failure_reason',
        'cost',
        'sent_at',
        'delivered_at',
    ];

    protected $casts = [
        'cost' => 'decimal:4',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function scopeForToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeSentToday($query)
    {
        return $query->forToday()->where('status', 'sent');
    }

    public function template()
    {
        return $this->belongsTo(CommunicationTemplate::class, 'template_id');
    }
}
