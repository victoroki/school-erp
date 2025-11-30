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

    public function paginate(int $perPage, array $columns = ['*']): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->allQuery();
        $search = request('q');
        $status = request('status');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('admission_no', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }
        if ($status) {
            $query->where('status', $status);
        }
        return $query->orderBy('first_name')->paginate($perPage, $columns);
    }
}
