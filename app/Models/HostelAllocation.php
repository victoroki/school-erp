<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class HostelAllocation extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_VACATED = 'vacated';
    public const STATUS_PENDING = 'pending';

    /** The complete set of values hostel_allocations.status accepts. */
    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_VACATED,
        self::STATUS_PENDING,
    ];

    public $table = 'hostel_allocations';
    protected $primaryKey = 'allocation_id';

    /**
     * Only an active allocation holds a bed and counts towards occupancy.
     */
    public function holdsBed(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Guard the enum at the model so no write path can store a status the
     * database would silently truncate. See HostelRoom::booted() for why this
     * is not left to the form request alone.
     */
    protected static function booted(): void
    {
        static::saving(function (HostelAllocation $allocation) {
            $status = $allocation->status;

            if ($status !== null && !in_array($status, self::STATUSES, true)) {
                throw ValidationException::withMessages([
                    'status' => sprintf(
                        'Allocation status must be one of: %s. "%s" is not a valid value.',
                        implode(', ', self::STATUSES),
                        $status
                    ),
                ]);
            }
        });
    }

    public $fillable = [
        'student_id',
        'hostel_id',
        'room_id',
        'bed_number',
        'allocation_date',
        'vacating_date',
        'status',
        'academic_year_id',
        'checkout_notes'
    ];

    protected $casts = [
        'allocation_date' => 'date',
        'vacating_date' => 'date',
        'status' => 'string',
        'checkout_notes' => 'string'
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,student_id',
        'hostel_id' => 'required|exists:hostels,hostel_id',
        'room_id' => 'required|exists:hostel_rooms,room_id',
        'bed_number' => 'nullable|integer|min:1',
        'allocation_date' => 'required|date',
        'vacating_date' => 'nullable|date|after_or_equal:allocation_date',
        'status' => 'nullable|in:active,vacated,pending',
        'academic_year_id' => 'nullable|exists:academic_years,academic_year_id',
        'checkout_notes' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function room(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\HostelRoom::class, 'room_id');
    }

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }

    public function academicYear(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\AcademicYear::class, 'academic_year_id');
    }

    public function hostel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Hostel::class, 'hostel_id');
    }

    public function hostelFees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelFee::class, 'allocation_id');
    }

    /**
     * Eager load everything the allocation tables and reports render, including
     * the student's current class and section (the ERP keeps class/stream on
     * student_class_enrollments, not on students).
     */
    public function scopeWithDisplayContext(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->with([
            'room',
            'hostel',
            'academicYear',
            'student' => fn ($q) => $q->with([
                'studentClassEnrollments' => fn ($e) => $e
                    ->where('is_current', true)
                    ->with(['classSection.schoolClass', 'classSection.section']),
            ]),
        ]);
    }

    /**
     * Apply the list/report filters: hostel, room, status, academic year,
     * class, section (stream) and a free text search over the student.
     */
    public function scopeFilter(\Illuminate\Database\Eloquent\Builder $query, array $filters): \Illuminate\Database\Eloquent\Builder
    {
        $query->withDisplayContext();

        if (!empty($filters['hostel_id'])) {
            $query->where('hostel_id', $filters['hostel_id']);
        }

        if (!empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['class_id']) || !empty($filters['section_id'])) {
            // Constrained to the *current* enrolment so a class/stream filter
            // describes where the student is now, not where they used to be.
            $query->whereHas('student.studentClassEnrollments', function ($enrollments) use ($filters) {
                $enrollments->where('is_current', true)
                    ->whereHas('classSection', function ($classSection) use ($filters) {
                        if (!empty($filters['class_id'])) {
                            $classSection->where('class_id', $filters['class_id']);
                        }

                        if (!empty($filters['section_id'])) {
                            $classSection->where('section_id', $filters['section_id']);
                        }
                    });
            });
        }

        if (!empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->whereHas('student', function ($q) use ($term) {
                $q->where('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('admission_no', 'like', $term);
            });
        }

        return $query;
    }

    /**
     * "Form 3 - East" for the student holding this bed, or a dash.
     */
    public function getClassInfoAttribute(): string
    {
        $enrollment = $this->student?->studentClassEnrollments?->first();

        if (!$enrollment || !$enrollment->classSection) {
            return '—';
        }

        $class = $enrollment->classSection->schoolClass->name ?? null;
        $section = $enrollment->classSection->section->name ?? null;

        return trim(($class ?? '—') . ' - ' . ($section ?? ''), ' -') ?: '—';
    }

    // Helper method to check if allocation is active
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
