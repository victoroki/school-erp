<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    public $table = 'staff';

    public $fillable = [
        'user_id',
        'employee_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'joining_date',
        'department_id',
        'designation',
        'qualification',
        'experience',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'photo_url',
        'staff_type',
        'status'
    ];

    protected $casts = [
        'employee_id' => 'string',
        'first_name' => 'string',
        'middle_name' => 'string',
        'last_name' => 'string',
        'date_of_birth' => 'date',
        'gender' => 'string',
        'joining_date' => 'date',
        'designation' => 'string',
        'qualification' => 'string',
        'experience' => 'float',
        'email' => 'string',
        'phone' => 'string',
        'address' => 'string',
        'city' => 'string',
        'country' => 'string',
        'photo_url' => 'string',
        'staff_type' => 'string',
        'status' => 'string'
    ];

    public static array $rules = [
        'user_id' => 'nullable',
        'employee_id' => 'required|string|max:20',
        'first_name' => 'required|string|max:50',
        'middle_name' => 'nullable|string|max:50',
        'last_name' => 'required|string|max:50',
        'date_of_birth' => 'required',
        'gender' => 'required|string',
        'joining_date' => 'required',
        'department_id' => 'nullable',
        'designation' => 'required|string|max:100',
        'qualification' => 'nullable|string|max:255',
        'experience' => 'nullable|numeric',
        'email' => 'required|string|max:100',
        'phone' => 'required|string|max:20',
        'address' => 'required|string|max:65535',
        'city' => 'required|string|max:50',
        'country' => 'required|string|max:50',
        'photo_url' => 'nullable|string|max:255',
        'staff_type' => 'required|string',
        'status' => 'nullable|string',
        'created_at' => 'nullable',
        'updated_at' => 'nullable'
    ];

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function assignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Assignment::class, 'teacher_id');
    }

    public function bankTransactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BankTransaction::class, 'created_by');
    }

    public function bookIssues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookIssue::class, 'received_by');
    }

    public function bookIssue1s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\BookIssue::class, 'issued_by');
    }

    public function classSections(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ClassSection::class, 'class_teacher_id');
    }

    public function examResults(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\ExamResult::class, 'created_by');
    }

    public function expenses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Expense::class, 'created_by');
    }

    public function expense2s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Expense::class, 'approved_by');
    }

    public function studentFees(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\StudentFee::class, 'fee_payments');
    }

    public function hostels(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Hostel::class, 'warden_id');
    }

    public function incomeCategories(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\IncomeCategory::class, 'income');
    }

    public function inventoryItems(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\InventoryItem::class, 'inventory_transactions');
    }

    public function staffSalaries(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(\App\Models\StaffSalary::class, 'payroll');
    }

    public function staffAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffAttendance::class, 'staff_id');
    }

    public function staffAttendance3s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffAttendance::class, 'marked_by');
    }

    public function staffDocuments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffDocument::class, 'staff_id');
    }

    public function staffLeaves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffLeafe::class, 'approved_by');
    }

    public function staffLeafe4s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffLeafe::class, 'staff_id');
    }

    public function staffSalary5s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StaffSalary::class, 'staff_id');
    }

    public function studentAttendances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\StudentAttendance::class, 'marked_by');
    }

    public function teacherSubjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TeacherSubject::class, 'staff_id');
    }

    public function timetables(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Timetable::class, 'teacher_id');
    }

    public function transportAssignments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportAssignment::class, 'driver_id');
    }

    public function transportAssignment6s(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TransportAssignment::class, 'assistant_id');
    }

    public function vehicles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Vehicle::class, 'driver_id');
    }
    public function getFullNameAttribute()
    {
        $names = array_filter([
            $this->first_name,
            $this->middle_name,
            $this->last_name
        ]);
        
        return implode(' ', $names);
    }
}
