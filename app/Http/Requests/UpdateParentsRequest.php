<?php

namespace App\Http\Requests;

use App\Models\Parents;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParentsRequest extends FormRequest
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
        $rules = Parents::$rules;
        
        return $rules;
    }
}
