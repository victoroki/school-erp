<?php

namespace App\Repositories;

use App\Models\EmailTemplate;
use App\Repositories\BaseRepository;

class EmailTemplateRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'subject',
        'content',
        'variables'
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
