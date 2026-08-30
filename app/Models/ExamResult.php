<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    public $table = 'exam_results';
    
    protected $primaryKey = 'result_id';

    public $fillable = [
        'exam_id',
        'student_id',
        'class_section_id',
        'subject_id',
        'marks_obtained',
        'grade_id',
        'remarks',
        'created_by'
    ];

    protected $casts = [
        'marks_obtained' => 'decimal:2',
        'remarks' => 'string'
    ];

    public static array $rules = [
        'exam_id' => 'nullable',
        'student_id' => 'nullable',
        'class_section_id' => 'nullable',
        'subject_id' => 'nullable',
        'marks_obtained' => 'required|numeric',
        'grade_id' => 'nullable',
        'remarks' => 'nullable|string|max:65535',
        'created_by' => 'nullable',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function subject(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Subject::class, 'subject_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function grade(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\GradingScale::class, 'grade_id');
    }

    public function classSection(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\ClassSection::class, 'class_section_id');
    }

    public function createdBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'created_by');
    }

    public function exam(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Exam::class, 'exam_id');
    }

    /**
     * Maximum marks for this result's paper, resolved from the exam
     * timetable (exam_schedules stores max_marks against the CLASS, while
     * results are stored against the SECTION).
     */
    public function getMaxMarksAttribute(): float
    {
        if (array_key_exists('max_marks', $this->attributes) && $this->attributes['max_marks'] !== null) {
            return (float) $this->attributes['max_marks'];
        }

        $maxMarks = null;

        if ($this->exam_id && $this->subject_id) {
            $query = \App\Models\ExamSchedule::where('exam_id', $this->exam_id)
                ->where('subject_id', $this->subject_id);

            $classId = null;
            if ($this->class_section_id) {
                if ($this->relationLoaded('classSection') && $this->classSection) {
                    $classId = $this->classSection->class_id;
                } else {
                    $classId = \App\Models\ClassSection::where('class_section_id', $this->class_section_id)->value('class_id');
                }
            }

            if ($classId) {
                $query->where('class_id', $classId);
            }

            $maxMarks = $query->min('max_marks');
        }

        // No timetable row → assume marks were recorded as percentages.
        return (float) ($maxMarks ?? 100);
    }

    /**
     * Percentage score for this result.
     */
    public function getPercentageAttribute(): float
    {
        $max = $this->getMaxMarksAttribute();

        if ($max <= 0) {
            return 0.0;
        }

        return round(((float) $this->marks_obtained / $max) * 100, 2);
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($examResult) {
            if ($examResult->marks_obtained === null) {
                return;
            }

            // Grade against the PERCENTAGE score, not the raw mark — raw
            // marks may be out of any maximum defined in the exam timetable.
            $percentage = $examResult->getPercentageAttribute();

            $grade = \App\Models\GradingScale::where('min_percentage', '<=', $percentage)
                ->where('max_percentage', '>=', $percentage)
                ->first();

            if ($grade) {
                $examResult->grade_id = $grade->grade_id;
            }
        });
    }
}
