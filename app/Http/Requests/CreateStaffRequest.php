<?php

namespace App\Http\Requests;

use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateStaffRequest extends FormRequest
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
        $rules = Staff::$rules;
        $rules['photo'] = 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048';
        $rules['personal_email'] = 'nullable|email|max:100|unique:staff,personal_email';
        $rules['tsc_number'] = 'nullable|string|max:20';

        return $rules;
    }

    /**
     * A teaching hire also gets a portal login, and the account-setup link goes
     * to the personal mailbox when one is given, otherwise to the work address.
     * That derived address must not already belong to another account.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('staff_type') !== 'teaching') {
                return;
            }

            $loginEmail = $this->input('personal_email') ?: $this->input('work_email');
            if (! $loginEmail || ! is_string($loginEmail)) {
                return;
            }

            if (User::where('email', $loginEmail)->exists()) {
                $validator->errors()->add(
                    $this->input('personal_email') ? 'personal_email' : 'work_email',
                    'A login account already uses ' . $loginEmail . '.'
                );
            }
        });
    }
}
