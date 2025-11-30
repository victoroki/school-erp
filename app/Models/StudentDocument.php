<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentDocument extends Model
{
    public $table = 'student_documents';
    protected $primaryKey = 'document_id';
    public $timestamps = false;

    public $fillable = [
        'student_id',
        'document_type',
        'document_name',
        'file_path',
        'uploaded_at'
    ];

    protected $casts = [
        'document_type' => 'string',
        'document_name' => 'string',
        'file_path' => 'string',
        'uploaded_at' => 'datetime'
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,student_id',
        'document_type' => 'required|string|max:50',
        'document_name' => 'required|string|max:100',
        'file_path' => 'required|string|max:255',
        'uploaded_at' => 'nullable'
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }
}
