<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Staff extends Model
{
    use SoftDeletes;

    public $table = 'staff';
    protected $primaryKey = 'staff_id';

    public $fillable = [
        'user_id',
        'employee_number',
        'first_name',
        'middle_name',
        'last_name',
        'national_id_number',
        'passport_number',
        'date_of_birth',
        'gender',
        'marital_status',
        'nationality',
        'religion',
        'phone_primary',
        'phone_secondary',
        'personal_email',
        'work_email',
        'current_address',
        'postal_address',
        'city',
        'county',
        'country',
        'emergency_contact_name',
        'emergency_contact_relationship',
        'emergency_contact_phone',
        'blood_group',
        'disability_status',
        'department_id',
        'job_position_id',
        'employment_type',
        'employment_status',
        'date_of_joining',
        'contract_start_date',
        'contract_end_date',
        'probation_period_months',
        'probation_end_date',
        'confirmation_status',
        'reporting_manager_id',
        'work_location',
        'work_schedule',
        'tsc_number',
        'kra_pin',
        'nhif_number',
        'nssf_number',
        'basic_salary',
        'salary_grade',
        'bank_name',
        'bank_branch',
        'account_number',
        'account_name',
        'annual_leave_entitlement',
        'photo_url',
        'staff_type',
        'designation',
        'qualification',
        'experience',
        'exit_date',
        'exit_reason',
        'notes',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'date_of_joining' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'probation_end_date' => 'date',
        'exit_date' => 'date',
        'basic_salary' => 'decimal:2',
        'annual_leave_entitlement' => 'integer',
        'probation_period_months' => 'integer',
    ];

    // Relationships
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function jobPosition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\JobPosition::class, 'job_position_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function reportingManager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'reporting_manager_id', 'staff_id');
    }

    public function subordinates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Staff::class, 'reporting_manager_id', 'staff_id');
    }

    public function qualifications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffQualification::class, 'staff_id', 'staff_id');
    }

    public function employmentHistory(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffEmploymentHistory::class, 'staff_id', 'staff_id');
    }

    public function allowances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffAllowance::class, 'staff_id', 'staff_id')
            ->where('status', 'active');
    }

    public function deductions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffDeduction::class, 'staff_id', 'staff_id')
            ->where('status', 'active');
    }

    public function leaveApplications(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\LeaveApplication::class, 'staff_id', 'staff_id');
    }

    public function leaveBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffLeaveBalance::class, 'staff_id', 'staff_id');
    }

    public function attendance(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffAttendance::class, 'staff_id', 'staff_id');
    }

    public function documents(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffDocument::class, 'staff_id', 'staff_id');
    }

    public function payrollDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\PayrollDetail::class, 'staff_id', 'staff_id');
    }

    public function onboardingChecklist(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffOnboardingChecklist::class, 'staff_id', 'staff_id');
    }

    public function exitClearance(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(\App\Models\StaffExitClearance::class, 'staff_id', 'staff_id');
    }

    // Legacy relationships (keep for backward compatibility)
    public function staffAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffAttendance::class, 'staff_id', 'staff_id');
    }

    public function staffDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffDocument::class, 'staff_id', 'staff_id');
    }

    public function teacherSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TeacherSubject::class, 'staff_id', 'staff_id');
    }

    public function classSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSection::class, 'class_teacher_id', 'staff_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'teacher_id', 'staff_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeTeachers($query)
    {
        return $query->where('staff_type', 'teaching');
    }

    public function scopeNonTeaching($query)
    {
        return $query->where('staff_type', 'non-teaching');
    }

    public function scopeOnProbation($query)
    {
        return $query->where('confirmation_status', 'on_probation');
    }

    // Accessors
    public function getFullNameAttribute()
    {
        $names = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        
        return implode(' ', $names);
    }

    public function getAgeAttribute()
    {
        if (!$this->date_of_birth) return null;
        return $this->date_of_birth->age;
    }

    public function getTenureAttribute()
    {
        if (!$this->date_of_joining) return null;
        
        $diff = $this->date_of_joining->diff(now());
        $years = $diff->y;
        $months = $diff->m;
        
        if ($years > 0) {
            return $years . ' year' . ($years > 1 ? 's' : '') . 
                   ($months > 0 ? ', ' . $months . ' month' . ($months > 1 ? 's' : '') : '');
        }
        
        return $months . ' month' . ($months > 1 ? 's' : '');
    }

    public function getTotalAllowancesAttribute()
    {
        return $this->allowances()->sum('amount');
    }

    public function getTotalDeductionsAttribute()
    {
        return $this->deductions()->sum('monthly_amount');
    }

    public function getGrossSalaryAttribute()
    {
        return $this->basic_salary + $this->total_allowances;
    }

    public function getIsOnProbationAttribute()
    {
        return $this->confirmation_status === 'on_probation';
    }

    public function getIsContractExpiringAttribute()
    {
        if (!$this->contract_end_date) return false;
        return $this->contract_end_date->diffInDays(now()) <= 30;
    }

    // Validation Rules
    public static array $rules = [
        'employee_number' => 'required|string|max:20|unique:staff,employee_number',
        'first_name' => 'required|string|max:50',
        'last_name' => 'required|string|max:50',
        'date_of_birth' => 'required|date|before:18 years ago',
        'gender' => 'required|in:male,female,other',
        'phone_primary' => 'required|string|max:20',
        'work_email' => 'required|email|unique:staff,work_email',
        'department_id' => 'required|exists:departments,department_id',
        'job_position_id' => 'required|exists:job_positions,job_position_id',
        'date_of_joining' => 'required|date',
        'basic_salary' => 'required|numeric|min:0',
        'employment_type' => 'required|in:full_time,part_time,contract,casual,intern',
        'employment_status' => 'required|in:active,on_leave,suspended,terminated,resigned,retired',
    ];
}
