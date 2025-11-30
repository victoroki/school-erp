<?php

namespace App\Repositories;

use App\Models\StudentDocument;
use App\Repositories\BaseRepository;

class StudentDocumentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
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
        return StudentDocument::class;
    }

    public function paginate(int $perPage, array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->allQuery();
        $query->with(['student']);
        return $query->paginate($perPage, $columns);
    }
}
