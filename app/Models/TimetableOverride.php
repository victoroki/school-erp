<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimetableOverride extends Model
{
    public $table = 'timetable_overrides';

    public $fillable = [
        'timetable_id',
        'term_week_id',
        'override_type',
        'substitute_teacher_id',
        'substitute_classroom_id',
        'new_day_of_week',
        'new_period_id',
        'new_teacher_id',
        'new_classroom_id',
        'effective_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    public static array $rules = [
        'timetable_id' => 'required|exists:timetable,timetable_id',
        'term_week_id' => 'required|exists:term_weeks,id',
        'override_type' => 'required|in:cancel,substitute,reschedule',
        'substitute_teacher_id' => 'nullable|exists:staff,staff_id',
        'substitute_classroom_id' => 'nullable|exists:classrooms,classroom_id',
        'new_day_of_week' => 'nullable|string|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
        'new_period_id' => 'nullable|exists:periods,period_id',
        'new_teacher_id' => 'nullable|exists:staff,staff_id',
        'new_classroom_id' => 'nullable|exists:classrooms,classroom_id',
        'effective_date' => 'required|date',
        'reason' => 'nullable|string|max:255',
    ];

    public static array $conditionalRules = [
        'cancel' => ['reason' => 'required|string|max:255'],
        'reschedule' => ['reason' => 'required|string|max:255'],
        'substitute' => ['reason' => 'nullable|string|max:255'],
    ];

    public function timetable()
    {
        return $this->belongsTo(Timetable::class, 'timetable_id');
    }

    public function termWeek()
    {
        return $this->belongsTo(TermWeek::class, 'term_week_id');
    }

    public function substituteTeacher()
    {
        return $this->belongsTo(Staff::class, 'substitute_teacher_id');
    }

    public function substituteClassroom()
    {
        return $this->belongsTo(Classroom::class, 'substitute_classroom_id');
    }

    public function newPeriod()
    {
        return $this->belongsTo(Period::class, 'new_period_id');
    }

    public function newTeacher()
    {
        return $this->belongsTo(Staff::class, 'new_teacher_id');
    }

    public function newClassroom()
    {
        return $this->belongsTo(Classroom::class, 'new_classroom_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
