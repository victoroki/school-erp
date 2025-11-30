<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    public $table = 'messages';
    
    protected $primaryKey = 'message_id';

    public $fillable = [
        'sender_id',
        'receiver_id',
        'subject',
        'message',
        'is_read',
        'read_at'
    ];

    protected $casts = [
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'subject' => 'string',
        'message' => 'string',
        'is_read' => 'boolean',
        'read_at' => 'datetime'
    ];

    public static array $rules = [
        'sender_id' => 'nullable|exists:users,id',
        'receiver_id' => 'nullable|exists:users,id',
        'subject' => 'nullable|string|max:255',
        'message' => 'required|string',
        'is_read' => 'nullable|boolean',
        'read_at' => 'nullable|date',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    /**
     * Get the sender that owns the message.
     */
    public function sender(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'sender_id');
    }

    /**
     * Get the receiver that owns the message.
     */
    public function receiver(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'receiver_id');
    }
}