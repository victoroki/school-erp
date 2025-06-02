<?php

namespace App\Repositories;

use App\Models\SmsTemplate;
use App\Repositories\BaseRepository;

class SmsTemplateRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'content',
        'variables'
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
