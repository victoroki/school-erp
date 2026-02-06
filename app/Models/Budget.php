<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    public $table = 'budgets';

    public $fillable = [
        'financial_year_id',
        'category_id',
        'category_type',
        'amount',
        'alert_threshold',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'alert_threshold' => 'decimal:2'
    ];

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class);
    }

    public function category()
    {
        if ($this->category_type === 'income') {
            return $this->belongsTo(IncomeCategory::class, 'category_id');
        }
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }
}
