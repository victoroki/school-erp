<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassroomRequest extends FormRequest
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
        $rules = Classroom::$rules;

        // Same uniqueness rule as create, minus this classroom's own row so
        // saving without changing the room number is not rejected against
        // itself.
        $rules['room_number'] .= '|' . Rule::unique('classrooms', 'room_number')
            ->ignore($this->route('id'), 'classroom_id');

        return $rules;
    }
}
