<?php

namespace App\Http\Requests;

use App\Models\Expenses;
use Illuminate\Foundation\Http\FormRequest;

class CreateExpensesRequest extends FormRequest
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
        $rules = Expenses::$rules;

        $rules['category_id'] = 'required|exists:expense_categories,category_id';
        $rules['amount'] = 'required|numeric|min:0.01';
        $rules['expense_date'] = 'required|date';
        $rules['payment_method'] = 'required|in:cash,check,bank_transfer,online';
        $rules['supplier_id'] = 'nullable|exists:suppliers,supplier_id';
        $rules['requested_by'] = 'nullable|exists:staff,staff_id';
        $rules['attachment'] = 'nullable|file|max:5120';

        if ($this->input('payment_method') && $this->input('payment_method') !== 'cash') {
            $rules['bank_account_id'] = 'required|exists:bank_accounts,account_id';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'category_id.required' => 'Please select an expense category.',
            'amount.required' => 'Please enter the expense amount.',
            'amount.min' => 'The expense amount must be greater than zero.',
            'expense_date.required' => 'Please pick the expense date.',
            'payment_method.required' => 'Please choose a payment method.',
            'payment_method.in' => 'The selected payment method is not valid.',
            'bank_account_id.required' => 'Please select a bank account for this payment method.',
            'bank_account_id.exists' => 'The selected bank account is not valid.',
        ];
    }
}
