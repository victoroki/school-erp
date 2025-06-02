<?php

namespace App\Repositories;

use App\Models\StaffDocument;
use App\Repositories\BaseRepository;

class StaffDocumentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'staff_id',
        'document_type',
        'document_name',
        'file_path',
        'uploaded_at'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return StaffDocument::class;
    }
}
