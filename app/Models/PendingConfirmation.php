<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingConfirmation extends Model
{
    protected $table = 'pending_confirmations';

    protected $fillable = [
        'trigger_type',
        'trigger_id',
        'trigger_model',
        'recipient_type',
        'recipient_id',
        'contact',
        'recipient_name',
        'student_name',
        'channel',
        'subject',
        'rendered_body',
        'status',
        'confirmed_by',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function confirm(int $userId): void
    {
        $this->update([
            'status' => 'sent',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);
    }

    public function discard(): void
    {
        $this->update(['status' => 'discarded']);
    }
}
