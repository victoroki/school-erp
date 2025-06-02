<?php

namespace App\Repositories;

use App\Models\LibraryMember;
use App\Repositories\BaseRepository;

class LibraryMemberRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'user_id',
        'member_type',
        'reference_id',
        'membership_date',
        'max_allowed_books',
        'status'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return LibraryMember::class;
    }
}
