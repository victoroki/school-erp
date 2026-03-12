<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffOnboardingChecklist extends Model
{
    protected $table = 'staff_onboarding_checklists';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'item_name',
        'item_order',
        'status',
        'completed_date',
        'notes',
    ];

    protected $casts = [
        'item_order' => 'integer',
        'completed_date' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
