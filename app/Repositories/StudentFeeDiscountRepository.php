<?php

namespace App\Repositories;

use App\Models\StudentFeeDiscount;
use App\Repositories\BaseRepository;

class StudentFeeDiscountRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'student_id',
        'discount_id',
        'academic_year_id'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return StudentFeeDiscount::class;
    }
}
