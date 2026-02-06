<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SentMessage extends Model
{
    public $table = 'sent_messages';

    public $fillable = [
        'message_type',
        'template_id',
        'subject',
        'content',
        'recipient_type',
        'recipient_count',
        'scheduled_for',
        'sent_at',
        'sent_by',
        'status',
        'cost'
    ];

    protected $casts = [
        'message_type' => 'string',
        'template_id' => 'integer',
        'subject' => 'string',
        'content' => 'string',
        'recipient_type' => 'string',
        'recipient_count' => 'integer',
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'sent_by' => 'integer',
        'status' => 'string',
        'cost' => 'decimal:2'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    public function recipients()
    {
        return $this->hasMany(MessageRecipient::class);
    }

    public function template()
    {
        if ($this->message_type == 'SMS') {
            return $this->belongsTo(SmsTemplate::class, 'template_id');
        } else {
            return $this->belongsTo(EmailTemplate::class, 'template_id');
        }
    }
}
