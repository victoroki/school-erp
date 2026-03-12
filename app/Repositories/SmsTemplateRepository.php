<?php

namespace App\Repositories;

use App\Models\SmsTemplate;
use App\Repositories\BaseRepository;

class SmsTemplateRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'category',
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
        return SmsTemplate::class;
    }
}
