<?php

namespace App\Http\Requests;

use App\Models\IncomeCategory;
use Illuminate\Foundation\Http\FormRequest;

class CreateIncomeCategoryRequest extends FormRequest
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
        return IncomeCategory::$rules;
    }
}
