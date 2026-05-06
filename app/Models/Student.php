<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    public $table = 'students';
    protected $primaryKey = 'student_id';

    public $fillable = [
        'user_id',
        'admission_no',
        'nemis_number',
        'upi_number',
        'roll_number',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'birth_certificate_no',
        'gender',
        'nationality',
        'religion',
        'blood_group',
        'address',
        'city',
        'county',
        'sub_county',
        'country',
        'postal_code',
        'phone',
        'emergency_contact',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone_2',
        'admission_date',
        'previous_school',
        'previous_class',
        'transfer_date',
        'transfer_reason',
        'transfer_certificate_no',
        'photo_url',
        'status',
        'enrollment_status',
        'graduation_date',
        'leaving_reason',
        'student_category',
        'education_system',
        'is_scholarship_holder',
        'scholarship_details',
        'medical_conditions',
        'allergies',
        'medications',
        'doctor_name',
        'doctor_phone',
        'uses_transport',
        'route_id',
        'pickup_point',
        'is_hosteller',
        'behavior_score',
        'special_notes',
        'last_login_at',
        'is_active'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'admission_date' => 'date',
        'transfer_date' => 'date',
        'graduation_date' => 'date',
        'last_login_at' => 'datetime',
        'uses_transport' => 'boolean',
        'is_hosteller' => 'boolean',
        'is_scholarship_holder' => 'boolean',
        'is_active' => 'boolean',
        'behavior_score' => 'integer',
        'route_id' => 'integer'
    ];

    public static array $rules = [
        'user_id' => 'nullable|exists:users,id',
        'admission_no' => 'required|string|max:20',
        'nemis_number' => 'nullable|string|max:50',
        'upi_number' => 'nullable|string|max:50',
        'roll_number' => 'nullable|string|max:20',
        'first_name' => 'required|string|max:50',
        'middle_name' => 'nullable|string|max:50',
        'last_name' => 'required|string|max:50',
        'date_of_birth' => 'required|date',
        'gender' => 'required|in:male,female,other',
        'city' => 'required|string|max:50',
        'county' => 'nullable|string|max:50',
        'country' => 'required|string|max:50',
        'admission_date' => 'required|date',
        'education_system' => 'nullable|in:CBC,8-4-4',
        'is_scholarship_holder' => 'boolean',
        'uses_transport' => 'boolean',
        'is_hosteller' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Assignment::class, 'assignment_submissions');
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'student_id');
    }

    public function hostelAllocations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelAllocation::class, 'student_id');
    }

    public function hostelFees(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\HostelFee::class, 'student_id');
    }

    public function studentAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentAttendance::class, 'student_id');
    }

    public function studentClassEnrollments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentClassEnrollment::class, 'student_id');
    }

    public function studentDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentDocument::class, 'student_id');
    }

    public function feeStructures(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\FeeStructure::class, 'student_fee_assignments', 'student_id', 'fee_structure_id');
    }

    public function payments()
    {
        return $this->hasManyThrough(\App\Models\FeePayment::class, \App\Models\StudentFeeAssignment::class, 'student_id', 'student_fee_assignment_id');
    }

    public function parents(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Parents::class, 'student_parent_relationship', 'student_id', 'parent_id');
    }

    public function transportRegistrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportRegistration::class, 'student_id');
    }

    public function emergencyContacts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\EmergencyContact::class, 'student_id');
    }

    public function disciplinaryRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\DisciplinaryRecord::class, 'student_id');
    }

    public function medicalIncidents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\MedicalIncident::class, 'student_id');
    }

    // Helper Attributes for Fee Management
    public function getTotalFeeAttribute()
    {
        return $this->feeAssignments()->where('status', 'active')->sum('final_amount');
    }

    public function getPaidFeeAttribute()
    {
        return $this->payments()->sum('fee_payments.amount');
    }

    public function getBalanceFeeAttribute()
    {
        return $this->total_fee - $this->paid_fee;
    }

    public function getPaymentStatusAttribute()
    {
        $total = $this->total_fee;
        $paid = $this->paid_fee;

        if ($total <= 0) {
            return 'No Fee';
        }

        if ($paid >= $total) {
            return 'Paid';
        }

        if ($paid > 0) {
            return 'Partial';
        }

        return 'Unpaid';
    }

    public function getFullNameAttribute()
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    public function siblings(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Student::class,
            'student_siblings',
            'student_id',
            'sibling_student_id',
            'student_id',
            'student_id'
        )->withPivot('relationship_type', 'is_twin', 'notes')->withTimestamps();
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->age : null;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'active' => '<span class="badge badge-success">Active</span>',
            'inactive' => '<span class="badge badge-secondary">Inactive</span>',
            'alumni' => '<span class="badge badge-info">Alumni</span>',
            'transferred' => '<span class="badge badge-warning">Transferred</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge badge-light">Unknown</span>';
    }

    public function getEnrollmentStatusBadgeAttribute()
    {
        $badges = [
            'enrolled' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Enrolled</span>',
            'graduated' => '<span class="badge badge-primary"><i class="fas fa-graduation-cap"></i> Graduated</span>',
            'transferred' => '<span class="badge badge-warning"><i class="fas fa-exchange-alt"></i> Transferred</span>',
            'expelled' => '<span class="badge badge-danger"><i class="fas fa-ban"></i> Expelled</span>',
            'dropped_out' => '<span class="badge badge-dark"><i class="fas fa-user-slash"></i> Dropped Out</span>',
            'on_leave' => '<span class="badge badge-info"><i class="fas fa-pause-circle"></i> On Leave</span>',
        ];

        return $badges[$this->enrollment_status] ?? $badges['enrolled'];
    }

    public function getCurrentEnrollmentAttribute()
    {
        return $this->studentClassEnrollments()
            ->where('status', 'active')
            ->with(['classSection.schoolClass', 'classSection.section', 'academicYear'])
            ->first();
    }

    public function getPaymentStatusBadgeAttribute()
    {
        $status = $this->payment_status;
        $badges = [
            'Paid' => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Paid</span>',
            'Partial' => '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Partial</span>',
            'Unpaid' => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> Unpaid</span>',
            'No Fee' => '<span class="badge badge-secondary"><i class="fas fa-minus-circle"></i> No Fee</span>',
        ];

        return $badges[$status] ?? $badges['No Fee'];
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->photo_url) {
            return asset($this->photo_url);
        }

        // Generate initials avatar
        $initials = strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
        $colors = ['#667eea', '#764ba2', '#f093fb', '#4facfe', '#43e97b', '#fa709a'];
        $color = $colors[ord($initials[0]) % count($colors)];
        
        return "https://ui-avatars.com/api/?name={$initials}&background=" . ltrim($color, '#') . "&color=fff&size=200&bold=true";
    }

    // Get student's complete academic journey
    public function getAcademicJourneyAttribute()
    {
        return $this->studentClassEnrollments()
            ->with(['classSection.schoolClass', 'classSection.section', 'academicYear'])
            ->orderBy('enrollment_date', 'asc')
            ->get();
    }

    // Check if student has any pending fees
    public function getHasPendingFeesAttribute()
    {
        return $this->balance_fee > 0;
    }

    // Get student's attendance percentage
    public function getAttendancePercentageAttribute()
    {
        $totalDays = $this->studentAttendances()->count();
        if ($totalDays == 0) return 0;
        
        $presentDays = $this->studentAttendances()->where('status', 'present')->count();
        return round(($presentDays / $totalDays) * 100, 2);
    }

    /**
     * Kenyan Phone Formatting
     */
    public function formatKenyanPhone($phone)
    {
        if (!$phone) return null;
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }
        
        if (strlen($phone) == 12) {
            return '+' . substr($phone, 0, 3) . ' ' . substr($phone, 3, 3) . ' ' . substr($phone, 6, 3) . ' ' . substr($phone, 9);
        }
        
        return '+' . $phone;
    }

    public function getFormattedPhoneAttribute()
    {
        return $this->formatKenyanPhone($this->phone) ?? 'N/A';
    }

    public function getFormattedEmergencyPhoneAttribute()
    {
        return $this->formatKenyanPhone($this->emergency_contact) ?? 'N/A';
    }

    public function getFormattedEmergencyPhone2Attribute()
    {
        return $this->formatKenyanPhone($this->emergency_contact_phone_2) ?? 'N/A';
    }

    /**
     * Standardized Kenyan Date Formatting (DD/MM/YYYY)
     */
    public function getKenyanDobAttribute()
    {
        return $this->date_of_birth ? $this->date_of_birth->format('d/m/Y') : 'N/A';
    }

    public function getKenyanAdmissionDateAttribute()
    {
        return $this->admission_date ? $this->admission_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedFeeBalanceAttribute()
    {
        return 'KES ' . number_format($this->balance_fee, 2);
    }

    public function feeAssignments()
    {
        return $this->hasMany(\App\Models\StudentFeeAssignment::class, 'student_id');
    }

    public function feeAdjustments()
    {
        return $this->hasMany(\App\Models\FeeAdjustment::class, 'student_id');
    }

    public function feeInvoices()
    {
        return $this->hasMany(\App\Models\FeeInvoice::class, 'student_id');
    }

    public function studentDiscounts()
    {
        return $this->hasMany(\App\Models\StudentDiscount::class, 'student_id');
    }

    public function getFeeSummaryAttribute()
    {
        $totalAssigned = $this->feeAssignments()->where('status', 'active')->sum('final_amount');
        $totalPaid = $this->payments()->sum('amount');

        return [
            'total_assigned' => $totalAssigned,
            'total_paid' => $totalPaid,
            'balance' => $totalAssigned - $totalPaid,
        ];
    }
}
