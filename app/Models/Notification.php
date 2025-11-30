<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    public $table = 'notifications';
    
    protected $primaryKey = 'notification_id';

    public $fillable = [
        'title',
        'message',
        'type',
        'recipient_type',
        'sender_id'
    ];

    protected $casts = [
        'title' => 'string',
        'message' => 'string',
        'type' => 'string',
        'recipient_type' => 'string',
        'sender_id' => 'integer'
    ];

    public static array $rules = [
        'title' => 'required|string|max:255',
        'message' => 'required|string',
        'type' => 'required|in:announcement,alert,event,exam,fee,general',
        'recipient_type' => 'required|in:all,students,parents,teachers,staff,specific',
        'sender_id' => 'nullable|exists:users,id',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Get the sender that owns the notification.
     */
    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }

    /**
     * Get the recipients for the notification.
     */
    public function recipients(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\NotificationRecipient::class, 'notification_id', 'notification_id');
    }
}