<?php

namespace App\Http\Requests;

use App\Models\StudentDocument;
use Illuminate\Foundation\Http\FormRequest;

class CreateStudentDocumentRequest extends FormRequest
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
        $rules = StudentDocument::$rules;
        $rules['document_file'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
        return $rules;
    }
}
