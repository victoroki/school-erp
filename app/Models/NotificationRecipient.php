<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    public $table = 'notification_recipients';
    
    protected $primaryKey = 'id';

    public $fillable = [
        'notification_id',
        'recipient_id',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'notification_id' => 'integer',
        'recipient_id' => 'integer',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public static array $rules = [
        'notification_id' => 'nullable|exists:notifications,notification_id',
        'recipient_id' => 'nullable|exists:users,id',
        'is_read' => 'nullable|boolean',
        'read_at' => 'nullable|date',
        'created_at' => 'nullable'
    ];

    /**
     * Get the notification that owns the recipient.
     */
    public function notification(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Notification::class, 'notification_id', 'notification_id');
    }

    /**
     * Get the recipient user.
     */
    public function recipient(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'recipient_id');
    }
}