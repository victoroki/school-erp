<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSubject extends Model
{
    public $table = 'class_subjects';
    protected $primaryKey = 'class_subject_id';
    public $timestamps = false; 

    public $fillable = [
        'class_id',
        'subject_id',
        'academic_year_id',
        'periods_per_week'
    ];

    protected $casts = [
        'periods_per_week' => 'integer',
    ];

    /**
     * Every field here used to be `nullable`, so this validated nothing: a POST
     * omitting the foreign keys passed validation and inserted a row with NULL
     * class_id / subject_id / academic_year_id. The columns are nullable in the
     * schema, so the database accepted that, and the row then appeared in
     * listings as a curriculum entry belonging to nothing. The create form's HTML
     * `required` attributes were the only guard, and those do not survive a
     * direct POST.
     *
     * subject_id is a single id on the edit form and a list on the bulk create
     * form. CreateClassSubjectRequest normalises the single case to a list, so
     * one rule covers both and every submitted id is checked against the
     * subjects table rather than trusted.
     */
    public static array $rules = [
        'class_id' => 'required|integer|exists:classes,class_id',
        'subject_id' => 'required',
        'subject_id.*' => 'integer|exists:subjects,subject_id',
        'academic_year_id' => 'required|integer|exists:academic_years,academic_year_id',
        'periods_per_week' => 'nullable|integer|min:1|max:40'
    ];

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function class(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\SchoolClass::class, 'class_id');
    }

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }
}
