<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTermRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $termId = $this->route('term');

        return [
            'academic_year_id' => 'required|exists:academic_years,academic_year_id',
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'max:20', Rule::unique('terms', 'code')->where(fn ($q) => $q->where('academic_year_id', $this->academic_year_id))->ignore($termId)],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'fee_due_date' => 'nullable|date',
            'status' => 'required|in:upcoming,active,completed',
            'display_order' => 'nullable|integer',
        ];
    }
}
