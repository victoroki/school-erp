<?php

namespace App\Http\Requests;

use App\Models\HostelAllocation;
use Illuminate\Foundation\Http\FormRequest;

class CreateHostelAllocationRequest extends FormRequest
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
        return HostelAllocation::$rules;
    }
}
