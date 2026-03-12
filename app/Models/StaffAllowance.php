<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffAllowance extends Model
{
    protected $table = 'staff_allowances';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'allowance_name',
        'amount',
        'is_taxable',
        'status',
        'effective_from',
        'effective_to',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
