<?php

namespace App\Http\Requests;

use App\Models\FeeCategory;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFeeCategoryRequest extends FormRequest
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
        $rules = FeeCategory::$rules;

        // Ignore the record being updated. Without this the unique rule
        // compares the row against itself, so submitting the Edit form
        // with the name unchanged fails with "The name has already been
        // taken" and the category's other fields can never be edited.
        // The parameter is resolved defensively because the resource
        // route parameter is {fee_category} while the controller action
        // signature names it $id.
        $id = $this->route('fee_category') ?? $this->route('id');

        $rules['name'] = 'required|string|max:100|unique:fee_categories,name,' . $id . ',category_id';

        return $rules;
    }
}
