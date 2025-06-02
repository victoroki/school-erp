<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffDocument extends Model
{
    public $table = 'staff_documents';

    public $fillable = [
        'staff_id',
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
        'staff_id' => 'nullable',
        'document_type' => 'required|string|max:50',
        'document_name' => 'required|string|max:100',
        'file_path' => 'required|string|max:255',
        'uploaded_at' => 'nullable'
    ];

    public function staff(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Staff::class, 'staff_id');
    }
}
