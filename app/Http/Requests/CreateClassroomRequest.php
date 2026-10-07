<?php

namespace App\Http\Requests;

use App\Models\Classroom;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateClassroomRequest extends FormRequest
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

        // classrooms has a unique index on room_number, but the rules did not,
        // so a duplicate POST blew up as a raw SQLSTATE 23000 instead of
        // showing a field error next to the input.
        $rules['room_number'] .= '|' . Rule::unique('classrooms', 'room_number');

        return $rules;
    }
}
