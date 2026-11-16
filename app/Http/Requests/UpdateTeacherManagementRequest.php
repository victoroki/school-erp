<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTeacherManagementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Staff-record fields only — never the linked login account or role,
     * which stay managed under User Management.
     */
    public function rules(): array
    {
        $teacherId = $this->route('teacher');
        $idRule = $teacherId ? ",{$teacherId},staff_id" : '';

        return [
            // Personal
            'first_name' => 'required|string|max:50',
            'middle_name' => 'nullable|string|max:50',
            'last_name' => 'required|string|max:50',
            'date_of_birth' => 'required|date|before:18 years ago',
            'gender' => 'required|in:male,female,other',
            'phone_primary' => 'required|string|max:20',

            // Contact
            'work_email' => "required|email|max:100|unique:staff,work_email{$idRule}",
            'personal_email' => "nullable|email|max:100|unique:staff,personal_email{$idRule}",
            'current_address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',

            // Employment
            'employee_number' => "nullable|string|max:20|unique:staff,employee_number{$idRule}",
            'tsc_number' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,department_id',
            'job_position_id' => 'nullable|exists:job_positions,job_position_id',
            'designation' => 'nullable|string|max:100',
            'qualification' => 'nullable|string|max:255',
            'date_of_joining' => 'nullable|date',
            'employment_type' => 'required|in:full_time,part_time,contract,casual,intern',
            'employment_status' => 'required|in:active,on_leave,suspended,terminated,resigned,retired',
        ];
    }
}
