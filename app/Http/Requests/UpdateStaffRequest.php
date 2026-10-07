<?php

namespace App\Http\Requests;

use App\Models\Staff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // The plain unique checks in Staff::$rules compare the record against
        // itself, so saving an edit without changing these values always failed.
        $staffId = $this->route('id');
        $unique = fn ($field) => Rule::unique('staff', $field)->ignore($staffId, 'staff_id');

        $rules = Staff::$rules;
        $rules['photo'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        $rules['personal_email'] = 'nullable|email|max:100|' . $unique('personal_email');
        $rules['tsc_number'] = 'nullable|string|max:20';
        $rules['employee_number'] = 'nullable|string|max:20|' . $unique('employee_number');
        $rules['work_email'] = 'required|email|' . $unique('work_email');

        return $rules;
    }
}
