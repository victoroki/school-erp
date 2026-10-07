<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryMember extends Model
{
    public $table = 'library_members';
    protected $primaryKey = 'member_id';

    public $fillable = [
        'user_id',
        'member_type',
        'reference_id',
        'membership_date',
        'membership_expiry_date',
        'max_allowed_books',
        'status'
    ];

    protected $casts = [
        'member_type' => 'string',
        'membership_date' => 'date',
        'status' => 'string'
    ];

    public static array $rules = [
        'user_id' => 'nullable',
        'member_type' => 'required|string',
        'reference_id' => 'required',
        'membership_date' => 'required',
        'max_allowed_books' => 'required',
        'status' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * `reference_id` points at whichever record the member actually is. It is a
     * string column, so it cannot be typed as a foreign key on the model — the
     * two relations below resolve it and pick the right one by member_type.
     */
    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'reference_id', 'student_id');
    }

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'reference_id', 'staff_id');
    }

    /**
     * The member's real name.
     *
     * `user_id` is nullable and is empty for every member created from a
     * student or staff record, so reading `user->name` alone renders "Unknown"
     * for the whole membership. Resolve the person from the reference instead
     * and only fall back to the linked login.
     */
    public function getPersonNameAttribute()
    {
        $person = $this->member_type === 'staff' ? $this->staff : $this->student;

        if ($person) {
            $name = trim(implode(' ', array_filter([
                $person->first_name ?? null,
                $person->middle_name ?? null,
                $person->last_name ?? null,
            ])));

            if ($name !== '') {
                return $name;
            }
        }

        return $this->user->name ?? null;
    }

    /**
     * Human-readable label, e.g. "Grace Wanjiku (ADM-0014)".
     *
     * Prefers the admission/employee number over the raw reference_id because
     * reference_id is a database id: it is what made every member in a
     * dropdown look alike and left nothing useful to search on.
     */
    public function getDisplayNameAttribute()
    {
        $name = $this->person_name;
        $person = $this->member_type === 'staff' ? $this->staff : $this->student;

        $identifier = $this->member_type === 'staff'
            ? ($person->employee_number ?? null)
            : ($person->admission_no ?? null);

        $identifier = $identifier ?: $this->reference_id;

        // Without any resolvable name, the id alone is still better than a
        // blank row — but label it so it is obvious the link is missing.
        if (! $name) {
            $label = 'Unlinked ' . ucfirst((string) $this->member_type);

            return $identifier ? "{$label} #{$identifier}" : $label;
        }

        return $identifier ? "{$name} ({$identifier})" : $name;
    }

    public function bookIssues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookIssue::class, 'member_id');
    }
}
