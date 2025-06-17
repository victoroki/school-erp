<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    public $table = 'classrooms';
    protected $primaryKey = 'classroom_id';

    public $fillable = [
        'room_number',
        'building',
        'floor',
        'capacity',
        'has_sockets',
        'has_whiteboard'
    ];

    protected $casts = [
        'room_number' => 'string',
        'building' => 'string',
        'has_sockets' => 'boolean',
        'has_whiteboard' => 'boolean'
    ];

    public static array $rules = [
        'room_number' => 'required|string|max:20',
        'building' => 'nullable|string|max:50',
        'floor' => 'nullable',
        'capacity' => 'required',
        'has_sockets' => 'nullable|boolean',
        'has_whiteboard' => 'nullable|boolean',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function classSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSection::class, 'classroom_id');
    }

    public function examSchedules(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamSchedule::class, 'room_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'classroom_id');
    }
}
