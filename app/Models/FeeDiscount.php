<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeDiscount extends Model
{
    protected $table = 'fee_discounts';
    protected $primaryKey = 'discount_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
    ];
}
