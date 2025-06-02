<?php

namespace App\Repositories;

use App\Models\AcademicYear;
use App\Repositories\BaseRepository;

class AcademicYearRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'start_date',
        'end_date',
        'is_current'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return AcademicYear::class;
    }
}
