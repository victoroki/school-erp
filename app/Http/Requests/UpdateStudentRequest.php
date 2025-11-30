<?php

namespace App\Http\Requests;

use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentRequest extends FormRequest
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

        $studentId = $this->route('student');
        $rules['admission_no'] .= '|' . Rule::unique('students', 'admission_no')->ignore($studentId, 'student_id');
        $rules['user_id'] .= '|' . Rule::unique('students', 'user_id')->ignore($studentId, 'student_id');
        return $rules;
    }
}
