<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\BaseRepository;

class StudentRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_id',
        'admission_no',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'city',
        'country',
        'phone',
        'emergency_contact',
        'admission_date',
        'photo_url',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Student::class;
    }
}
