<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Repositories\BaseRepository;

class NotificationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title',
        'message',
        'type',
        'recipient_type'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Notification::class;
    }
}