<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Period extends Model
{
    public $table = 'periods';
    protected $primaryKey = 'period_id';


    public $fillable = [
        'name',
        'start_time',
        'end_time',
        'type'
    ];

    protected $casts = [
        'name' => 'string',
        'type' => 'string'
    ];

    public static array $rules = [
        'name' => 'required|string|max:50',
        'start_time' => 'required',
        'end_time' => 'required',
        'type' => 'required|in:period,break',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'period_id');
    }
}
