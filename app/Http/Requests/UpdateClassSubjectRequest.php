<?php

namespace App\Http\Requests;

use App\Models\ClassSubject;
use Illuminate\Foundation\Http\FormRequest;

class UpdateClassSubjectRequest extends FormRequest
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
        // The edit form submits a single subject, so subject_id.* does not apply
        // to it — the id itself has to be checked.
        return array_merge(ClassSubject::$rules, [
            'subject_id' => 'required|integer|exists:subjects,subject_id',
        ]);
    }
}
