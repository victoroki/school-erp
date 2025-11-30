<?php

namespace App\Repositories;

use App\Models\Message;
use App\Repositories\BaseRepository;

class MessageRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'sender_id',
        'receiver_id',
        'subject',
        'is_read'
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Message::class;
    }
}