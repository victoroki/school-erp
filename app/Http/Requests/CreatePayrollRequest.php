<?php

namespace App\Http\Requests;

use App\Models\Payroll;
use Illuminate\Foundation\Http\FormRequest;

class CreatePayrollRequest extends FormRequest
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
        return Payroll::$rules;
    }
}
