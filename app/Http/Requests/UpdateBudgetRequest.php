<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $categoryTable = $this->input('category_type') === 'income' ? 'income_categories' : 'expense_categories';

        return [
            'financial_year_id' => ['required', 'integer', 'exists:financial_years,id'],
            'category_type' => ['required', 'in:income,expense'],
            'category_id' => [
                'required',
                'integer',
                Rule::exists($categoryTable, 'category_id'),
                Rule::unique('budgets')->ignore($this->route('budget'))->where(function ($query) {
                    return $query->where('financial_year_id', $this->input('financial_year_id'))
                        ->where('category_type', $this->input('category_type'));
                }),
            ],
            'amount' => ['required', 'numeric', 'min:0'],
            'alert_threshold' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'include_fees' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     */
    public function attributes(): array
    {
        return [
            'financial_year_id' => 'financial year',
            'category_type' => 'category type',
            'category_id' => 'category',
            'amount' => 'amount',
            'alert_threshold' => 'alert threshold',
        ];
    }
}