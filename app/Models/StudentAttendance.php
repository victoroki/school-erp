<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentAttendance extends Model
{
    public $table = 'student_attendance';
    protected $primaryKey = 'attendance_id';

    public $fillable = [
        'student_id',
        'class_section_id',
        'academic_year_id',
        'term_id',
        'date',
        'status',
        'remarks',
        'marked_by'
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'string',
        'remarks' => 'string',
    ];

    /**
     * Resolve the academic year and term that a given date falls in for a
     * learner, so a register entry is scoped without the caller having to know.
     *
     * Falls back to the learner's current enrollment when no enrollment covers
     * the date, and returns NULLs rather than guessing when nothing matches.
     *
     * @return array{academic_year_id: int|null, term_id: int|null}
     */
    public static function resolvePeriodFor(int $studentId, $date): array
    {
        $date = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : (string) $date;

        $yearId = \Illuminate\Support\Facades\DB::table('student_class_enrollments as e')
            ->join('academic_years as y', 'y.academic_year_id', '=', 'e.academic_year_id')
            ->where('e.student_id', $studentId)
            ->whereDate('y.start_date', '<=', $date)
            ->whereDate('y.end_date', '>=', $date)
            ->value('e.academic_year_id');

        if (! $yearId) {
            $yearId = \Illuminate\Support\Facades\DB::table('student_class_enrollments')
                ->where('student_id', $studentId)
                ->where('is_current', true)
                ->value('academic_year_id');
        }

        $termId = $yearId
            ? \Illuminate\Support\Facades\DB::table('terms')
                ->where('academic_year_id', $yearId)
                ->whereDate('start_date', '<=', $date)
                ->whereDate('end_date', '>=', $date)
                ->value('id')
            : null;

        return [
            'academic_year_id' => $yearId ? (int) $yearId : null,
            'term_id' => $termId ? (int) $termId : null,
        ];
    }

    public function academicYear()
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id', 'academic_year_id');
    }

    public function term()
    {
        return $this->belongsTo(\App\Models\Term::class, 'term_id', 'id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }

    public function classSection()
    {
        return $this->belongsTo(ClassSection::class, 'class_section_id', 'class_section_id');
    }

    public function markedBy()
    {
        // marked_by holds staff.staff_id (enforced by student_attendance_ibfk_3),
        // not users.id. Pointing this at User meant a register's marker resolved
        // to an unrelated account, or to nothing at all.
        return $this->belongsTo(\App\Models\Staff::class, 'marked_by', 'staff_id');
    }
}
