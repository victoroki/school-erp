<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NotificationTriggered
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public string $triggerType,
        public int $studentId,
        public string $triggerModel,
        public int $triggerId,
        public array $context = []
    ) {}
}
