<?php

namespace App\Repositories;

use App\Models\NotificationRecipient;
use App\Repositories\BaseRepository;

class NotificationRecipientRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'notification_id',
        'recipient_id',
        'is_read'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return NotificationRecipient::class;
    }
}