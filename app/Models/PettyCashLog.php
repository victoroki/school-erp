<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PettyCashLog extends Model
{
    public $table = 'petty_cash_logs';

    public $fillable = [
        'date',
        'amount',
        'description',
        'type',
        'reference',
        'recorded_by'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2'
    ];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
