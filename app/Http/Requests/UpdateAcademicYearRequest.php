<?php

namespace App\Http\Requests;

use App\Models\AcademicYear;
use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
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
        $rules = AcademicYear::$rules;
        
        // Get the ID from the route. It might be the model instance or just the ID integer
        $id = $this->route('academic_year');
        if (is_object($id)) {
            $id = $id->academic_year_id;
        }

        // Append the current record ID to ignore it during the unique check
        // Syntax: unique:table,column,except,idColumn
        $rules['name'] = 'required|string|max:50|unique:academic_years,name,' . $id . ',academic_year_id';
        
        return $rules;
    }
}
