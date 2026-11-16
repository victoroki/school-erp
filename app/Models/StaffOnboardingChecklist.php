<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffOnboardingChecklist extends Model
{
    protected $table = 'staff_onboarding_checklists';
    protected $primaryKey = 'id';

    protected $fillable = [
        'staff_id',
        'checklist_item',
        'is_completed',
        'completed_by',
        'completed_date',
        'notes',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_date' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}
