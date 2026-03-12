<?php

namespace App\Repositories;

use App\Models\EmailTemplate;
use App\Repositories\BaseRepository;

class EmailTemplateRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'category',
        'subject',
        'content',
        'variables',
        'status',
        'created_by'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return EmailTemplate::class;
    }
}
