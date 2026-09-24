<?php

namespace App\Http\Requests;

use App\Models\ClassSubject;
use Illuminate\Foundation\Http\FormRequest;

class CreateClassSubjectRequest extends FormRequest
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
    /**
     * The bulk create form submits subject_id[] while the single-assignment form
     * submits a bare subject_id. Normalising the single case to a list here means
     * one set of rules validates both shapes.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('subject_id') && ! is_array($this->input('subject_id'))) {
            $this->merge(['subject_id' => [$this->input('subject_id')]]);
        }
    }

    public function rules()
    {
        return ClassSubject::$rules;
    }
}
