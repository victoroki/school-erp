<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeAdjustmentAuditLog extends Model
{
    public $table = 'fee_adjustment_audit_logs';

    public $fillable = [
        'fee_adjustment_id',
        'action',
        'user_id',
        'details',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function feeAdjustment()
    {
        return $this->belongsTo(\App\Models\FeeAdjustment::class, 'fee_adjustment_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
