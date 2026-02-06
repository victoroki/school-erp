<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessageRecipient extends Model
{
    public $table = 'message_recipients';

    public $fillable = [
        'sent_message_id',
        'recipient_type',
        'recipient_id',
        'contact',
        'recipient_name',
        'delivery_status',
        'delivery_time',
        'failure_reason'
    ];

    protected $casts = [
        'sent_message_id' => 'integer',
        'recipient_type' => 'string',
        'recipient_id' => 'integer',
        'contact' => 'string',
        'recipient_name' => 'string',
        'delivery_status' => 'string',
        'delivery_time' => 'datetime',
        'failure_reason' => 'string'
    ];

    public function message()
    {
        return $this->belongsTo(SentMessage::class, 'sent_message_id');
    }

    public function recipient()
    {
        if ($this->recipient_type == 'Student') {
            return $this->belongsTo(Student::class, 'recipient_id');
        } elseif ($this->recipient_type == 'Parent') {
            return $this->belongsTo(Parents::class, 'recipient_id');
        } elseif ($this->recipient_type == 'Staff') {
            return $this->belongsTo(Staff::class, 'recipient_id');
        }
        return null;
    }
}
