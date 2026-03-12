<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;

class CreateStudentRequest extends FormRequest
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
        $rules = Student::$rules;
        $rules['admission_no'] .= '|unique:students,admission_no';
        $rules['user_id'] .= '|unique:students,user_id';
        
        // Add rules for optional initial enrollment
        $rules['class_section_id'] = 'nullable|exists:class_sections,class_section_id';
        $rules['academic_year_id'] = 'nullable|required_with:class_section_id|exists:academic_years,academic_year_id';
        $rules['roll_number_enrollment'] = 'nullable|string|max:20';
        
        return $rules;
    }
}
