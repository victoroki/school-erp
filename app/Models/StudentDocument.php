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
        'document_category',
        'document_name',
        'file_path',
        'is_verified',
        'verified_by',
        'verified_at',
        'expiry_date',
        'is_mandatory',
        'notes'
    ];

    protected $casts = [
        'document_type' => 'string',
        'document_name' => 'string',
        'file_path' => 'string',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'expiry_date' => 'date',
        'is_mandatory' => 'boolean'
    ];

    public static array $rules = [
        'student_id' => 'required|exists:students,student_id',
        'document_type' => 'required|string|max:50',
        'document_category' => 'required|in:academic,medical,identification,financial,legal,certificates,other',
        'document_name' => 'required|string|max:100',
        'file_path' => 'nullable|string|max:255',
        'document_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        'is_verified' => 'boolean',
        'expiry_date' => 'nullable|date',
        'is_mandatory' => 'boolean'
    ];

    public function student(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Student::class, 'student_id');
    }
}
